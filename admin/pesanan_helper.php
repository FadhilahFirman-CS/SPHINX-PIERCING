<?php
/**
 * pesanan_helper.php
 * Shared include: hitung jumlah pesanan baru (belum diproses)
 * untuk ditampilkan sebagai badge di sidebar.
 */

if (!isset($conn)) {
    require_once __DIR__ . '/../php-customer/db_config.php';
}

$_stmt = $conn->query("SELECT COUNT(*) AS cnt FROM pesanan WHERE status = 'baru'");
$pending_pesanan_count = (int)(($_stmt && $_stmt->num_rows > 0) ? $_stmt->fetch_assoc()['cnt'] : 0);
$_stmt = null;
