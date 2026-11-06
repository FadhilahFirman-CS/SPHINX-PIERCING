<?php
require_once __DIR__ . '/auth_admin.php';
require_once __DIR__ . '/pesanan_helper.php';
$id = trim($_GET['id'] ?? 'ORD-082');

$detail_map = [
    'ORD-082' => [
        'no'      => '#082',
        'customer'=> 'Bob R.',
        'layanan' => 'Browsur AS',
        'nama'    => 'Bob R.',
        'telepon' => '083114456',
        'alamat'  => 'Jl Merdeka No. 5',
        'status'  => 'diproses',
        'items'   => [
            ['nama' => 'Browsur AS - 500 lembar', 'harga' => 'Rp15.000'],
            ['nama' => 'Finishing Laminasi',       'harga' => 'Rp45.000'],
        ],
        'total'        => 'Rp60.000',
        'progres'      => 1,
        'jadwal_tgl'   => 'Rabu, 18 Juni 2026',
        'jadwal_jam'   => '11:00',
        'jadwal_status'=> 'Slot sudah dikonfirmasi di Kelola Jadwal',
    ],
    'ORD-083' => [
        'no'      => '#083',
        'customer'=> 'Citra S.',
        'layanan' => 'Browsur AS',
        'nama'    => 'Citra S.',
        'telepon' => '081234567',
        'alamat'  => 'Jl. Sudirman No. 10',
        'status'  => 'selesai',
        'items'   => [
            ['nama' => 'Browsur AS - 250 lembar', 'harga' => 'Rp10.000'],
        ],
        'total'        => 'Rp10.000',
        'progres'      => 2,
        'jadwal_tgl'   => 'Rabu, 18 Juni 2026',
        'jadwal_jam'   => '11:00',
        'jadwal_status'=> 'Slot sudah dikonfirmasi di Kelola Jadwal',
    ],
    'ORD-084' => [
        'no'      => '#084',
        'customer'=> 'Dimas T.',
        'layanan' => 'Browsur AS',
        'nama'    => 'Dimas T.',
        'telepon' => '089876543',
        'alamat'  => 'Jl. Mawar No. 3',
        'status'  => 'baru',
        'items'   => [
            ['nama' => 'Browsur AS - 1000 lembar', 'harga' => 'Rp25.000'],
        ],
        'total'        => 'Rp25.000',
        'progres'      => 0,
        'jadwal_tgl'   => '',
        'jadwal_jam'   => '',
        'jadwal_status'=> 'Belum dijadwalkan',
    ],
    'ORD-085' => [
        'no'      => '#085',
        'customer'=> 'Dewi S.',
        'layanan' => 'Browsur AS',
        'nama'    => 'Dewi S.',
        'telepon' => '082222333',
        'alamat'  => 'Jl. Diponegoro No. 7',
        'status'  => 'selesai',
        'items'   => [
            ['nama' => 'Browsur AS - 500 lembar', 'harga' => 'Rp15.000'],
            ['nama' => 'Desain Custom',            'harga' => 'Rp20.000'],
        ],
        'total'        => 'Rp35.000',
        'progres'      => 2,
        'jadwal_tgl'   => 'Rabu, 18 Juni 2026',
        'jadwal_jam'   => '11:00',
        'jadwal_status'=> 'Slot sudah dikonfirmasi di Kelola Jadwal',
    ],
    'ORD-086' => [
        'no'      => '#086',
        'customer'=> 'Rina M.',
        'layanan' => 'Jasa Piercing',
        'nama'    => 'Rina M.',
        'telepon' => '081333444',
        'alamat'  => 'Jl. Anggrek No. 12',
        'status'  => 'baru',
        'items'   => [
            ['nama' => 'Jasa Piercing Telinga', 'harga' => 'Rp50.000'],
        ],
        'total'        => 'Rp50.000',
        'progres'      => 0,
        'jadwal_tgl'   => 'Jumat, 20 Juni 2026',
        'jadwal_jam'   => '13:00',
        'jadwal_status'=> 'Menunggu konfirmasi',
    ],
    'ORD-087' => [
        'no'      => '#087',
        'customer'=> 'Anton W.',
        'layanan' => 'Jasa Piercing',
        'nama'    => 'Anton W.',
        'telepon' => '087555666',
        'alamat'  => 'Jl. Kenanga No. 2',
        'status'  => 'batal',
        'items'   => [
            ['nama' => 'Jasa Piercing Hidung', 'harga' => 'Rp75.000'],
        ],
        'total'        => 'Rp75.000',
        'progres'      => 0,
        'jadwal_tgl'   => '',
        'jadwal_jam'   => '',
        'jadwal_status'=> 'Pesanan dibatalkan',
    ],
];

