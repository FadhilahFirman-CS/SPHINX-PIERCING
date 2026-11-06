<?php
session_start();
require_once 'config.php';

$username = $_SESSION['customer_user'] ?? '';
if ($username === '' && empty($_SESSION['admin']) && !empty($_SESSION['user'])) {
    $username = (string)$_SESSION['user'];
}
$username = $username !== '' ? $username : "@sphnx_piercing";

$current_page = basename($_SERVER['PHP_SELF']);

if (!function_exists('sanitize_text')) {
    function sanitize_text($text) {
        return htmlspecialchars(trim((string)$text), ENT_QUOTES, 'UTF-8');
    }
}

function format_shipping_label($shipping) {
    $normalized = strtolower(trim((string)$shipping));
    if ($normalized === 'ambil' || $normalized === 'ambil di toko') return 'Ambil di Toko';
    if ($normalized === 'spx')    return 'SPX';
    if ($normalized === 'gosend') return 'GoSend';
    return $shipping !== '' ? $shipping : '-';
}

// Helper: dapatkan customer_id dari username
function get_customer_id($conn, $username) {
    $s = $conn->prepare("SELECT id FROM customers WHERE username = ? LIMIT 1");
    $s->bind_param('s', $username);
    $s->execute();
    $row = $s->get_result()->fetch_assoc();
    $s->close();
    return $row ? (int)$row['id'] : 0;
}

// Helper: generate kode ORD-XXX
function generate_kode($conn) {
    $res = $conn->query("SELECT MAX(id) AS m FROM pesanan");
    $row = $res->fetch_assoc();
    $n = (int)($row['m'] ?? 0) + 1;
    return 'ORD-' . str_pad($n, 3, '0', STR_PAD_LEFT);
}

// =====================================================================
// 1. SIMPAN PESANAN BARU (dari metode.php redirect)
// =====================================================================
if (isset($_GET['order_complete']) && $_GET['order_complete'] === '1' && !empty($_GET['order_id'])) {

    $shippingRaw = strtolower(trim($_GET['shipping'] ?? ''));
    $shippingLabel = match($shippingRaw) {
        'ambil'  => 'Ambil di Toko',
        'spx'    => 'SPX',
        'gosend' => 'GoSend',
        default  => ucfirst($shippingRaw) ?: 'Lainnya',
    };

    $serviceName    = sanitize_text($_GET['product']          ?? 'Produk');
    $quantity       = max(1, (int)($_GET['quantity']          ?? 1));
    $total          = max(0, (int)($_GET['total']             ?? 0));
    $paymentMethod  = sanitize_text($_GET['payment_method']   ?? '-');
    $recipientName  = sanitize_text($_GET['recipient_name']   ?? '');
    $recipientPhone = sanitize_text($_GET['recipient_phone']  ?? '');
    $recipientAddr  = sanitize_text($_GET['recipient_address']?? '');
    $paymentNote    = sanitize_text($_GET['payment_note']     ?? '');

    $customer_id = get_customer_id($conn, $username);

    if ($customer_id > 0) {
        $kode = generate_kode($conn);

        // Insert pesanan
        $stmtP = $conn->prepare(
            "INSERT INTO pesanan
             (kode, customer_id, layanan, status, total, metode_bayar, pengiriman,
              nama_penerima, tlp_penerima, alamat_penerima, catatan_bayar, tgl_masuk)
             VALUES (?, ?, 'Produk', 'baru', ?, ?, ?, ?, ?, ?, ?, CURDATE())"
        );
        $stmtP->bind_param('ssisssssss',
            $kode, $customer_id, $total,
            $paymentMethod, $shippingLabel,
            $recipientName, $recipientPhone, $recipientAddr, $paymentNote
        );
        $stmtP->execute();
        $pesanan_id = $conn->insert_id;
        $stmtP->close();

        // Insert pesanan_items
        if ($pesanan_id > 0) {
            $hargaSatuan = $quantity > 0 ? (int)ceil($total / $quantity) : $total;
            $stmtI = $conn->prepare(
                "INSERT INTO pesanan_items (pesanan_id, nama_produk, harga, qty)
                 VALUES (?, ?, ?, ?)"
            );
            $stmtI->bind_param('isii', $pesanan_id, $serviceName, $hargaSatuan, $quantity);
            $stmtI->execute();
            $stmtI->close();

            // Insert notifikasi untuk admin
            $judulN = "Pesanan baru #{$kode}";
            $pesanN = "Customer {$username} memesan {$serviceName} (Rp " . number_format($total, 0, ',', '.') . ")";
            $stmtN  = $conn->prepare(
                "INSERT INTO notifikasi (tipe, judul, pesan, pesanan_id) VALUES ('baru', ?, ?, ?)"
            );
            $stmtN->bind_param('ssi', $judulN, $pesanN, $pesanan_id);
            $stmtN->execute();
            $stmtN->close();
        }
    }

    header('Location: riwayat.php');
    exit;
}

