<?php
require_once __DIR__ . '/auth_admin.php';
require_once __DIR__ . '/../php-customer/db_config.php';
$admin_name = 'Admin';
require_once __DIR__ . '/pesanan_helper.php';

$search  = trim($_GET['search'] ?? '');
$page    = max(1, (int)($_GET['page'] ?? 1));
$perPage = 10;

// Count total customers with optional search
if ($search !== '') {
    $stmtCount = $conn->prepare(
        "SELECT COUNT(*) AS cnt FROM customers
         WHERE is_active = 1
           AND (nama LIKE ? OR email LIKE ? OR telepon LIKE ?)"
    );
    $like = '%' . $search . '%';
    $stmtCount->bind_param('sss', $like, $like, $like);
} else {
    $stmtCount = $conn->prepare("SELECT COUNT(*) AS cnt FROM customers WHERE is_active = 1");
}
$stmtCount->execute();
$total = (int)$stmtCount->get_result()->fetch_assoc()['cnt'];
$stmtCount->close();

$pages  = max(1, (int)ceil($total / $perPage));
$page   = min($page, $pages);
$offset = ($page - 1) * $perPage;

// Fetch paginated customers with order count
if ($search !== '') {
    $stmtList = $conn->prepare(
        "SELECT c.id, c.nama, c.email, c.telepon, c.membership, c.tgl_bergabung,
                COUNT(p.id) AS total_pesanan
         FROM customers c
         LEFT JOIN pesanan p ON p.customer_id = c.id
         WHERE c.is_active = 1
           AND (c.nama LIKE ? OR c.email LIKE ? OR c.telepon LIKE ?)
         GROUP BY c.id
         ORDER BY c.tgl_bergabung DESC, c.id DESC
         LIMIT ? OFFSET ?"
    );
    $like = '%' . $search . '%';
    $stmtList->bind_param('sssii', $like, $like, $like, $perPage, $offset);
} else {
    $stmtList = $conn->prepare(
        "SELECT c.id, c.nama, c.email, c.telepon, c.membership, c.tgl_bergabung,
                COUNT(p.id) AS total_pesanan
         FROM customers c
         LEFT JOIN pesanan p ON p.customer_id = c.id
         WHERE c.is_active = 1
         GROUP BY c.id
         ORDER BY c.tgl_bergabung DESC, c.id DESC
         LIMIT ? OFFSET ?"
    );
    $stmtList->bind_param('ii', $perPage, $offset);
}
$stmtList->execute();
$slice = $stmtList->get_result()->fetch_all(MYSQLI_ASSOC);
$stmtList->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer - Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"/>
    <link rel="stylesheet" href="../css-admin/admin_dashboard.css?v=2">
    <link rel="stylesheet" href="../css-admin/responsive_unified.css?v=2">
    <link rel="stylesheet" href="../css-admin/customer.css?v=2">
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
                <span class="breadcrumb">Dashboard &gt; Customer</span>
            </div>
            <div class="topbar-icons">
                <a href="Notifikasi-admin.php" class="icon-btn" title="Notifikasi"><i class="fa-regular fa-bell"></i></a>
                <a href="Profile-admin.php" class="icon-btn" title="Profil"><i class="fa-regular fa-user"></i></a>
            </div>
        </div>

        <!-- Banner -->
        <div class="admin-banner">
            <a href="Customer-admin.php">Customer</a>
        </div>

        <!-- Customer Table Card -->
        <div class="customer-card">
            <div class="customer-header">
                <h2><i class="fa-solid fa-users" style="color:var(--lime);margin-right:8px;"></i>Daftar Customer</h2>
                <form class="search-form" method="GET">
                    <input type="text" name="search" placeholder="Cari nama, email atau nomor telepon" value="<?= htmlspecialchars($search) ?>">
                    <button type="submit">Cari</button>
                </form>
            </div>

            <div class="customer-table-wrap">
                <table class="customer-tbl">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>Telepon</th>
                            <th>Membership</th>
                            <th>Bergabung</th>
                            <th>Pesanan</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($slice)): ?>
                        <tr>
                            <td colspan="8" style="text-align:center;color:var(--text-soft);padding:30px;">
                                Tidak ada customer ditemukan.
                            </td>
                        </tr>
                        <?php else:
                            $no = ($page - 1) * $perPage + 1;
                            foreach ($slice as $c):
                                $parts = explode(' ', $c['nama']);
                                $initials = strtoupper(substr($parts[0],0,1) . (isset($parts[1]) ? substr($parts[1],0,1) : ''));
                                $member_class = strtolower($c['membership'] ?? 'reguler');
                        ?>
                        <tr>
                            <td style="color:var(--text-soft);font-size:12px;"><?= $no++ ?></td>
                            <td>
                                <div style="display:flex;align-items:center;gap:10px;">
                                    <div class="avatar-sm"><?= htmlspecialchars($initials) ?></div>
                                    <span style="font-weight:600;"><?= htmlspecialchars($c['nama']) ?></span>
                                </div>
                            </td>
                            <td style="color:var(--text-soft);font-size:13px;"><?= htmlspecialchars($c['email']) ?></td>
                            <td><?= htmlspecialchars($c['telepon'] ?? '-') ?></td>
                            <td><span class="badge member-<?= $member_class ?>"><?= htmlspecialchars($c['membership']) ?></span></td>
                            <td style="color:var(--text-soft);font-size:12px;"><?= date('d M Y', strtotime($c['tgl_bergabung'])) ?></td>
                            <td style="text-align:center;font-weight:700;color:var(--lime);"><?= (int)$c['total_pesanan'] ?></td>
                            <td>
                                <a href="Customer-detail-admin.php?id=<?= $c['id'] ?>" class="btn-detail">Lihat Detail</a>
                            </td>
                        </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Table Footer -->
            <div class="table-footer">
                <span>
                    Menampilkan <?= count($slice) ?> dari <?= $total ?> customer
                    <?= $search ? '(hasil pencarian: "'.htmlspecialchars($search).'")' : '' ?>
                </span>
                <div class="pagination">
                    <?php
                    $qs = $search ? '&search='.urlencode($search) : '';
                    $prev = $page > 1 ? "?page=".($page-1).$qs : '#';
                    $next = $page < $pages ? "?page=".($page+1).$qs : '#';
                    ?>
                    <a href="<?= $prev ?>" class="<?= $page <= 1 ? 'disabled' : '' ?>">
                        <i class="fa-solid fa-chevron-left" style="font-size:10px;"></i>
                    </a>
                    <?php for ($i = 1; $i <= $pages; $i++): ?>
                        <?php if ($i == $page): ?>
                            <span class="active"><?= $i ?></span>
                        <?php else: ?>
                            <a href="?page=<?= $i ?><?= $qs ?>"><?= $i ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>
                    <a href="<?= $next ?>" class="<?= $page >= $pages ? 'disabled' : '' ?>">
                        <i class="fa-solid fa-chevron-right" style="font-size:10px;"></i>
                    </a>
                </div>
            </div>
        </div>

    </main>
</div>

</body>
</html>
