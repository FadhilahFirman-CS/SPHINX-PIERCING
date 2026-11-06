<?php
require_once __DIR__ . '/auth_admin.php';
$admin_name = 'Admin';
require_once __DIR__ . '/pesanan_helper.php';

// =====================================================================
// Stats dashboard dari database
// =====================================================================
// Total customer aktif
$rCust = $conn->query("SELECT COUNT(*) AS cnt FROM customers WHERE is_active = 1");
$totalCustomers = $rCust ? (int)$rCust->fetch_assoc()['cnt'] : 0;

// Pendapatan bulan ini (pesanan selesai)
$rRev = $conn->query("SELECT COALESCE(SUM(total),0) AS total FROM pesanan WHERE status='selesai' AND MONTH(tgl_masuk)=MONTH(NOW()) AND YEAR(tgl_masuk)=YEAR(NOW())");
$revenue = $rRev ? (int)$rRev->fetch_assoc()['total'] : 0;

// Jadwal aktif (ada pesanan, belum selesai/batal)
$rJdwl = $conn->query("SELECT COUNT(*) AS cnt FROM jadwal j JOIN pesanan p ON p.id=j.pesanan_id WHERE p.status NOT IN ('selesai','batal') AND j.tanggal >= CURDATE()");
$pendingJadwal = $rJdwl ? (int)$rJdwl->fetch_assoc()['cnt'] : 0;

// Produk stok rendah (≤5)
$rProd = $conn->query("SELECT COUNT(*) AS cnt FROM produk WHERE stok <= 5 AND is_active = 1");
$lowStock = $rProd ? (int)$rProd->fetch_assoc()['cnt'] : 0;

// Order terbaru (status baru/diproses)
$qOrd = $conn->query(
    "SELECT p.kode AS id, c.nama AS customer, p.layanan, p.status, p.tgl_masuk, p.total
     FROM pesanan p
     JOIN customers c ON c.id = p.customer_id
     WHERE p.status = 'baru'
     ORDER BY p.tgl_masuk DESC, p.id DESC
     LIMIT 5"
);
$orders_terbaru = $qOrd ? $qOrd->fetch_all(MYSQLI_ASSOC) : [];

function format_rupiah_short($value) {
    $value = (int)$value;
    if ($value >= 1000000) {
        return 'Rp ' . rtrim(rtrim(number_format($value / 1000000, 1, '.', ''), '0'), '.') . ' jt';
    }
    if ($value >= 1000) {
        return 'Rp ' . rtrim(rtrim(number_format($value / 1000, 1, '.', ''), '0'), '.') . ' k';
    }
    return 'Rp ' . number_format($value, 0, ',', '.');
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard ADMIN</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"/>
    <link rel="stylesheet" href="../css-admin/admin_dashboard.css?v=2">
    <link rel="stylesheet" href="../css-admin/responsive_unified.css?v=2">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
            <li class="active">
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
                <h1>Admin Dashboard</h1>
                <span class="breadcrumb">Dashboard</span>
            </div>
            <div class="topbar-icons">
                <a href="Notifikasi-admin.php" class="icon-btn" title="Notifikasi"><i class="fa-regular fa-bell"></i></a>
                <a href="Profile-admin.php" class="icon-btn" title="Profil"><i class="fa-regular fa-user"></i></a>
            </div>
        </div>

        <!-- Banner -->
        <div class="admin-banner">
            <a href="Dashboard-admin.php">Admin Dashboard</a>
        </div>

        <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon blue"><i class="fa-solid fa-users"></i></div>
                <div class="stat-info">
                    <span class="stat-label">Total Customer</span>
                    <span class="stat-value"><?= $totalCustomers ?></span>
                    <span class="stat-sub positive">Customer Aktif</span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon green"><i class="fa-solid fa-money-bill-wave"></i></div>
                <div class="stat-info">
                    <span class="stat-label">Pendapatan Bulan Ini</span>
                    <span class="stat-value"><?= format_rupiah_short($revenue) ?></span>
                    <span class="stat-sub positive">Pesanan Selesai</span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon yellow"><i class="fa-regular fa-calendar-check"></i></div>
                <div class="stat-info">
                    <span class="stat-label">Jadwal Menunggu Verifikasi</span>
                    <span class="stat-value"><?= $pendingJadwal ?></span>
                    <span class="stat-sub warning">Membutuhkan Tindakan</span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon red"><i class="fa-solid fa-triangle-exclamation"></i></div>
                <div class="stat-info">
                    <span class="stat-label">Stok Produk Rendah</span>
                    <span class="stat-value"><?= $lowStock ?> Produk</span>
                </div>
            </div>
        </div>

        <!-- Bottom Section -->
        <div class="admin-bottom">

            <!-- Chart -->
            <div class="chart-box">
                <div class="chart-title">
                    <i class="fa-solid fa-sack-dollar"></i>
                    <span>Pendapatan Mingguan (Rp Juta)</span>
                </div>
                <canvas id="weeklyChart"></canvas>
                <div class="chart-summary">
                    <span>Tertinggi : Jumat<br>Rp 9.1 Jt</span>
                    <span>Rata-Rata :<br>Rp 6.9 Jt</span>
                </div>
            </div>

            <!-- Recent Orders -->
            <div class="orders-box">
                <div class="orders-title">
                    <i class="fa-solid fa-clipboard-list"></i>
                    ORDER TERBARU
                </div>
                <div class="order-list">
                    <?php if (empty($orders_terbaru)): ?>
                        <div class="order-empty">Tidak ada order baru saat ini.</div>
                    <?php else: ?>
                        <?php foreach ($orders_terbaru as $order): ?>
                        <a class="order-item-link" href="KelolaPesanan-admin.php?id=<?= urlencode($order['id']) ?>">
                            <div class="order-item">
                                <span class="order-id">#<?= htmlspecialchars($order['id']) ?></span>
                                <div class="order-info">
                                    <strong><?= htmlspecialchars($order['customer']) ?></strong>
                                    <small><?= htmlspecialchars($order['layanan']) ?></small>
                                </div>
                                <span class="badge baru">Baru</span>
                                <span class="order-price"><?= htmlspecialchars(format_rupiah_short($order['total'])) ?></span>
                            </div>
                        </a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </main>
</div>

<script>
    const ctx = document.getElementById('weeklyChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'],
            datasets: [{
                data: [60, 65, 50, 30, 91, 25],
                backgroundColor: ['#4a9eff', '#4a9eff', '#5bc8ff', '#3a7aff', '#7bddff', '#4a9eff'],
                borderRadius: 5,
                borderSkipped: false,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    max: 80,
                    ticks: { color: '#9a9ab0', stepSize: 20, font: { family: 'Poppins', size: 11 } },
                    grid: { color: 'rgba(255,255,255,0.05)' }
                },
                x: {
                    ticks: { color: '#9a9ab0', font: { family: 'Poppins', size: 11 } },
                    grid: { display: false }
                }
            }
        }
    });
</script>

</body>
</html>
