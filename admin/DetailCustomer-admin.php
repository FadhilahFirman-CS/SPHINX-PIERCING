<?php
session_start();
if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    header('Location: ../php-customer/Login.php');
    exit;
}
$customer_id = isset($_GET['id']) ? (int)$_GET['id'] : 101;

// Data dummy berdasarkan ID – ganti dengan query database nyata
$customers = [
    101 => [
        'nama'        => 'Nayaka Bagus',
        'membership'  => 'PLATINUM MEMBER',
        'whatsapp'    => '083125443565',
        'tgl_lahir'   => '22 Mei 1995',
        'usia'        => '28 Thn',
        'foto'        => '../gambar/default-avatar.png',
        'total_order' => 12,
        'lifetime'    => 'Rp 2.450k',
        'loyalty'     => '450 Pts',
        'integrasi'   => '01 Mei 2023',
        'warning'     => 'Pelanggan ini belum memperbarui informasi profilnya.',
        'transaksi'   => [
            ['tanggal'=>'12 Jan 2025','layanan'=>'Lobe Piercing','total'=>'Rp 450.000','status'=>'lunas'],
            ['tanggal'=>'12 Jan 2025','layanan'=>'Lobe Piercing','total'=>'Rp 450.000','status'=>'lunas'],
            ['tanggal'=>'12 Jan 2025','layanan'=>'Lobe Piercing','total'=>'Rp 450.000','status'=>'lunas'],
            ['tanggal'=>'12 Jan 2025','layanan'=>'Lobe Piercing','total'=>'Rp 450.000','status'=>'lunas'],
            ['tanggal'=>'12 Jan 2025','layanan'=>'Lobe Piercing','total'=>'Rp 450.000','status'=>'lunas'],
            ['tanggal'=>'12 Jan 2025','layanan'=>'Lobe Piercing','total'=>'Rp 450.000','status'=>'menunggu'],
        ],
    ],
    102 => [
        'nama'        => 'Rahardian',
        'membership'  => 'MEMBER',
        'whatsapp'    => '085119124857',
        'tgl_lahir'   => '10 Mar 1998',
        'usia'        => '26 Thn',
        'foto'        => '../gambar/default-avatar.png',
        'total_order' => 5,
        'lifetime'    => 'Rp 900k',
        'loyalty'     => '150 Pts',
        'integrasi'   => '15 Jun 2023',
        'warning'     => '',
        'transaksi'   => [
            ['tanggal'=>'05 Mar 2025','layanan'=>'Aftercare Kit','total'=>'Rp 180.000','status'=>'lunas'],
            ['tanggal'=>'20 Jan 2025','layanan'=>'Nose Piercing','total'=>'Rp 350.000','status'=>'lunas'],
        ],
    ],
    103 => [
        'nama'        => 'Tisa nin',
        'membership'  => 'AKTIF',
        'whatsapp'    => '085119124857',
        'tgl_lahir'   => '05 Jul 2000',
        'usia'        => '24 Thn',
        'foto'        => '../gambar/default-avatar.png',
        'total_order' => 3,
        'lifetime'    => 'Rp 650k',
        'loyalty'     => '90 Pts',
        'integrasi'   => '20 Aug 2024',
        'warning'     => '',
        'transaksi'   => [
            ['tanggal'=>'01 Apr 2025','layanan'=>'Piercing Servis','total'=>'Rp 250.000','status'=>'lunas'],
        ],
    ],
    104 => [
        'nama'        => 'Nina aja',
        'membership'  => 'MEMBER',
        'whatsapp'    => '085119124857',
        'tgl_lahir'   => '30 Nov 1997',
        'usia'        => '27 Thn',
        'foto'        => '../gambar/default-avatar.png',
        'total_order' => 8,
        'lifetime'    => 'Rp 1.200k',
        'loyalty'     => '280 Pts',
        'integrasi'   => '10 Feb 2023',
        'warning'     => '',
        'transaksi'   => [
            ['tanggal'=>'10 Feb 2025','layanan'=>'Aftercare Kit','total'=>'Rp 150.000','status'=>'lunas'],
            ['tanggal'=>'22 Jan 2025','layanan'=>'Lobe Piercing','total'=>'Rp 450.000','status'=>'lunas'],
        ],
    ],
    105 => [
        'nama'        => 'Maulida',
        'membership'  => 'MEMBER',
        'whatsapp'    => '085119124857',
        'tgl_lahir'   => '14 Sep 1999',
        'usia'        => '25 Thn',
        'foto'        => '../gambar/default-avatar.png',
        'total_order' => 6,
        'lifetime'    => 'Rp 1.050k',
        'loyalty'     => '200 Pts',
        'integrasi'   => '03 Mar 2024',
        'warning'     => '',
        'transaksi'   => [
            ['tanggal'=>'12 Mar 2025','layanan'=>'Piercing Servis','total'=>'Rp 350.000','status'=>'lunas'],
            ['tanggal'=>'01 Mar 2025','layanan'=>'Piercing Servis','total'=>'Rp 350.000','status'=>'menunggu'],
        ],
    ],
    106 => [
        'nama'        => 'Maulida',
        'membership'  => 'MEMBER',
        'whatsapp'    => '085119124857',
        'tgl_lahir'   => '14 Sep 1999',
        'usia'        => '25 Thn',
        'foto'        => '../gambar/default-avatar.png',
        'total_order' => 6,
        'lifetime'    => 'Rp 1.050k',
        'loyalty'     => '200 Pts',
        'integrasi'   => '03 Mar 2024',
        'warning'     => '',
        'transaksi'   => [
            ['tanggal'=>'12 Mar 2025','layanan'=>'Piercing Servis','total'=>'Rp 350.000','status'=>'lunas'],
        ],
    ],
];

