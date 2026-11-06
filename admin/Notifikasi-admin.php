<?php
require_once __DIR__ . '/auth_admin.php';
require_once __DIR__ . '/pesanan_helper.php';

// =====================================================================
// Mark as read handler
// =====================================================================
if (isset($_GET['action'])) {
    header('Content-Type: application/json');
    if ($_GET['action'] === 'mark_read' && isset($_GET['id'])) {
        $id = (int)$_GET['id'];
        $conn->query("UPDATE notifikasi SET is_read=1 WHERE id=$id");
        echo json_encode(['success'=>true]);
        exit;
    }
    if ($_GET['action'] === 'mark_all_read') {
        $conn->query("UPDATE notifikasi SET is_read=1 WHERE is_read=0");
        echo json_encode(['success'=>true]);
        exit;
    }
}

// =====================================================================
// Query notifikasi dari DB
// =====================================================================
$qN = $conn->query(
    "SELECT n.id, n.tipe AS status, n.judul, n.pesan, n.is_read, n.created_at, p.kode
     FROM notifikasi n
     LEFT JOIN pesanan p ON p.id = n.pesanan_id
     ORDER BY n.created_at DESC
     LIMIT 50"
);
$notifikasi_raw = $qN ? $qN->fetch_all(MYSQLI_ASSOC) : [];

// Format waktu relatif
function relativeTime($ts) {
    $diff = time() - strtotime($ts);
    if ($diff < 60)    return $diff . ' detik lalu';
    if ($diff < 3600)  return (int)($diff/60) . ' menit lalu';
    if ($diff < 86400) return (int)($diff/3600) . ' jam lalu';
    return (int)($diff/86400) . ' hari lalu';
}

$labelMap = ['baru'=>'Baru','diproses'=>'Diproses','selesai'=>'Selesai','batal'=>'Batal','info'=>'Info'];

$notifikasi = [];
foreach ($notifikasi_raw as $n) {
    $notifikasi[] = [
        'id'     => $n['id'],
        'status' => $n['status'] ?: 'info',
        'label'  => $labelMap[$n['status']] ?? 'Info',
        'pesan'  => $n['pesan'],
        'waktu'  => relativeTime($n['created_at']),
        'baru'   => !(bool)$n['is_read'],
    ];
}

$jumlah_baru = count(array_filter($notifikasi, fn($n) => $n['baru']));

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifikasi - Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"/>
    <link rel="stylesheet" href="../css-admin/admin_dashboard.css?v=2">
    <link rel="stylesheet" href="../css-admin/responsive_unified.css?v=2">
    <link rel="stylesheet" href="../css-admin/notifikasi.css">
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
                <h1>Notifikasi</h1>
                <span class="breadcrumb">Dashboard &gt; Notifikasi</span>
            </div>
            <div class="topbar-icons">
                <a href="Notifikasi-admin.php" class="icon-btn" title="Notifikasi" style="position:relative;">
                    <i class="fa-regular fa-bell"></i>
                    <?php if ($jumlah_baru > 0): ?>
                    <span style="position:absolute;top:4px;right:4px;width:8px;height:8px;background:var(--lime);border-radius:50%;"></span>
                    <?php endif; ?>
                </a>
                <a href="Profile-admin.php" class="icon-btn" title="Profil"><i class="fa-regular fa-user"></i></a>
            </div>
        </div>

        <!-- Banner -->
        <div class="admin-banner">
            <a href="Notifikasi-admin.php">Notifikasi</a>
        </div>

        <!-- Notifikasi Card -->
        <div class="notif-card">
            <div class="notif-card-header">
                <h2>Semua Notifikasi</h2>
                <?php if ($jumlah_baru > 0): ?>
                <span class="notif-badge-count"><?= $jumlah_baru ?> Notifikasi Baru</span>
                <?php endif; ?>
            </div>

            <div class="notif-list" id="notif-list">
                <?php foreach ($notifikasi as $n): ?>
                <div class="notif-item">
                    <span class="notif-icon-badge <?= htmlspecialchars($n['status']) ?>">
                        <?php
                            $icons = ['baru'=>'fa-plus','diproses'=>'fa-rotate','selesai'=>'fa-check','batal'=>'fa-xmark','info'=>'fa-circle-info'];
                            echo '<i class="fa-solid '.($icons[$n['status']]??'fa-bell').'"></i>';
                        ?>
                    </span>
                    <div class="notif-body">
                        <div class="notif-text">
                            <span class="notif-status-label <?= htmlspecialchars($n['status']) ?>"><?= htmlspecialchars($n['label']) ?></span>
                            <?= htmlspecialchars($n['pesan']) ?>
                        </div>
                        <div class="notif-time"><?= htmlspecialchars($n['waktu']) ?></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="notif-footer">
                <button class="btn-mark-all" onclick="tandaiSemuaDibaca()">
                    Tandai Semua Sudah Dibaca
                </button>
            </div>
        </div>

    </main>
</div>

<script>
const EXTRA_NOTIF_KEY = 'sphinx_notifikasi_tambahan_v1';

function renderExtraNotifikasi() {
    let list = [];
    try { list = JSON.parse(localStorage.getItem(EXTRA_NOTIF_KEY) || '[]'); } catch(e) { list = []; }
    if (!Array.isArray(list) || list.length === 0) return;

    const container = document.getElementById('notif-list');
    if (!container) return;

    list.forEach(function(n) {
        const item = document.createElement('div');
        item.className = 'notif-item';
        item.innerHTML = `
            <span class="notif-icon-badge info"><i class="fa-solid fa-circle-info"></i></span>
            <div class="notif-body">
                <div class="notif-text">
                    <span class="notif-status-label info">Info</span>
                    ${escapeHtml(n.pesan || 'Notifikasi tambahan')}
                </div>
                <div class="notif-time">${escapeHtml(n.waktu || 'Baru saja')}</div>
            </div>
        `;
        container.prepend(item);
    });
}

function escapeHtml(str) {
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

function tandaiSemuaDibaca() {
    SphinxModal.konfirmasi(
        'Tandai Semua Dibaca',
        'Apakah kamu yakin ingin menandai semua notifikasi sebagai sudah dibaca?',
        async () => {
            try { await fetch('Notifikasi-admin.php?action=mark_all_read'); } catch(e){}
            document.querySelectorAll('.notif-icon-badge').forEach(d => {
                d.className = 'notif-icon-badge info';
                d.innerHTML = '<i class="fa-solid fa-check"></i>';
            });
            document.querySelectorAll('.notif-status-label').forEach(l => {
                l.className = 'notif-status-label info';
                l.textContent = 'Dibaca';
            });
            const badge = document.querySelector('.notif-badge-count');
            if (badge) badge.remove();
            const bellDot = document.querySelector('.topbar-icons .icon-btn span');
            if (bellDot) bellDot.remove();
            SphinxModal.berhasil('Semua notifikasi telah ditandai sebagai dibaca.');
        }
    );
}

renderExtraNotifikasi();
</script>

</body>
</html>
