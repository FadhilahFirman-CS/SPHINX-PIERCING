<?php
require_once __DIR__ . '/auth_admin.php';
require_once __DIR__ . '/pesanan_helper.php';

// Data profil admin (dalam implementasi nyata dari database)
$admin = [
    'nama'     => 'Admin Kece',
    'username' => 'sphinx_admin',
    'email'    => 'admin@sphinx-piercing.com',
    'telepon'  => '081234567890',
    'role'     => 'Admin',
    'bergabung'=> 'Januari 2024',
    'foto'     => '', // kosong = avatar inisial
];

$inisial = strtoupper(substr($admin['nama'], 0, 1));

// Simpan perubahan
$success_msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_profile'])) {
    $admin['nama']    = htmlspecialchars(trim($_POST['nama'] ?? $admin['nama']));
    $admin['telepon'] = htmlspecialchars(trim($_POST['telepon'] ?? $admin['telepon']));
    // Dalam implementasi nyata: simpan ke database
    $success_msg = 'Profil berhasil disimpan.';
}

// Statistik admin
$stats = [
    ['key' => 'jadwal',    'icon' => 'fa-calendar-check', 'label' => 'Jadwal',   'value' => '181',  'trend' => '+12%'],
    ['key' => 'customer',  'icon' => 'fa-users',           'label' => 'Customer', 'value' => '1.8K', 'trend' => '+12%'],
    ['key' => 'produk',    'icon' => 'fa-box-open',        'label' => 'Produk',   'value' => '63',   'trend' => '+12%'],
    ['key' => 'aktifitas', 'icon' => 'fa-chart-line',      'label' => 'Aktifitas','value' => '80%',  'trend' => '+12%'],
];

$laporan_mingguan = [
    ['label' => 'Minggu 1 (1-7 Jun 2026)',  'pendapatan' => 3250000],
    ['label' => 'Minggu 2 (8-14 Jun 2026)', 'pendapatan' => 4180000],
    ['label' => 'Minggu 3 (15-21 Jun 2026)','pendapatan' => 4625000],
    ['label' => 'Minggu 4 (22-28 Jun 2026)','pendapatan' => 4975000],
];