$id        = array_key_exists($id, $detail_map) ? $id : 'ORD-082';
$detail    = $detail_map[$id];
$steps     = ['Diterima', 'Siap Ambil', 'Selesai'];
$label_map = ['baru' => 'Baru', 'diproses' => 'Diproses', 'selesai' => 'Selesai', 'batal' => 'Batal'];
$progres   = (int)$detail['progres'];
$fill_pct  = $progres === 0 ? '0%' : ($progres === 1 ? '50%' : '100%');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Pesanan - Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"/>
    <link rel="stylesheet" href="../css-admin/admin_dashboard.css?v=2">
    <link rel="stylesheet" href="../css-admin/responsive_unified.css?v=2">
    <link rel="stylesheet" href="../css-admin/kelola_pesanan.css">
    <link rel="stylesheet" href="../css-admin/modal_global.css">
    <script src="../js-admin/modal_global.js"></script>
</head>
<body>

<div class="admin-wrapper">

    <!-- Sidebar -->
    <aside class="admin-sidebar">
        <div class="sidebar-logo">
            <img src="../gambar/logo2.jpeg" alt="Logo">
            <span>@sphinx_piercing</span>
        </div>

        <ul class="sidebar-menu">
            <li>
                <a href="Dashboard-admin.php">
                    <i class="fa-solid fa-house"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li>
                <a href="Customer-admin.php">
                    <i class="fa-solid fa-users"></i>
                    <span>Customer</span>
                </a>
            </li>
            <li>
                <a href="Produk-admin.php">
                    <i class="fa-solid fa-box-open"></i>
                    <span>Produk</span>
                </a>
            </li>
            <li>
                <a href="KelolaJadwal-admin.php">
                    <i class="fa-solid fa-calendar-days"></i>
                    <span>Kelola Jadwal</span>
                </a>
            </li>
            <li class="active">
                <a href="KelolaPesanan-admin.php">
                    <i class="fa-solid fa-cube"></i>
                    <span>Kelola Pesanan</span>
                    <?php if ($pending_pesanan_count > 0): ?>
                    <span class="sidebar-badge"><?= $pending_pesanan_count ?></span>
                    <?php endif; ?>
                </a>
            </li>
        </ul>

        <div class="sidebar-bottom">
            <a href="#" class="sidebar-bantuan">
                <i class="fa-solid fa-circle-question"></i>
                <span>Bantuan</span>
            </a>
            <a href="logout-admin.php" class="sidebar-logout">
                <i class="fa-solid fa-right-from-bracket"></i>
                <span>Logout</span>
            </a>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="admin-main">

        <!-- Top Bar -->
        <div class="admin-topbar">
            <div class="topbar-title">
                <h1>Kelola Pesanan</h1>
                <span class="breadcrumb">Dashboard &gt; Kelola Pesanan &gt; Detail</span>
            </div>
            <div class="topbar-icons">
                <a href="Notifikasi-admin.php" class="icon-btn" title="Notifikasi"><i class="fa-regular fa-bell"></i></a>
                <a href="Profile-admin.php" class="icon-btn" title="Profil"><i class="fa-regular fa-user"></i></a>
            </div>
        </div>

        <!-- Banner -->
        <div class="admin-banner" style="display:flex;align-items:center;gap:10px;">
            <a href="KelolaPesanan-admin.php">Kelola Pesanan</a>
            <span style="color:#1a1135;font-weight:700;">&nbsp;&gt;&nbsp;</span>
            <span style="color:#1a1135;font-weight:600;">Detail</span>
        </div>

        <a href="KelolaPesanan-admin.php" class="back-link">
            <i class="fa-solid fa-arrow-left"></i> Kembali ke Daftar Pesanan
        </a>

        <!-- Detail Card (centered, max-width) -->
        <div style="max-width:520px;">

            <!-- Header Bar -->
            <div class="detail-header-bar">
                <div class="dh-title">
                    <i class="fa-solid fa-receipt"></i>
                    Kelola Pesanan - Detail
                </div>
                <span class="order-id-tag">#<?= htmlspecialchars($id) ?></span>
            </div>

            <div class="detail-body">

                <!-- Info Customer -->
                <div>
                    <div class="section-label">Info Customer</div>
                    <div class="info-customer-grid">
                        <span class="status-tag badge <?= htmlspecialchars($detail['status']) ?>">
                            <?= htmlspecialchars($label_map[$detail['status']] ?? ucfirst($detail['status'])) ?>
                        </span>
                        <div class="info-row">
                            <i class="fa-regular fa-user"></i>
                            <span class="ir-label">Nama</span>
                            <span class="ir-value"><?= htmlspecialchars($detail['nama']) ?></span>
                        </div>
                        <div class="info-row">
                            <i class="fa-solid fa-phone"></i>
                            <span class="ir-label">Telepon</span>
                            <span class="ir-value"><?= htmlspecialchars($detail['telepon']) ?></span>
                        </div>
                        <div class="info-row">
                            <i class="fa-solid fa-location-dot"></i>
                            <span class="ir-label">Alamat</span>
                            <span class="ir-value"><?= htmlspecialchars($detail['alamat']) ?></span>
                        </div>
                    </div>
                </div>

                <!-- Item Pesanan -->
                <div>
                    <div class="section-label">Item Pesanan</div>
                    <div class="item-list">
                        <?php foreach ($detail['items'] as $item): ?>
                        <div class="item-row">
                            <span class="item-name"><?= htmlspecialchars($item['nama']) ?></span>
                            <span class="item-price"><?= htmlspecialchars($item['harga']) ?></span>
                        </div>
                        <?php endforeach; ?>
                        <div class="item-total">
                            <span class="total-label">Total</span>
                            <span class="total-value"><?= htmlspecialchars($detail['total']) ?></span>
                        </div>
                    </div>
                </div>

                <!-- Progres Pesanan -->
                <div>
                    <div class="progres-wrap">
                        <div class="section-label" style="margin-bottom:0;">
                            <i class="fa-regular fa-clock" style="color:var(--lime);margin-right:6px;"></i>
                            PROGRES PESANAN
                        </div>
                        <div class="progres-bar" style="margin-top:18px;padding:0 16px;box-sizing:border-box;">
                            <div class="pb-line-fill" style="width:<?= $fill_pct ?>;left:calc(16px + 16px);"></div>
                            <?php foreach ($steps as $idx => $step): ?>
                            <div class="progres-step">
                                <div class="ps-dot <?= $idx < $progres ? 'done' : ($idx === $progres ? 'active' : '') ?>">
                                    <?php if ($idx < $progres): ?>
                                        <i class="fa-solid fa-check" style="font-size:12px;"></i>
                                    <?php else: ?>
                                        <i class="fa-solid fa-circle" style="font-size:8px;"></i>
                                    <?php endif; ?>
                                </div>
                                <span class="ps-label-text <?= $idx < $progres ? 'done' : ($idx === $progres ? 'active' : '') ?>">
                                    <?= htmlspecialchars($step) ?>
                                </span>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- Info Jadwal -->
                <div class="jadwal-info-box">
                    <div class="ji-header">
                        <i class="fa-regular fa-calendar-check"></i>
                        Info Jadwal Ambil - Terhubung Ke Kalender
                    </div>
                    <?php if ($detail['jadwal_tgl']): ?>
                    <div class="ji-row">
                        Jadwal ambil : <span><?= htmlspecialchars($detail['jadwal_tgl']) ?> - <?= htmlspecialchars($detail['jadwal_jam']) ?></span>
                    </div>
                    <?php endif; ?>
                    <div class="ji-row"><?= htmlspecialchars($detail['jadwal_status']) ?></div>
                    <a href="KelolaJadwal-admin.php" class="btn-lihat-kalender">
                        <i class="fa-solid fa-arrow-left" style="font-size:11px;"></i>
                        Lihat Kalender Jadwal
                    </a>
                </div>

                <!-- Action Buttons -->
                <div class="detail-actions">
                    <?php if (in_array($detail['status'], ['baru', 'diproses'])): ?>
                    <button class="btn-action btn-tolak" onclick="konfirmAksi('tolak')">
                        <i class="fa-solid fa-xmark"></i> Tolak
                    </button>
                    <?php endif; ?>
                    <button class="btn-action btn-hubungi" onclick="konfirmAksi('hubungi')">
                        <i class="fa-regular fa-comment"></i> Hubungi
                    </button>
                    <?php if ($detail['status'] === 'baru'): ?>
                    <button class="btn-action btn-konfirm" onclick="konfirmAksi('konfirm')">
                        <i class="fa-solid fa-check"></i> Konfirmasi
                    </button>
                    <?php elseif ($detail['status'] === 'diproses'): ?>
                    <button class="btn-action btn-konfirm" onclick="konfirmAksi('konfirm')">
                        <i class="fa-solid fa-check"></i> Tandai Selesai
                    </button>
                    <?php endif; ?>
                </div>

            </div><!-- /detail-body -->
        </div>

    </main>
