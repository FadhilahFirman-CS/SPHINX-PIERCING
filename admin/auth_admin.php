<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$isAdmin = isset($_SESSION['admin']) && $_SESSION['admin'] === true;
$loginTime = (int)($_SESSION['admin_login_time'] ?? 0);
$sessionAge = time() - $loginTime;
$maxSessionAge = 3600;

if (!$isAdmin || $loginTime <= 0 || $sessionAge > $maxSessionAge) {
    session_unset();
    session_destroy();
    header('Location: ../php-customer/Login.php');
    exit;
}

$currentIp = $_SERVER['REMOTE_ADDR'] ?? '';
$savedIp = $_SESSION['admin_ip'] ?? '';
if ($savedIp !== '' && $currentIp !== '' && !hash_equals($savedIp, $currentIp)) {
    session_unset();
    session_destroy();
    header('Location: ../php-customer/Login.php');
    exit;
}

$_SESSION['admin_last_activity'] = time();