// =====================================================================
// 2. AMBIL RIWAYAT PESANAN CUSTOMER DARI DB
// =====================================================================
$orderHistory  = [];
$customer_id   = get_customer_id($conn, $username);

if ($customer_id > 0) {
    $stmtH = $conn->prepare(
        "SELECT p.id, p.kode, p.layanan, p.status, p.total, p.tgl_masuk,
                p.metode_bayar, p.pengiriman, p.catatan_bayar,
                GROUP_CONCAT(pi.nama_produk ORDER BY pi.id SEPARATOR ', ') AS service_name,
                COALESCE(SUM(pi.qty), 1) AS qty_total
         FROM pesanan p
         LEFT JOIN pesanan_items pi ON pi.pesanan_id = p.id
         WHERE p.customer_id = ?
         GROUP BY p.id
         ORDER BY p.tgl_masuk DESC, p.id DESC"
    );
    $stmtH->bind_param('i', $customer_id);
    $stmtH->execute();
    $rows = $stmtH->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmtH->close();

    $statusLabel = [
        'baru'     => 'Baru',
        'diproses' => 'Sedang Diproses',
        'selesai'  => 'Selesai',
        'batal'    => 'Dibatalkan',
    ];

    foreach ($rows as $row) {
        $orderHistory[] = [
            'order_id'       => $row['kode'],
            'service_name'   => $row['service_name'] ?: $row['layanan'],
            'quantity'       => (string)max(1, (int)$row['qty_total']),
            'total'          => (string)$row['total'],
            'payment_method' => $row['metode_bayar'] ?? '-',
            'shipping'       => format_shipping_label($row['pengiriman'] ?? ''),
            'payment_note'   => $row['catatan_bayar'] ?? '',
            'status'         => $statusLabel[$row['status']] ?? ucfirst($row['status']),
            'date'           => date('d F Y', strtotime($row['tgl_masuk'])),
            'time'           => date('H:i', strtotime($row['tgl_masuk'])),
        ];
    }
}

$page_css = '../css-customer/riwayat.css?v=' . filemtime(__DIR__ . '/../css-customer/riwayat.css');
include 'header.php';
?>