</div>

<script>
// ===== DATA PESANAN TERPILIH =====
const ORDER = <?= json_encode([
    'id'         => $id,
    'layanan'    => $detail['layanan'],
    'progres'    => (int)$detail['progres'],
    'status'     => $detail['status'],
    'customer'   => $detail['nama'],
    'jadwal_tgl' => $detail['jadwal_tgl'],
    'jadwal_jam' => $detail['jadwal_jam'],
], JSON_UNESCAPED_UNICODE) ?>;

const JADWAL_STORAGE_KEY = 'sphinx_jadwal_v1';
const PENDING_ASSIGN_KEY = 'sphinx_pending_jadwal_assign_v1';

function parseTanggalIndonesia(jadwalTgl) {
    if (!jadwalTgl) return null;
    var bulanIdx = {
        'januari':1, 'jan':1,
        'februari':2, 'feb':2,
        'maret':3, 'mar':3,
        'april':4, 'apr':4,
        'mei':5,
        'juni':6, 'jun':6,
        'juli':7, 'jul':7,
        'agustus':8, 'agu':8, 'agt':8,
        'september':9, 'sep':9,
        'oktober':10, 'okt':10,
        'november':11, 'nov':11,
        'desember':12, 'des':12
    };
    var clean = String(jadwalTgl).replace(/^[^0-9]*/, '').trim();
    var match = clean.match(/(\d{1,2})\s+([A-Za-z]+)\s+(\d{4})/);
    if (!match) return null;
    var tgl = parseInt(match[1], 10);
    var bln = bulanIdx[String(match[2]).toLowerCase()] || null;
    var thn = parseInt(match[3], 10);
    if (!tgl || !bln || !thn) return null;
    return { tanggal: tgl, bulan: bln, tahun: thn };
}

