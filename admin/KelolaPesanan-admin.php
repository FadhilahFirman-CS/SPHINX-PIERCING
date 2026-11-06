<?php
require_once __DIR__ . '/auth_admin.php';
require_once __DIR__ . '/pesanan_helper.php';

// =====================================================================
// Query pesanan dari database dengan filter
// =====================================================================
$search    = trim($_GET['search']  ?? '');
$f_status  = isset($_GET['f_status'])  && is_array($_GET['f_status'])  ? array_filter($_GET['f_status'],  fn($s) => in_array($s, ['baru','diproses','selesai','batal'], true)) : [];
$f_layanan = isset($_GET['f_layanan']) && is_array($_GET['f_layanan']) ? $_GET['f_layanan'] : [];
$f_dari    = $_GET['f_dari']   ?? '';
$f_sampai  = $_GET['f_sampai'] ?? '';
$f_urut    = $_GET['f_urut']   ?? 'terbaru';
$hasFilter = !empty($f_status) || !empty($f_layanan) || $f_dari || $f_sampai;

$where  = ['1=1'];
$params = [];
$types  = '';

if ($search) {
    $where[]  = '(p.kode LIKE ? OR c.nama LIKE ? OR p.layanan LIKE ?)';
    $s = "%{$search}%";
    $params = array_merge($params, [$s, $s, $s]);
    $types .= 'sss';
}
foreach ($f_status as $st) {
    $where[] = 'p.status = ?';
    $params[] = $st; $types .= 's';
}
if (!empty($f_layanan)) {
    $ph = implode(',', array_fill(0, count($f_layanan), '?'));
    $where[] = "p.layanan IN ($ph)";
    foreach ($f_layanan as $l) { $params[] = $l; $types .= 's'; }
}
if ($f_dari)   { $where[] = 'p.tgl_masuk >= ?'; $params[] = $f_dari;   $types .= 's'; }
if ($f_sampai) { $where[] = 'p.tgl_masuk <= ?'; $params[] = $f_sampai; $types .= 's'; }

$order = $f_urut === 'terlama' ? 'p.tgl_masuk ASC, p.id ASC' : 'p.tgl_masuk DESC, p.id DESC';
$sql = "SELECT p.kode AS id, p.kode AS no, c.nama AS customer, p.layanan,
               DATE_FORMAT(j.tanggal,'%d %b %Y') AS jadwal_tgl, j.jam AS jadwal_jam,
               p.status, p.tgl_masuk, p.total
        FROM pesanan p
        JOIN customers c ON c.id = p.customer_id
        LEFT JOIN jadwal j ON j.pesanan_id = p.id
        WHERE " . implode(' AND ', $where) . "
        ORDER BY $order";

$stmtP = $conn->prepare($sql);
if ($types && $stmtP) {
    $stmtP->bind_param($types, ...$params);
}
if ($stmtP) { $stmtP->execute(); $pesanan = $stmtP->get_result()->fetch_all(MYSQLI_ASSOC); $stmtP->close(); }
else { $pesanan = []; }

$selected_id = $_GET['id'] ?? ($pesanan[0]['id'] ?? null);
$selected    = null;
foreach ($pesanan as $p) { if ($p['id'] === $selected_id) { $selected = $p; break; } }
if (!$selected && !empty($pesanan)) { $selected = $pesanan[0]; $selected_id = $selected['id']; }

// Build query string untuk row click
$_row_carry = [];
if ($search)               { $_row_carry['search']    = $search; }
foreach ($f_status  as $v) { $_row_carry['f_status'][]  = $v; }
foreach ($f_layanan as $v) { $_row_carry['f_layanan'][] = $v; }
if ($f_dari)               { $_row_carry['f_dari']    = $f_dari; }
if ($f_sampai)             { $_row_carry['f_sampai']  = $f_sampai; }
if ($f_urut !== 'terbaru') { $_row_carry['f_urut']    = $f_urut; }
$_row_qs = $_row_carry ? '&' . http_build_query($_row_carry) : '';

