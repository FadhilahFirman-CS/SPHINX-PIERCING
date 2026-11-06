<?php
require_once __DIR__ . '/auth_admin.php';
require_once __DIR__ . '/../php-customer/db_config.php';
require_once __DIR__ . '/pesanan_helper.php';

// Ambil ID customer dari URL (validasi integer positif)
$id = max(0, (int)($_GET['id'] ?? 0));
if ($id <= 0) {
    header('Location: Customer-admin.php');
    exit;
}

// Fetch data customer
$stmtC = $conn->prepare(
    "SELECT id, nama, email, telepon, whatsapp, alamat, tgl_lahir,
            foto, membership, loyalty_pts, tgl_bergabung, is_active
     FROM customers WHERE id = ? AND is_active = 1 LIMIT 1"
);
$stmtC->bind_param('i', $id);
$stmtC->execute();
$customer = $stmtC->get_result()->fetch_assoc();
$stmtC->close();

if (!$customer) {
    header('Location: Customer-admin.php');
    exit;
}

// Statistik pesanan customer
$stmtStat = $conn->prepare(
    "SELECT COUNT(*) AS total_pesanan,
            COALESCE(SUM(total), 0) AS total_nominal,
            SUM(status = 'selesai')   AS selesai,
            SUM(status = 'diproses')  AS diproses,
            SUM(status = 'batal')     AS batal
     FROM pesanan WHERE customer_id = ?"
);
$stmtStat->bind_param('i', $id);
$stmtStat->execute();
$stat = $stmtStat->get_result()->fetch_assoc();
$stmtStat->close();

// Riwayat pesanan (10 terbaru)
$stmtO = $conn->prepare(
    "SELECT p.id, p.kode, p.layanan, p.status, p.total, p.tgl_masuk,
            GROUP_CONCAT(pi.nama_produk ORDER BY pi.id SEPARATOR ', ') AS produk_list,
            COUNT(pi.id) AS jumlah_item
     FROM pesanan p
     LEFT JOIN pesanan_items pi ON pi.pesanan_id = p.id
     WHERE p.customer_id = ?
     GROUP BY p.id
     ORDER BY p.tgl_masuk DESC, p.id DESC
     LIMIT 20"
);
$stmtO->bind_param('i', $id);
$stmtO->execute();
$orders = $stmtO->get_result()->fetch_all(MYSQLI_ASSOC);
$stmtO->close();