function simpanPendingJadwal(orderId, customer, jadwalTgl, jadwalJam) {
    var parsed = parseTanggalIndonesia(jadwalTgl);
    var payload = {
        orderId: orderId,
        customer: customer,
        requestedTanggalRaw: jadwalTgl || '',
        requestedJam: jadwalJam || '',
        requestedTanggal: parsed,
        createdAt: Date.now()
    };
    localStorage.setItem(PENDING_ASSIGN_KEY, JSON.stringify(payload));
}

function simpanKePiercingJadwal(orderId, customer, jadwalTgl, jadwalJam) {
    var bulanIdx = {'Januari':1,'Februari':2,'Maret':3,'April':4,'Mei':5,'Juni':6,
                    'Juli':7,'Agustus':8,'September':9,'Oktober':10,'November':11,'Desember':12};
    var parts = jadwalTgl.replace(/^[A-Za-z]+,\s*/, '').trim().split(' ');
    var tgl   = parseInt(parts[0]);
    var bln   = bulanIdx[parts[1]] || 6;
    var thn   = parseInt(parts[2]) || 2026;
    if (!tgl) return;
    var jam = jadwalJam || '09:00';
    var key = thn + '-' + bln;
    var raw = {};
    try { raw = JSON.parse(localStorage.getItem(JADWAL_STORAGE_KEY) || '{}'); } catch(e) { raw = {}; }
    if (!raw[key]) raw[key] = { jadwalDates: {}, slotData: {}, pesananData: {} };
    if (!raw[key].pesananData[tgl]) raw[key].pesananData[tgl] = [];
    var sudahAda = raw[key].pesananData[tgl].some(function(p){ return p.id === orderId; });
    if (!sudahAda) {
        raw[key].pesananData[tgl].push({ id: orderId, customer: customer, layanan: 'Jasa Piercing', jam: jam, status: 'diproses' });
    }
    if (!raw[key].slotData[tgl]) {
        raw[key].slotData[tgl] = {'08:00':'tersedia','09:00':'tersedia','10:00':'tersedia',
                                  '11:00':'tersedia','13:00':'tersedia','14:00':'tersedia','15:00':'tersedia'};
    }
    if (raw[key].slotData[tgl][jam] !== 'slot_penuh') {
        raw[key].slotData[tgl][jam] = 'ada_pesanan';
    }
    raw[key].jadwalDates[tgl] = 'ada_pesanan';
    localStorage.setItem(JADWAL_STORAGE_KEY, JSON.stringify(raw));
}

