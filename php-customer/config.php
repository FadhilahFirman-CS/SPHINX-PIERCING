<?php
// ============================================================
// Konfigurasi MySQL Database
// Lokal (XAMPP) : DB_HOST = 'localhost'
// Hosting       : DB_HOST = 'sql111.infinityfree.com'
// ============================================================
define('DB_HOST', 'localhost');          // XAMPP lokal
define('DB_USER', 'root');               // default XAMPP user
define('DB_PASS', '');                   // default XAMPP (kosong)
define('DB_NAME', 'sphinx_piercing');    // nama database lokal

// Create Database Connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check Connection
if ($conn->connect_error) {
    die("Database Connection failed: " . $conn->connect_error);
}

// Set charset to UTF8MB4
$conn->set_charset("utf8mb4");

// Set timezone untuk Indonesia
date_default_timezone_set('Asia/Jakarta');

// ---------------------------------------------------------------------------
// Session & route policy (dipakai bersama oleh seluruh halaman customer)
// ---------------------------------------------------------------------------
if (session_status() == PHP_SESSION_NONE) { session_start(); }

// Helper shared
if (!function_exists('sanitize_text')) {
    function sanitize_text($text) {
        return htmlspecialchars(trim((string)$text), ENT_QUOTES, 'UTF-8');
    }
}
if (!function_exists('format_rupiah')) {
    function format_rupiah($value) {
        $value = (int)$value;
        return 'Rp ' . number_format($value, 0, ',', '.');
    }
}

// Lengkapi key sesi customer_user dari fallback `user` (jika ada) agar
// seluruh aplikasi menggunakan satu key kanonik.
if (empty($_SESSION['admin']) && empty($_SESSION['customer_user']) && !empty($_SESSION['user'])) {
    $_SESSION['customer_user'] = (string)$_SESSION['user'];
}

// Daftar halaman yang boleh diakses tanpa login (publik).
// Halaman lain dianggap private dan akan diarahkan ke Login.php.
$GLOBALS['SPHINX_PUBLIC_PAGES'] = [
    'index.php',
    'Login.php',
    'BuatAkun.php',
    'Welcome.php',
    'Dashboard.php',       // guest dapat browsing dashboard
    'piercing_produk.php',
    'jasa_piercing.php',
    'alternatif_piercing.php',
    'piercing_disukai.php',
    'bantuan.php',
    'maps.php',
    '404.html'
];

// Halaman yang membutuhkan login (guest akan diredirect)
$GLOBALS['SPHINX_LOGIN_REQUIRED_PAGES'] = [
    'riwayat.php',
    'jadwal.php',
    'metode.php',
    'pengaturan.php',
    'beli.php',
];
?>