$c = $customers[$customer_id] ?? $customers[101];
$whatsapp_link = 'https://wa.me/62' . ltrim($c['whatsapp'], '0');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Customer - ADMIN</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"/>
    <link rel="stylesheet" href="../css/admin_dashboard.css">
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
            <li class="active">
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
            <li>
                <a href="KelolaPesanan-admin.php">
                    <i class="fa-solid fa-cube"></i>
                    <span>Kelola Pesanan</span>
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
                <div class="topbar-back-row">
                    <a href="Customer-admin.php" class="btn-back">
                        <i class="fa-solid fa-arrow-left"></i> Back
                    </a>
                    <div>
                        <h1>Customer</h1>
                        <span class="breadcrumb">Dashboard &rsaquo; <a href="Customer-admin.php">Customer</a> &rsaquo; Detail Customer</span>
                    </div>
                </div>
            </div>
            <div class="topbar-icons">
                <button class="icon-btn" title="Notifikasi"><i class="fa-regular fa-bell"></i></button>
                <button class="icon-btn" title="Profil"><i class="fa-regular fa-user"></i></button>
            </div>
        </div>

        <!-- Profile Layout -->
        <div class="detail-layout">

            <!-- Left: Profile Card -->
            <div class="profile-card">

                <!-- Name + Badge + WA -->
                <div class="profile-header">
                    <div class="profile-name-wrap">
                        <h2 class="profile-name"><?= htmlspecialchars($c['nama']) ?></h2>
                        <?php
                        $mb = strtoupper($c['membership']);
                        $mbClass = ($mb === 'PLATINUM MEMBER') ? 'platinum' : (($mb === 'AKTIF') ? 'aktif' : 'member');
                        ?>
                        <span class="membership-badge <?= $mbClass ?>"><?= htmlspecialchars($c['membership']) ?></span>
                    </div>
                    <a href="<?= htmlspecialchars($whatsapp_link) ?>" target="_blank" rel="noopener noreferrer" class="btn-wa">
                        <i class="fa-brands fa-whatsapp"></i> HUBUNGI
                    </a>
                </div>

                <!-- Photo + Info -->
                <div class="profile-body">
                    <div class="profile-photo-wrap">
                        <img src="<?= htmlspecialchars($c['foto']) ?>" alt="Foto Customer"
                             onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name=<?= urlencode($c['nama']) ?>&background=1f1542&color=7FFF00&size=110&bold=true';">
                    </div>
                    <div class="profile-info-list">
                        <div class="info-item">
                            <span class="info-label">NO WHATSAPP</span>
                            <span class="info-value"><?= htmlspecialchars($c['whatsapp']) ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">TANGGAL LAHIR</span>
                            <span class="info-value"><?= htmlspecialchars($c['tgl_lahir']) ?> (<?= htmlspecialchars($c['usia']) ?>)</span>
                        </div>
                        <?php if (!empty($c['warning'])): ?>
                        <div class="info-warning">
                            <i class="fa-solid fa-triangle-exclamation"></i>
                            <?= htmlspecialchars($c['warning']) ?>
                        </div>
                        <?php endif; ?>
                        <div class="info-item integrasi">
                            <span class="info-label">INTEGRASI SEJAK</span>
                            <span class="info-value"><?= htmlspecialchars($c['integrasi']) ?></span>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Right: Stats + Log -->
            <div class="detail-right">

                <!-- Stats -->
                <div class="detail-stats">
                    <div class="dstat-card">
                        <span class="dstat-label">Total Pemesanan</span>
                        <span class="dstat-value lime"><?= htmlspecialchars($c['total_order']) ?> Kali</span>
                    </div>
                    <div class="dstat-card">
                        <span class="dstat-label">Lifetime Value</span>
                        <span class="dstat-value"><?= htmlspecialchars($c['lifetime']) ?></span>
                    </div>
                    <div class="dstat-card">
                        <span class="dstat-label">Loyalty Point</span>
                        <span class="dstat-value gold"><?= htmlspecialchars($c['loyalty']) ?></span>
                    </div>
                </div>

                <!-- Transaction Log -->
                <div class="log-box">
                    <div class="log-title">LOG TRANSAKSI &amp; KUNJUNGAN</div>
                    <table class="log-table">
                        <thead>
                            <tr>
                                <th>TANGGAL</th>
                                <th>LAYANAN/PRODUK</th>
                                <th>TOTAL</th>
                                <th>STATUS</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($c['transaksi'] as $t): ?>
                            <tr>
                                <td><?= htmlspecialchars($t['tanggal']) ?></td>
                                <td><?= htmlspecialchars($t['layanan']) ?></td>
                                <td><?= htmlspecialchars($t['total']) ?></td>
                                <td><span class="status-badge <?= htmlspecialchars($t['status']) ?>"><?= strtoupper(htmlspecialchars($t['status'])) ?></span></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>

    </main>
</div>

</body>
</html>