$laporan_bulanan = [
    ['label' => 'Januari 2026', 'pendapatan' => 13250000],
    ['label' => 'Februari 2026','pendapatan' => 14780000],
    ['label' => 'Maret 2026',   'pendapatan' => 15600000],
    ['label' => 'April 2026',   'pendapatan' => 16900000],
    ['label' => 'Mei 2026',     'pendapatan' => 18250000],
    ['label' => 'Juni 2026',    'pendapatan' => 17030000],
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"/>
    <link rel="stylesheet" href="../css-admin/admin_dashboard.css?v=2">
    <link rel="stylesheet" href="../css-admin/responsive_unified.css?v=2">
    <link rel="stylesheet" href="../css-admin/profile.css">
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
            <li><a href="Dashboard-admin.php"><i class="fa-solid fa-house"></i><span>Dashboard</span></a></li>
            <li><a href="Customer-admin.php"><i class="fa-solid fa-users"></i><span>Customer</span></a></li>
            <li><a href="Produk-admin.php"><i class="fa-solid fa-box-open"></i><span>Produk</span></a></li>
            <li><a href="KelolaJadwal-admin.php"><i class="fa-solid fa-calendar-days"></i><span>Kelola Jadwal</span></a></li>
            <li><a href="KelolaPesanan-admin.php"><i class="fa-solid fa-cube"></i><span>Kelola Pesanan</span><?php if ($pending_pesanan_count > 0): ?><span class="sidebar-badge"><?= $pending_pesanan_count ?></span><?php endif; ?></a></li>
        </ul>
        <div class="sidebar-bottom">
            <a href="#" class="sidebar-bantuan"><i class="fa-solid fa-circle-question"></i><span>Bantuan</span></a>
            <a href="logout-admin.php" class="sidebar-logout"><i class="fa-solid fa-right-from-bracket"></i><span>Logout</span></a>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="admin-main">

        <!-- Top Bar -->
        <div class="admin-topbar">
            <div class="topbar-title">
                <h1>Profile Admin</h1>
                <span class="breadcrumb">Dashboard &gt; Profile</span>
            </div>
            <div class="topbar-icons">
                <a href="Notifikasi-admin.php" class="icon-btn" title="Notifikasi"><i class="fa-regular fa-bell"></i></a>
                <a href="Profile-admin.php" class="icon-btn active-icon" title="Profil"><i class="fa-regular fa-user"></i></a>
            </div>
        </div>

        <!-- Banner -->
        <div class="admin-banner">
            <a href="Profile-admin.php">Profile Admin</a>
        </div>

        <?php if ($success_msg): ?>
        <div style="background:rgba(46,204,113,0.12);border:1px solid rgba(46,204,113,0.3);border-radius:10px;padding:12px 18px;font-size:13px;color:#2ecc71;display:flex;align-items:center;gap:10px;">
            <i class="fa-solid fa-circle-check"></i> <?= $success_msg ?>
        </div>
        <?php endif; ?>

        <div class="profile-page-wrap">

            <!-- ===== PROFILE HERO ===== -->
            <div class="profile-hero">
                <div class="profile-hero-banner"></div>
                <div class="profile-hero-body">

                    <!-- Avatar -->
                    <div class="profile-avatar-wrap">
                        <div class="profile-avatar" id="avatar-display">
                            <?php if ($admin['foto']): ?>
                                <img src="<?= htmlspecialchars($admin['foto']) ?>" alt="Foto Admin" id="avatar-img">
                            <?php else: ?>
                                <span id="avatar-initials"><?= $inisial ?></span>
                            <?php endif; ?>
                        </div>
                        <label for="foto-input" class="profile-avatar-edit" title="Ganti foto">
                            <i class="fa-solid fa-camera"></i>
                        </label>
                        <input type="file" id="foto-input" accept="image/*" onchange="pratinjauFoto(event)">
                    </div>

                    <!-- Name & Role -->
                    <div class="profile-identity">
                        <div class="profile-name"><?= htmlspecialchars($admin['nama']) ?></div>
                        <span class="profile-role-badge"><?= htmlspecialchars($admin['role']) ?></span>
                    </div>

                    <!-- Action Buttons -->
                    <div class="profile-actions">
                        <label for="foto-input" class="btn-edit-foto">
                            <i class="fa-regular fa-image"></i> Edit Foto Profile
                        </label>
                        <button class="btn-unduh" onclick="unduhLaporan()">
                            <i class="fa-solid fa-download"></i> Unduh Laporan Akun
                        </button>
                    </div>

                </div>
            </div>

            <!-- ===== STATS MINI ===== -->
            <div class="profile-stats-row">
                <?php foreach ($stats as $s): ?>
                <div class="pstat-card <?= $s['key'] ?>">
                    <span class="pstat-trend"><?= $s['trend'] ?></span>
                    <i class="fa-solid <?= $s['icon'] ?> pstat-icon"></i>
                    <span class="pstat-value"><?= $s['value'] ?></span>
                    <span class="pstat-label"><?= $s['label'] ?></span>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- ===== INFO SECTIONS ===== -->
            <div class="profile-info-grid">

                <!-- Info Akun -->
                <form method="POST" class="info-section-card">
                    <div class="info-section-title">
                        <i class="fa-solid fa-circle-user"></i> Informasi Akun
                    </div>
                    <div class="info-field">
                        <label>Nama Lengkap</label>
                        <input type="text" name="nama" value="<?= htmlspecialchars($admin['nama']) ?>">
                    </div>
                    <div class="info-field">
                        <label>Username</label>
                        <input type="text" value="<?= htmlspecialchars($admin['username']) ?>" readonly>
                    </div>
                    <div class="info-field">
                        <label>Email</label>
                        <input type="email" value="<?= htmlspecialchars($admin['email']) ?>" readonly>
                    </div>
                    <div class="info-field">
                        <label>No. Telepon</label>
                        <input type="text" name="telepon" value="<?= htmlspecialchars($admin['telepon']) ?>">
                    </div>
                    <div class="info-field">
                        <label>Bergabung Sejak</label>
                        <input type="text" value="<?= htmlspecialchars($admin['bergabung']) ?>" readonly>
                    </div>
                    <button type="submit" name="save_profile" class="btn-save-profile">
                        <i class="fa-solid fa-floppy-disk"></i> Simpan Perubahan
                    </button>
                </form>

                <!-- Ganti Password -->
                <div class="info-section-card">
                    <div class="info-section-title">
                        <i class="fa-solid fa-lock"></i> Keamanan Akun
                    </div>
                    <div class="info-field">
                        <label>Password Lama</label>
                        <input type="password" id="pw-lama" placeholder="Masukkan password lama">
                    </div>
                    <div class="info-field">
                        <label>Password Baru</label>
                        <input type="password" id="pw-baru" placeholder="Masukkan password baru">
                    </div>
                    <div class="info-field">
                        <label>Konfirmasi Password Baru</label>
                        <input type="password" id="pw-konfirm" placeholder="Ulangi password baru">
                    </div>
                    <button type="button" class="btn-save-profile" style="background:#6d28d9;" onclick="gantiPassword()">
                        <i class="fa-solid fa-shield-halved"></i> Ganti Password
                    </button>
                </div>

            </div>

        </div><!-- /profile-page-wrap -->

    </main>
</div>

<script>
const LAPORAN_MINGGUAN = <?= json_encode($laporan_mingguan, JSON_UNESCAPED_UNICODE) ?>;
const LAPORAN_BULANAN = <?= json_encode($laporan_bulanan, JSON_UNESCAPED_UNICODE) ?>;

function pratinjauFoto(event) {
    const file = event.target.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = function(e) {
        const wrap = document.getElementById('avatar-display');
        wrap.innerHTML = `<img src="${e.target.result}" alt="Foto Admin" id="avatar-img" style="width:100%;height:100%;border-radius:50%;object-fit:cover;">`;
    };
    reader.readAsDataURL(file);
}

function unduhLaporan() {
    const reportWin = window.open('', '_blank');
    if (!reportWin) {
        SphinxModal.peringatan('Popup browser diblokir. Izinkan popup untuk membuka laporan di tab baru.');
        return;
    }

    reportWin.document.open();
    reportWin.document.write(generateReportTabHtml());
    reportWin.document.close();
    reportWin.focus();
}

function formatRupiah(nominal) {
    return 'Rp' + Number(nominal || 0).toLocaleString('id-ID');
}

function buildRowsAndTotal(source) {
    let total = 0;
    const rows = source.map(function(item) {
        total += Number(item.pendapatan || 0);
        return '<tr><td>' + item.label + '</td><td>' + formatRupiah(item.pendapatan) + '</td></tr>';
    }).join('');
    return { rows: rows, total: total };
}

function generateReportTabHtml() {
    const mingguan = buildRowsAndTotal(LAPORAN_MINGGUAN);
    const bulanan = buildRowsAndTotal(LAPORAN_BULANAN);

    return '<!doctype html>'
        + '<html lang="id"><head><meta charset="utf-8">'
        + '<meta name="viewport" content="width=device-width, initial-scale=1">'
        + '<title>Laporan Pendapatan Admin</title>'
        + '<style>'
        + 'body{font-family:Segoe UI,Tahoma,Arial,sans-serif;background:#f8fafc;color:#0f172a;margin:0;padding:24px}'
        + '.wrap{max-width:980px;margin:0 auto;background:#fff;border:1px solid #e2e8f0;border-radius:12px;overflow:hidden;box-shadow:0 10px 24px rgba(15,23,42,.08)}'
        + '.head{padding:18px 20px;border-bottom:1px solid #e2e8f0;display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap}'
        + '.title{margin:0;font-size:22px;font-weight:700}'
        + '.subtitle{margin:4px 0 0;color:#475569;font-size:13px}'
        + '.actions{display:flex;gap:8px;flex-wrap:wrap}'
        + '.btn{border:1px solid #cbd5e1;background:#fff;color:#0f172a;border-radius:8px;padding:9px 12px;font-size:13px;font-weight:600;cursor:pointer}'
        + '.btn-print{background:#0f172a;border-color:#0f172a;color:#fff}'
        + '.content{padding:18px 20px 24px}'
        + '.section{margin-bottom:24px}'
        + '.section h2{margin:0 0 10px;font-size:17px}'
        + 'table{width:100%;border-collapse:collapse}'
        + 'th,td{border:1px solid #e2e8f0;padding:10px;text-align:left;font-size:13px}'
        + 'th{background:#f1f5f9}'
        + '.sum{display:flex;justify-content:space-between;align-items:center;margin-top:10px;padding-top:10px;border-top:2px solid #0f172a;font-weight:700}'
        + '@media print{body{padding:0;background:#fff}.wrap{box-shadow:none;border:none}.actions{display:none}}'
        + '</style></head><body>'
        + '<div class="wrap">'
        + '<div class="head">'
        + '<div><h1 class="title">Laporan Pendapatan Admin</h1><p class="subtitle">Sphinx Piercing - Ringkasan Mingguan dan Bulanan</p></div>'
        + '<div class="actions"><button class="btn btn-print" onclick="window.print()">Cetak / Simpan PDF</button></div>'
        + '</div>'
        + '<div class="content">'
        + '<section class="section"><h2>Pendapatan Mingguan</h2>'
        + '<table><thead><tr><th>Periode</th><th>Pendapatan</th></tr></thead><tbody>' + mingguan.rows + '</tbody></table>'
        + '<div class="sum"><span>Total Mingguan</span><strong>' + formatRupiah(mingguan.total) + '</strong></div>'
        + '</section>'
        + '<section class="section"><h2>Pendapatan Bulanan</h2>'
        + '<table><thead><tr><th>Periode</th><th>Pendapatan</th></tr></thead><tbody>' + bulanan.rows + '</tbody></table>'
        + '<div class="sum"><span>Total Bulanan</span><strong>' + formatRupiah(bulanan.total) + '</strong></div>'
        + '</section>'
        + '</div></div></body></html>';
}

function gantiPassword() {
    const lama    = document.getElementById('pw-lama').value;
    const baru    = document.getElementById('pw-baru').value;
    const konfirm = document.getElementById('pw-konfirm').value;

    if (!lama || !baru || !konfirm) {
        SphinxModal.peringatan('Semua field password wajib diisi.');
        return;
    }
    if (baru.length < 6) {
        SphinxModal.peringatan('Password baru minimal 6 karakter.');
        return;
    }
    if (baru !== konfirm) {
        SphinxModal.peringatan('Konfirmasi password tidak cocok.');
        return;
    }
    SphinxModal.konfirmasi(
        'Ganti Password',
        'Apakah kamu yakin ingin mengganti password akun ini?',
        () => {
            document.getElementById('pw-lama').value = '';
            document.getElementById('pw-baru').value = '';
            document.getElementById('pw-konfirm').value = '';
            SphinxModal.berhasil('Password berhasil diubah.');
        }
    );
}

</script>

</body>
</html>