// Inisial avatar dari nama
$parts    = explode(' ', $customer['nama']);
$initials = strtoupper(substr($parts[0], 0, 1) . (isset($parts[1]) ? substr($parts[1], 0, 1) : ''));
$member_class = strtolower($customer['membership'] ?? 'reguler');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Customer - Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"/>
    <link rel="stylesheet" href="../css-admin/admin_dashboard.css?v=2">
    <link rel="stylesheet" href="../css-admin/responsive_unified.css?v=2">
    <link rel="stylesheet" href="../css-admin/customer_detail.css?v=2">
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
                <h1>Customer</h1>
                <span class="breadcrumb">Dashboard &gt; Customer &gt; Detail</span>
            </div>
            <div class="topbar-icons">
                <a href="Notifikasi-admin.php" class="icon-btn" title="Notifikasi"><i class="fa-regular fa-bell"></i></a>
                <a href="Profile-admin.php" class="icon-btn" title="Profil"><i class="fa-regular fa-user"></i></a>
            </div>
        </div>

        <!-- Banner -->
        <div class="admin-banner" style="display:flex;align-items:center;gap:10px;">
            <span style="color:#1a1135;font-weight:600;">Customer</span>
            <span style="color:#1a1135;font-weight:700;">&nbsp;&gt;&nbsp;</span>
            <span style="color:#1a1135;font-weight:600;">Detail</span>
        </div>

        <!-- Detail Grid -->
        <div class="detail-grid">

            <!-- Profile Card -->
            <div class="profile-card">
                <div class="avatar-circle"><?= htmlspecialchars($initials) ?></div>
                <div class="profile-name"><?= htmlspecialchars($customer['nama']) ?></div>
                <span class="badge member-<?= $member_class ?>" style="margin-top:-6px;"><?= htmlspecialchars($customer['membership']) ?></span>

                <div class="profile-info-list">
                    <div class="profile-info-row">
                        <i class="fa-solid fa-envelope"></i>
                        <span><?= htmlspecialchars($customer['email']) ?></span>
                    </div>
                    <div class="profile-info-row">
                        <i class="fa-solid fa-phone"></i>
                        <span><?= htmlspecialchars($customer['telepon'] ?? '-') ?></span>
                    </div>
                    <?php if (!empty($customer['whatsapp'])): ?>
                    <div class="profile-info-row">
                        <i class="fa-brands fa-whatsapp"></i>
                        <span><?= htmlspecialchars($customer['whatsapp']) ?></span>
                    </div>
                    <?php endif; ?>
                    <div class="profile-info-row">
                        <i class="fa-solid fa-location-dot"></i>
                        <span><?= htmlspecialchars($customer['alamat'] ?? 'Belum diisi') ?></span>
                    </div>
                    <?php if (!empty($customer['tgl_lahir'])): ?>
                    <div class="profile-info-row">
                        <i class="fa-solid fa-cake-candles"></i>
                        <span><?= date('d M Y', strtotime($customer['tgl_lahir'])) ?></span>
                    </div>
                    <?php endif; ?>
                    <div class="profile-info-row">
                        <i class="fa-solid fa-calendar-check"></i>
                        <span>Bergabung <?= date('d M Y', strtotime($customer['tgl_bergabung'])) ?></span>
                    </div>
                    <div class="profile-info-row">
                        <i class="fa-solid fa-star"></i>
                        <span><?= number_format((int)$customer['loyalty_pts']) ?> Loyalty Points</span>
                    </div>
                </div>

                <hr class="profile-divider">

                <div class="profile-stat-row">
                    <div class="profile-stat">
                        <div class="ps-label">Total Pesanan</div>
                        <div class="ps-value"><?= (int)$stat['total_pesanan'] ?></div>
                    </div>
                    <div class="profile-stat">
                        <div class="ps-label">Total Belanja</div>
                        <div class="ps-value">Rp<?= number_format((int)$stat['total_nominal'], 0, ',', '.') ?></div>
                    </div>
                    <div class="profile-stat">
                        <div class="ps-label">Selesai</div>
                        <div class="ps-value" style="color:#2ecc71;"><?= (int)$stat['selesai'] ?></div>
                    </div>
                    <div class="profile-stat">
                        <div class="ps-label">Dibatal</div>
                        <div class="ps-value" style="color:#e74c3c;"><?= (int)$stat['batal'] ?></div>
                    </div>
                </div>

                <a href="Customer-admin.php" class="back-link" style="margin-top:6px;">
                    <i class="fa-solid fa-arrow-left"></i> Kembali ke Daftar
                </a>
            </div>

            <!-- Orders Card -->
            <div class="orders-card">
                <div class="orders-card-title">
                    <i class="fa-solid fa-receipt"></i>
                    RIWAYAT PESANAN CUSTOMER
                    <span style="margin-left:auto;font-size:12px;color:var(--text-soft);font-weight:400;"><?= (int)$stat['total_pesanan'] ?> pesanan</span>
                </div>

                <?php if (empty($orders)): ?>
                <div style="text-align:center;color:var(--text-soft);padding:40px 0;font-size:13px;">
                    <i class="fa-solid fa-box-open" style="font-size:30px;margin-bottom:10px;display:block;opacity:.4;"></i>
                    Belum ada pesanan.
                </div>
                <?php else: foreach ($orders as $order):
                    $deskripsi = !empty($order['produk_list'])
                        ? $order['layanan'] . ' — ' . $order['produk_list']
                        : $order['layanan'];
                    if (strlen($deskripsi) > 60) $deskripsi = substr($deskripsi, 0, 57) . '...';
                ?>
                <div class="order-entry">
                    <div class="oe-id">#<?= htmlspecialchars($order['kode']) ?></div>
                    <div class="oe-info">
                        <div class="oe-desc"><?= htmlspecialchars($deskripsi) ?></div>
                        <div style="font-size:11px;color:var(--text-soft);margin-top:3px;">
                            <?= (int)$order['jumlah_item'] ?> item
                        </div>
                    </div>
                    <div class="oe-right">
                        <span class="oe-harga">Rp<?= number_format((int)$order['total'], 0, ',', '.') ?></span>
                        <span class="oe-tanggal"><?= date('d M Y', strtotime($order['tgl_masuk'])) ?></span>
                        <span class="badge <?= htmlspecialchars($order['status']) ?>">
                            <?= ucfirst($order['status']) ?>
                        </span>
                    </div>
                </div>
                <?php endforeach; endif; ?>
            </div>

        </div>

    </main>
</div>

</body>
</html>