function konfirmAksi(aksi) {
    const id       = ORDER.id;
    const layanan  = ORDER.layanan;
    const status   = ORDER.status;
    const customer = ORDER.customer;

    if (aksi === 'tolak') {
        SphinxModal.hapus(
            'Pesanan ' + id + ' atas nama ' + customer + ' akan ditolak. Tindakan ini tidak dapat dibatalkan.',
            () => SphinxModal.berhasil('Pesanan ' + id + ' berhasil ditolak.', () => { window.location.href = 'KelolaPesanan-admin.php'; })
        );
        return;
    }

    if (aksi === 'hubungi') {
        SphinxModal.konfirmasi(
            'Hubungi Customer',
            'Apakah kamu yakin ingin menghubungi ' + customer + ' untuk pesanan ' + id + '?',
            () => SphinxModal.berhasil('Menghubungi customer untuk pesanan ' + id + '.')
        );
        return;
    }

    // ===== KONFIRMASI: logika berdasarkan layanan & status =====
    if (layanan === 'Jasa Piercing') {
        if (status === 'baru') {
            SphinxModal.konfirmasi(
                'Konfirmasi Jasa Piercing',
                'Setelah klik Lanjutkan, kamu akan diarahkan ke Kelola Jadwal untuk memilih slot sesuai pilihan customer. Jika slot pilihan customer penuh, kamu bisa kirim notifikasi ke customer dari sana.',
                () => {
                    simpanPendingJadwal(id, customer, ORDER.jadwal_tgl, ORDER.jadwal_jam);
                    window.location.href = 'KelolaJadwal-admin.php?assign=' + encodeURIComponent(id);
                }
            );
        } else if (status === 'diproses') {
            SphinxModal.konfirmasi(
                'Pesanan Sudah Selesai?',
                'Apakah layanan piercing untuk pesanan ' + id + ' sudah selesai? Status pesanan akan diubah menjadi Selesai.',
                () => SphinxModal.berhasil('Pesanan ' + id + ' berhasil ditandai selesai!', () => { window.location.href = 'KelolaPesanan-admin.php'; })
            );
        } else {
            SphinxModal.berhasil('Pesanan ' + id + ' sudah selesai atau dibatalkan.');
        }
    } else {
        if (status === 'baru') {
            showKonfirmProdukModal(id, customer);
        } else if (status === 'diproses') {
            SphinxModal.konfirmasi(
                'Pesanan Sudah Diterima?',
                'Apakah pesanan ' + id + ' sudah diterima / sampai ke ' + customer + '? Status pesanan akan diubah menjadi Selesai.',
                () => SphinxModal.berhasil('Pesanan ' + id + ' berhasil ditandai selesai!', () => { window.location.href = 'KelolaPesanan-admin.php'; })
            );
        } else {
            SphinxModal.berhasil('Pesanan ' + id + ' sudah selesai.');
        }
    }
}