<div class="app">
    <aside class="sidebar">
        <div class="brand">
            <img src="../gambar/logo2.jpeg" alt="Logo">
            <span><?= htmlspecialchars($username) ?></span>
        </div>

        <ul class="menu">
            <li><a href="dashboard.php" class="<?= $current_page == 'dashboard.php' ? 'active' : '' ?>"><i class="fa-solid fa-house"></i>Dashboard</a></li>
            <li><a href="riwayat.php" class="<?= $current_page == 'riwayat.php' ? 'active' : '' ?>"><i class="fa-solid fa-clock-rotate-left"></i>Riwayat Pemesanan</a></li>
            <li><a href="jadwal.php" class="<?= $current_page == 'jadwal.php' ? 'active' : '' ?>"><i class="fa-solid fa-calendar-check"></i>Jadwal Reservasi</a></li>

            <li><a href="pengaturan.php" class="<?= $current_page == 'pengaturan.php' ? 'active' : '' ?>"><i class="fa-solid fa-gear"></i>Pengaturan Akun</a></li>
        </ul>
        
        <div class="sidebar-footer">
            <ul class="menu">
                 <li><a href="bantuan.php" class="<?= $current_page == 'bantuan.php' ? 'active' : '' ?>"><i class="fa-solid fa-circle-question"></i>Bantuan</a></li>
            </ul>
        </div>
    </aside>

    <main class="main">
        
        <div class="header-top">
            <a href="Dashboard.php" class="btn-back-page"><i class="fa-solid fa-arrow-left"></i> Kembali</a>
            <h1 class="page-title">Riwayat Pemesanan</h1>
        </div>

        <?php if (empty($orderHistory)): ?>
            <div class="empty-state">
                <i class="fa-regular fa-bag-shopping"></i>
                <p>Belum ada riwayat pemesanan.<br>Lakukan pembelian produk agar muncul di sini.</p>
            </div>
        <?php else: ?>
            <?php foreach ($orderHistory as $order): ?>
                <div class="order-card">
                    <div class="card-left">
                        <div class="order-id"><?= sanitize_text($order['order_id']) ?></div>
                        <div class="service-name"><?= sanitize_text($order['service_name']) ?></div>
                        <div class="sub-info">Jumlah: <?= sanitize_text($order['quantity']) ?> pcs</div>
                        <div class="sub-info">Metode: <?= sanitize_text($order['payment_method']) ?></div>
                        <?php if (!empty($order['payment_note'])): ?>
                        <div class="sub-info" style="color:var(--lime); font-weight:600;"><?= sanitize_text($order['payment_note']) ?></div>
                        <?php endif; ?>
                        <div class="sub-info">Pengiriman: <?= sanitize_text($order['shipping']) ?></div>
                        <div class="price">Rp <?= number_format((int)$order['total'], 0, ',', '.') ?></div>
                    </div>

                    <div class="card-right">
                        <div class="order-date"><?= sanitize_text($order['date']) ?> <?= sanitize_text($order['time']) ?></div>
                        <?php
                            $st = $order['status'];
                            if ($st === 'Selesai') { $stClass = 'status-done'; $stIcon = 'fa-circle-check'; }
                            elseif ($st === 'Dalam Pengiriman') { $stClass = 'status-ship'; $stIcon = 'fa-truck'; }
                            elseif ($st === 'Sedang Dikemas') { $stClass = 'status-pack'; $stIcon = 'fa-box'; }
                            else { $stClass = 'status-new'; $stIcon = 'fa-inbox'; }
                        ?>
                        <div class="status-badge <?= $stClass ?>">
                            <i class="fa-solid <?= $stIcon ?>"></i> <?= sanitize_text($st) ?>
                        </div>
                        <div class="action-buttons">
                            <button class="btn btn-detail" type="button"
                                data-order-id="<?= sanitize_text($order['order_id']) ?>"
                                data-service="<?= sanitize_text($order['service_name']) ?>"
                                data-quantity="<?= sanitize_text($order['quantity']) ?>"
                                data-payment="<?= sanitize_text($order['payment_method']) ?>"
                                data-shipping="<?= sanitize_text($order['shipping']) ?>"
                                data-recipient-name="<?= sanitize_text($order['recipient_name']) ?>"
                                data-recipient-phone="<?= sanitize_text($order['recipient_phone']) ?>"
                                data-recipient-address="<?= sanitize_text($order['recipient_address']) ?>"
                                data-total="<?= sanitize_text($order['total']) ?>"
                                data-date="<?= sanitize_text($order['date']) ?>"
                                data-time="<?= sanitize_text($order['time']) ?>"
                                data-status="<?= sanitize_text($order['status']) ?>"
                                data-payment-note="<?= sanitize_text($order['payment_note'] ?? '') ?>"
                            >Lihat Detail</button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

    </main>
</div>

<div id="orderDetailModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <span class="close-btn" onclick="closeModal()">&times;</span>
            <h2>Detail Pemesanan</h2>
        </div>
        <div class="modal-body">
            <div class="status-stepper" id="detailStepper">
                <div class="step" data-step="Pesanan Masuk">
                    <div class="step-dot"><i class="fa-solid fa-inbox"></i></div>
                    <div class="step-label">Pesanan Masuk</div>
                </div>
                <div class="step-line"></div>
                <div class="step" data-step="Sedang Dikemas">
                    <div class="step-dot"><i class="fa-solid fa-box"></i></div>
                    <div class="step-label">Sedang Dikemas</div>
                </div>
                <div class="step-line"></div>
                <div class="step" data-step="Dalam Pengiriman">
                    <div class="step-dot"><i class="fa-solid fa-truck"></i></div>
                    <div class="step-label">Dalam Pengiriman</div>
                </div>
                <div class="step-line"></div>
                <div class="step" data-step="Selesai">
                    <div class="step-dot"><i class="fa-solid fa-circle-check"></i></div>
                    <div class="step-label">Selesai</div>
                </div>
            </div>
            <div class="detail-row">
                <span class="detail-label">NOMOR PEMESANAN</span>
                <span class="detail-value" id="detailOrderId">-</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">NAMA LAYANAN / PRODUK</span>
                <span class="detail-value" id="detailServiceName">-</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">JUMLAH</span>
                <span class="detail-value" id="detailQuantity">-</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">TOTAL HARGA</span>
                <span class="detail-value" id="detailTotal">-</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">METODE PEMBAYARAN</span>
                <span class="detail-value" id="detailPaymentMethod">-</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">JENIS PENGIRIMAN</span>
                <span class="detail-value" id="detailShipping">-</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">NAMA PENERIMA</span>
                <span class="detail-value" id="detailRecipientName">-</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">NO. HP</span>
                <span class="detail-value" id="detailRecipientPhone">-</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">ALAMAT</span>
                <span class="detail-value" id="detailRecipientAddress">-</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">TANGGAL</span>
                <span class="detail-value" id="detailDate">-</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">WAKTU</span>
                <span class="detail-value" id="detailTime">-</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">STATUS</span>
                <span class="detail-value status-success" id="detailStatus">-</span>
            </div>
            <div class="detail-row" id="detailPaymentNoteRow" style="display:none;">
                <span class="detail-label">KETERANGAN</span>
                <span class="detail-value" id="detailPaymentNote" style="color:var(--lime); font-weight:600;">-</span>
            </div>
        </div>
    </div>
