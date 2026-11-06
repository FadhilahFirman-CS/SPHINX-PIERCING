﻿<?php
// Logout: bersihkan semua key sesi yang mungkin dipakai (canonical + fallback)
// dan invalidate session secara keseluruhan agar tidak ada state yang
// "tersangkut" di key alternatif.
if (session_status() == PHP_SESSION_NONE) { session_start(); }

$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params['path'], $params['domain'],
        $params['secure'], $params['httponly']
    );
}

// Unset eksplisit seluruh key yang dikenal (canonical + fallback) supaya
// sinkron dengan logika login di config.php / header.php.
$session_keys_to_clear = [
    'customer_user',
    'customer_data',
    'admin',
    'user',
    'username',
    'nohp',
    'email',
    'login_time',
];
foreach ($session_keys_to_clear as $k) {
    unset($_SESSION[$k]);
}

session_destroy();

// Hapus juga cache keranjang sisi klien agar tidak menyimpan referensi
// pesanan atas nama pengguna yang sudah logout.
header('Location: Login.php');
exit;
?>