// =====================================================================
// Detail pesanan yang dipilih
// =====================================================================
$detail = null;
if ($selected_id) {
    $sd = $conn->prepare(
        "SELECT p.*, c.nama, c.telepon, c.alamat,
                j.tanggal AS jdwl_tgl, j.jam AS jdwl_jam, j.status AS jdwl_status
         FROM pesanan p
         JOIN customers c ON c.id = p.customer_id
         LEFT JOIN jadwal j ON j.pesanan_id = p.id
         WHERE p.kode = ? LIMIT 1"
    );
    $sd->bind_param('s', $selected_id); $sd->execute();
    $detail = $sd->get_result()->fetch_assoc(); $sd->close();

    if ($detail) {
        $si = $conn->prepare("SELECT nama_produk AS nama, harga FROM pesanan_items WHERE pesanan_id = (SELECT id FROM pesanan WHERE kode=? LIMIT 1)");
        $si->bind_param('s', $selected_id); $si->execute();
        $detail['items'] = $si->get_result()->fetch_all(MYSQLI_ASSOC); $si->close();
    }
}

$steps = ['Diterima', 'Siap Ambil', 'Selesai'];

$label_map = ['baru' => 'Baru', 'diproses' => 'Diproses', 'selesai' => 'Selesai', 'batal' => 'Batal'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Pesanan - Admin</title>
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
                <span class="breadcrumb">Dashboard &gt; Kelola Pesanan</span>
            </div>
            <div class="topbar-icons">
                <a href="Notifikasi-admin.php" class="icon-btn" title="Notifikasi"><i class="fa-regular fa-bell"></i></a>
                <a href="Profile-admin.php" class="icon-btn" title="Profil"><i class="fa-regular fa-user"></i></a>
            </div>
        </div>

        <!-- Banner -->
        <div class="admin-banner">
            <a href="KelolaPesanan-admin.php">Kelola Pesanan</a>
        </div>

        <!-- Two-column layout -->
        <div class="pesanan-layout">

            <!-- LEFT: List -->
            <div class="pesanan-card">
                <div class="pesanan-card-header">
                    <h2><i class="fa-solid fa-cube" style="color:var(--lime);margin-right:8px;"></i>Kelola Pesanan - List</h2>
                    <div class="pch-controls">
                        <!-- Search -->
                        <form class="filter-form" method="GET" id="searchForm">
                            <?php if ($selected_id): ?><input type="hidden" name="id" value="<?= htmlspecialchars($selected_id) ?>"> <?php endif; ?>
                            <?php foreach ($f_status  as $v): ?><input type="hidden" name="f_status[]"  value="<?= htmlspecialchars($v) ?>"> <?php endforeach; ?>
                            <?php foreach ($f_layanan as $v): ?><input type="hidden" name="f_layanan[]" value="<?= htmlspecialchars($v) ?>"> <?php endforeach; ?>
                            <?php if ($f_dari):   ?><input type="hidden" name="f_dari"   value="<?= htmlspecialchars($f_dari) ?>">   <?php endif; ?>
                            <?php if ($f_sampai): ?><input type="hidden" name="f_sampai" value="<?= htmlspecialchars($f_sampai) ?>"> <?php endif; ?>
                            <?php if ($f_urut !== 'terbaru'): ?><input type="hidden" name="f_urut" value="<?= htmlspecialchars($f_urut) ?>"> <?php endif; ?>
                            <input type="text" name="search" placeholder="Cari no. pesanan atau nama.." value="<?= htmlspecialchars($search) ?>">
                            <button type="submit" class="btn-cari">Cari</button>
                        </form>
                        <!-- Filter toggle -->
                        <button type="button" id="btnFilter" class="btn-filter<?= $hasFilter ? ' active' : '' ?>" onclick="openFilter(this)">
                            <i class="fa-solid fa-sliders"></i> Filter
                            <?php if ($hasFilter): ?><span class="filter-dot"></span><?php endif; ?>
                        </button>
                    </div>
                </div>

                <!-- Filter Popup – dipindah ke level body via PHP, ditaruh sebelum </body> -->

                <div class="pesanan-table-wrap">
                    <table class="pesanan-tbl">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Customer</th>
                                <th>Layanan</th>
                                <th>Jadwal</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($pesanan)): ?>
                            <tr>
                                <td colspan="5" style="text-align:center;color:var(--text-soft);padding:30px;">
                                    Tidak ada pesanan ditemukan.
                                </td>
                            </tr>
                            <?php else: foreach ($pesanan as $p): ?>
                            <tr onclick="window.location='?id=<?= urlencode($p['id']) ?><?= $_row_qs ?>'"
                                style="<?= $p['id'] === $selected_id ? 'background:rgba(127,255,0,0.06);' : '' ?>">
                                <td class="order-no"><?= htmlspecialchars($p['no']) ?></td>
                                <td><?= htmlspecialchars($p['customer']) ?></td>
                                <td><?= htmlspecialchars($p['layanan']) ?></td>
                                <td>
                                    <?php if ($p['jadwal_tgl']): ?>
                                        <span class="jadwal-cell">
                                            <span class="jadwal-date"><?= htmlspecialchars($p['jadwal_tgl']) ?></span>
                                            <span class="jadwal-time"> -<?= htmlspecialchars($p['jadwal_jam']) ?></span>
                                        </span>
                                    <?php else: ?>
                                        <span class="jadwal-cell belum">Belum dijadwalkan</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge <?= htmlspecialchars($p['status']) ?>">
                                        <?= htmlspecialchars($label_map[$p['status']] ?? ucfirst($p['status'])) ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- RIGHT: Detail -->
            <?php if ($detail): ?>
            <div>
                <!-- Header bar -->
                <div class="detail-header-bar">
                    <div class="dh-title">
                        <i class="fa-solid fa-receipt"></i>
                        Kelola Pesanan - Detail
                    </div>
                    <span class="order-id-tag">#<?= htmlspecialchars($selected_id) ?></span>
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
                            <?php foreach (($detail['items'] ?? []) as $item): ?>
                            <div class="item-row">
                                <span class="item-name"><?= htmlspecialchars($item['nama'] ?? $item['nama_produk'] ?? '') ?></span>
                                <span class="item-price">Rp <?= number_format((int)($item['harga'] ?? 0), 0, ',', '.') ?></span>
                            </div>
                            <?php endforeach; ?>
                            <div class="item-total">
                                <span class="total-label">Total</span>
                                <span class="total-value">Rp <?= number_format((int)($detail['total'] ?? 0), 0, ',', '.') ?></span>
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
                            <?php
                            $progres = (int)$detail['progres'];
                            // fill width: 0 step=0%, 1 step=50%, 2 step=100%
                            $fill_pct = $progres === 0 ? '0%' : ($progres === 1 ? '50%' : '100%');
                            ?>
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
                        <?php if ($detail['jdwl_tgl'] ?? $detail['jadwal_tgl'] ?? null): ?>
                        <div class="ji-row">
                            Jadwal ambil : <span><?= htmlspecialchars($detail['jdwl_tgl'] ?? $detail['jadwal_tgl'] ?? '') ?> - <?= htmlspecialchars($detail['jdwl_jam'] ?? $detail['jadwal_jam'] ?? '') ?></span>
                        </div>
                        <?php endif; ?>
                        <div class="ji-row"><?= htmlspecialchars($detail['jdwl_status'] ?? $detail['jadwal_status'] ?? 'Belum dijadwalkan') ?></div>
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
            </div><!-- /detail wrapper -->
            <?php else: ?>
            <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:14px;padding:40px;text-align:center;color:var(--text-soft);">
                <i class="fa-solid fa-cube" style="font-size:32px;margin-bottom:12px;display:block;color:var(--border);"></i>
                Pilih pesanan untuk melihat detail
            </div>
            <?php endif; ?>

        </div><!-- /pesanan-layout -->

    </main>
