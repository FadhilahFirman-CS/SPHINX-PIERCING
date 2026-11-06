<?php
/**
 * MySQL Database Configuration
 * Replace Firebase dengan MySQL untuk Sphinx Piercing
 */

// Database Connection Settings
// Lokal (XAMPP) : DB_HOST = 'localhost'
// Hosting       : DB_HOST = 'sql111.infinityfree.com'
define('DB_HOST', 'localhost');          // XAMPP lokal
define('DB_USER', 'root');               // default XAMPP user
define('DB_PASS', '');                   // default XAMPP (kosong)
define('DB_NAME', 'sphinx_piercing');    // nama database lokal

// Create Connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check Connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset to UTF8
$conn->set_charset("utf8mb4");

// Set timezone
date_default_timezone_set('Asia/Jakarta');

?>