function showKonfirmProdukModal(id, customer) {
    var existing = document.getElementById('modal-metode-kirim');
    if (existing) existing.remove();
    var modal = document.createElement('div');
    modal.id = 'modal-metode-kirim';
    modal.style.cssText = 'position:fixed;inset:0;background:rgba(0,0,0,0.6);z-index:10000;display:flex;align-items:center;justify-content:center;';
    modal.innerHTML = `
    <div style="background:#1f1542;border:1px solid rgba(255,255,255,0.12);border-radius:16px;padding:28px 28px 24px;width:360px;max-width:92vw;">
        <div style="font-size:15px;font-weight:700;color:#e8e8f0;margin-bottom:6px;">
            <i class="fa-solid fa-truck" style="color:#7FFF00;margin-right:8px;"></i>Konfirmasi Pesanan Produk
        </div>
        <div style="font-size:13px;color:#9a9ab0;margin-bottom:20px;line-height:1.7;">
            Pesanan <strong style="color:#e8e8f0;">${id}</strong> dari <strong style="color:#e8e8f0;">${customer}</strong> siap diproses.<br>
            Pilih metode pengiriman:
        </div>
        <div style="display:flex;gap:10px;margin-bottom:20px;">
            <button onclick="pilihMetodeKirim('kurir','${id}')" style="flex:1;background:#241750;border:2px solid rgba(255,255,255,0.1);border-radius:10px;padding:14px 8px;cursor:pointer;transition:all 0.2s;font-family:Poppins,sans-serif;color:#e8e8f0;font-size:13px;" onmouseover="this.style.borderColor='#7FFF00'" onmouseout="this.style.borderColor='rgba(255,255,255,0.1)'">
                <i class="fa-solid fa-truck" style="color:#7FFF00;font-size:20px;display:block;margin-bottom:8px;"></i>
                Dikirim Kurir
            </button>
            <button onclick="pilihMetodeKirim('toko','${id}')" style="flex:1;background:#241750;border:2px solid rgba(255,255,255,0.1);border-radius:10px;padding:14px 8px;cursor:pointer;transition:all 0.2s;font-family:Poppins,sans-serif;color:#e8e8f0;font-size:13px;" onmouseover="this.style.borderColor='#7FFF00'" onmouseout="this.style.borderColor='rgba(255,255,255,0.1)'">
                <i class="fa-solid fa-store" style="color:#4a9eff;font-size:20px;display:block;margin-bottom:8px;"></i>
                Ambil di Toko
            </button>
        </div>
        <button onclick="document.getElementById('modal-metode-kirim').remove()" style="width:100%;background:transparent;border:1px solid rgba(255,255,255,0.1);border-radius:8px;padding:9px;color:#9a9ab0;font-family:Poppins,sans-serif;font-size:13px;cursor:pointer;">Batal</button>
    </div>`;
    document.body.appendChild(modal);
    modal.addEventListener('click', function(e){ if(e.target===modal) modal.remove(); });
}

function pilihMetodeKirim(metode, orderId) {
    var el = document.getElementById('modal-metode-kirim');
    if (el) el.remove();
    var label = metode === 'kurir' ? 'dikirim melalui kurir' : 'siap diambil di toko';
    SphinxModal.berhasil(
        'Pesanan ' + orderId + ' dikonfirmasi - akan ' + label + '!',
        () => { window.location.href = 'KelolaPesanan-admin.php'; }
    );
}
</script>

</body>
</html>