</div>

<!-- ===== FILTER POPUP (body level, position:fixed) ===== -->
<div id="filterPopup" class="fp-popup" style="display:none;">
    <div class="fp-header">
        <span>Filter Pesanan</span>
        <button type="button" class="fp-close" onclick="closeFilter()"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <form method="GET" id="filterForm">
        <input type="hidden" name="search" value="<?= htmlspecialchars($search) ?>">
        <?php if ($selected_id): ?><input type="hidden" name="id" value="<?= htmlspecialchars($selected_id) ?>"> <?php endif; ?>
        <!-- Status -->
        <div class="fp-section">
            <div class="fp-label">Status Pesanan</div>
            <div class="fp-chips">
                <?php foreach (['baru'=>'Baru','diproses'=>'Diproses','selesai'=>'Selesai','batal'=>'Batal'] as $val=>$lbl): ?>
                <label class="fp-chip badge <?= $val ?><?= in_array($val,$f_status) ? ' fp-chip-on' : '' ?>">
                    <input type="checkbox" name="f_status[]" value="<?= $val ?>" <?= in_array($val,$f_status)?'checked':'' ?>> <?= $lbl ?>
                </label>
                <?php endforeach; ?>
            </div>
        </div>
        <!-- Tanggal Masuk -->
        <div class="fp-section">
            <div class="fp-label">Tanggal Masuk Pesanan</div>
            <div class="fp-date-row">
                <div class="fp-date-col"><span class="fp-sub">Dari</span><input type="date" name="f_dari"   class="fp-date" value="<?= htmlspecialchars($f_dari) ?>"></div>
                <span class="fp-dash">&mdash;</span>
                <div class="fp-date-col"><span class="fp-sub">Sampai</span><input type="date" name="f_sampai" class="fp-date" value="<?= htmlspecialchars($f_sampai) ?>"></div>
            </div>
        </div>
        <!-- Layanan -->
        <div class="fp-section">
            <div class="fp-label">Layanan</div>
            <div class="fp-chips">
                <?php foreach (['Browsur AS','Jasa Piercing'] as $lay): ?>
                <label class="fp-chip fp-chip-plain<?= in_array($lay,$f_layanan) ? ' fp-chip-on' : '' ?>">
                    <input type="checkbox" name="f_layanan[]" value="<?= htmlspecialchars($lay) ?>" <?= in_array($lay,$f_layanan)?'checked':'' ?>> <?= htmlspecialchars($lay) ?>
                </label>
                <?php endforeach; ?>
            </div>
        </div>
        <!-- Urutkan -->
        <div class="fp-section">
            <div class="fp-label">Urutkan Berdasarkan</div>
            <div class="fp-radios">
                <label class="fp-radio"><input type="radio" name="f_urut" value="terbaru" <?= $f_urut!=='terlama'?'checked':'' ?>> Terbaru</label>
                <label class="fp-radio"><input type="radio" name="f_urut" value="terlama" <?= $f_urut==='terlama'?'checked':'' ?>> Terlama</label>
            </div>
        </div>
        <!-- Actions -->
        <div class="fp-actions">
            <button type="button" class="fp-btn-reset" onclick="resetFilter()">Reset</button>
            <button type="submit" class="fp-btn-apply">Terapkan Filter</button>
        </div>
    </form>