</div>

<script>
    function openModal() {
        document.getElementById('orderDetailModal').style.display = 'flex';
    }

    function closeModal() {
        document.getElementById('orderDetailModal').style.display = 'none';
    }

    document.querySelectorAll('.btn-detail').forEach(button => {
        button.addEventListener('click', function() {
            const modal = document.getElementById('orderDetailModal');
            const orderId = this.dataset.orderId || '-';
            const service = this.dataset.service || '-';
            const quantity = this.dataset.quantity || '-';
            const total = this.dataset.total ? 'Rp ' + parseInt(this.dataset.total, 10).toLocaleString('id-ID') : '-';
            const paymentMethod = this.dataset.payment || '-';
            const shipping = this.dataset.shipping || '-';
            const recipientName = this.dataset.recipientName || '-';
            const recipientPhone = this.dataset.recipientPhone || '-';
            const recipientAddress = this.dataset.recipientAddress || '-';
            const date = this.dataset.date || '-';
            const time = this.dataset.time || '-';
            const status = this.dataset.status || 'Selesai';
            const paymentNote = this.dataset.paymentNote || '';

            document.getElementById('detailOrderId').innerText = orderId;
            document.getElementById('detailServiceName').innerText = service;
            document.getElementById('detailQuantity').innerText = quantity + ' pcs';
            document.getElementById('detailTotal').innerText = total;
            document.getElementById('detailPaymentMethod').innerText = paymentMethod;
            document.getElementById('detailShipping').innerText = shipping;
            document.getElementById('detailRecipientName').innerText = recipientName;
            document.getElementById('detailRecipientPhone').innerText = recipientPhone;
            document.getElementById('detailRecipientAddress').innerText = recipientAddress;
            document.getElementById('detailDate').innerText = date;
            document.getElementById('detailTime').innerText = time;
            document.getElementById('detailStatus').innerText = status;

            const isPickup = (shipping === 'Ambil di Toko');

            document.querySelectorAll('#detailStepper .step').forEach(step => {
                const s = step.dataset.step;
                if (isPickup && (s === 'Sedang Dikemas' || s === 'Dalam Pengiriman')) {
                    step.style.display = 'none';
                } else {
                    step.style.display = '';
                }
            });
            const stepLines = document.querySelectorAll('#detailStepper .step-line');
            if (isPickup) {
                stepLines.forEach((line, i) => { line.style.display = i === 0 ? '' : 'none'; });
            } else {
                stepLines.forEach(line => { line.style.display = ''; });
            }

            const statusOrder = isPickup
                ? ['Pesanan Masuk', 'Selesai']
                : ['Pesanan Masuk', 'Sedang Dikemas', 'Dalam Pengiriman', 'Selesai'];
            const currentIdx = statusOrder.indexOf(status);
            document.querySelectorAll('#detailStepper .step').forEach((step) => {
                if (step.style.display === 'none') return;
                step.classList.remove('step-done', 'step-active');
                const stepIdx = statusOrder.indexOf(step.dataset.step);
                if (stepIdx < currentIdx) step.classList.add('step-done');
                else if (stepIdx === currentIdx) step.classList.add('step-active');
            });
            document.querySelectorAll('#detailStepper .step-line').forEach((line, i) => {
                if (line.style.display === 'none') return;
                line.classList.toggle('line-done', i < currentIdx);
            });

            const noteRow = document.getElementById('detailPaymentNoteRow');
            const noteEl = document.getElementById('detailPaymentNote');
            if (paymentNote && noteRow && noteEl) {
                noteEl.innerText = paymentNote;
                noteRow.style.display = '';
            } else if (noteRow) {
                noteRow.style.display = 'none';
            }

            openModal();
        });
    });

    window.addEventListener('click', function(event) {
        const modal = document.getElementById('orderDetailModal');
        if (event.target === modal) {
            closeModal();
        }
    });
</script>
</body>
</html>