</div>

<script>
// ===== DATA PESANAN TERPILIH =====
const ORDER = <?= json_encode([
    'id'       => $selected_id ?? '',
    'layanan'  => $selected ? ($selected['layanan'] ?? '') : '',
    'progres'  => $detail ? (int)$detail['progres'] : 0,
    'status'   => $detail ? $detail['status'] : '',
    'customer' => $detail ? $detail['nama'] : '',
    'jadwal_tgl' => $detail ? $detail['jadwal_tgl'] : '',
    'jadwal_jam' => $detail ? $detail['jadwal_jam'] : '',
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

// Simpan pesanan Jasa Piercing ke localStorage Kelola Jadwal
function simpanKePiercingJadwal(orderId, customer, jadwalTgl, jadwalJam) {
    // Parse tanggal dari string "Jumat, 20 Juni 2026" → ambil angka tanggal & bulan
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

    // Tambahkan ke pesananData[tgl]
    if (!raw[key].pesananData[tgl]) raw[key].pesananData[tgl] = [];
    // Hindari duplikat
    var sudahAda = raw[key].pesananData[tgl].some(function(p){ return p.id === orderId; });
    if (!sudahAda) {
        raw[key].pesananData[tgl].push({
            id:       orderId,
            customer: customer,
            layanan:  'Jasa Piercing',
            jam:      jam,
            status:   'diproses'
        });
    }
    // Tandai slot jam sebagai ada_pesanan
    if (!raw[key].slotData[tgl]) {
        raw[key].slotData[tgl] = {'08:00':'tersedia','09:00':'tersedia','10:00':'tersedia',
                                  '11:00':'tersedia','13:00':'tersedia','14:00':'tersedia','15:00':'tersedia'};
    }
    if (raw[key].slotData[tgl][jam] !== 'slot_penuh') {
        raw[key].slotData[tgl][jam] = 'ada_pesanan';
    }
    // Tandai tanggal di kalender
    raw[key].jadwalDates[tgl] = 'ada_pesanan';

    localStorage.setItem(JADWAL_STORAGE_KEY, JSON.stringify(raw));
}

// ===== FILTER POPUP =====
var _filterBtn = null;
function openFilter(btn) {
    var popup = document.getElementById('filterPopup');
    if (popup.style.display === 'block') { closeFilter(); return; }
    _filterBtn = btn;
    var r = btn.getBoundingClientRect();
    popup.style.top  = (r.bottom + window.scrollY + 8) + 'px';
    popup.style.left = Math.max(8, r.right - 320) + 'px';
    popup.style.display = 'block';
}
function closeFilter() {
    document.getElementById('filterPopup').style.display = 'none';
}
function resetFilter() {
    var f = document.getElementById('filterForm');
    f.querySelectorAll('input[type=checkbox]').forEach(function(c){ c.checked = false; });
    f.querySelectorAll('input[type=date]').forEach(function(d){ d.value = ''; });
    var r = f.querySelector('input[name=f_urut][value=terbaru]');
    if (r) r.checked = true;
    f.submit();
}
document.addEventListener('click', function(e) {
    var popup = document.getElementById('filterPopup');
    var btn   = document.getElementById('btnFilter');
    if (popup && popup.style.display === 'block' && !popup.contains(e.target) && btn && !btn.contains(e.target)) {
        closeFilter();
    }
});
/* Sync chip visual state on change */
document.addEventListener('change', function(e) {
    if (e.target.type === 'checkbox' && e.target.closest('.fp-chip')) {
        var chip = e.target.closest('.fp-chip');
        chip.classList.toggle('fp-chip-on', e.target.checked);
    }
});

function konfirmAksi(aksi) {
    const id       = ORDER.id;
    const layanan  = ORDER.layanan;
    const progres  = ORDER.progres;
    const status   = ORDER.status;
    const customer = ORDER.customer;

    if (aksi === 'tolak') {
        SphinxModal.hapus(
            'Pesanan ' + id + ' atas nama ' + customer + ' akan ditolak. Tindakan ini tidak dapat dibatalkan.',
            () => SphinxModal.berhasil('Pesanan ' + id + ' berhasil ditolak.', () => location.reload())
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

    // ===== KONFIRMASI: logika berdasarkan layanan & progres =====
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
                () => SphinxModal.berhasil('Pesanan ' + id + ' berhasil ditandai selesai!', () => location.reload())
            );
        } else {
            SphinxModal.berhasil('Pesanan ' + id + ' sudah selesai atau dibatalkan.');
        }
    } else {
        // Produk (Browsur AS dll)
        if (status === 'baru') {
            // Tahap 1: pesanan baru diterima → tanya metode pengiriman
            showKonfirmProdukModal(id, customer);
        } else if (status === 'diproses') {
            // Tahap 2: sudah siap ambil → tanya apakah sudah diterima customer
            SphinxModal.konfirmasi(
                'Pesanan Sudah Diterima?',
                'Apakah pesanan ' + id + ' sudah diterima / sampai ke ' + customer + '? Status pesanan akan diubah menjadi Selesai.',
                () => SphinxModal.berhasil('Pesanan ' + id + ' berhasil ditandai selesai!', () => location.reload())
            );
        } else {
            SphinxModal.berhasil('Pesanan ' + id + ' sudah selesai.');
        }
    }
}

function showKonfirmProdukModal(id, customer) {
    // Buat modal pilihan kurir / ambil di toko
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
        () => location.reload()
    );
}
</script>

</body>
</html>
