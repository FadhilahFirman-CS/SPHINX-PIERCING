<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['register'])) {

    $username = trim($_POST['username']);
    $nohp     = trim($_POST['nohp']);
    $email    = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($username) || empty($nohp) || empty($email) || empty($password)) {
        $error = "Semua kolom wajib diisi.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Email tidak valid.";
    } elseif (strlen($password) < 6) {
        $error = "Password minimal 6 karakter.";
    } else {

        // Cek username atau email
        $cek = $conn->prepare("SELECT id FROM customers WHERE username=? OR email=?");
        $cek->bind_param("ss", $username, $email);
        $cek->execute();
        $cek->store_result();

        if ($cek->num_rows > 0) {

            $error = "Username atau Email sudah digunakan.";

        } else {

            $hash = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $conn->prepare("
                INSERT INTO customers
                (username,nama,email,password,telepon)
                VALUES (?,?,?,?,?)
            ");

            $stmt->bind_param(
                "sssss",
                $username,
                $username,
                $email,
                $hash,
                $nohp
            );

            if ($stmt->execute()) {

                header("Location: Login.php?registered=1");
                exit;

            } else {

                $error = "Gagal membuat akun.";

            }

            $stmt->close();
        }

        $cek->close();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buat Akun - Sphinx Piercing</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"/>
    <link rel="stylesheet" href="../css-customer/BuatAkun.css">
</head>
<body>

    <div class="container">
        <div class="left-section">
            <img src="../gambar/logo2.jpeg" alt="Logo Sphinx" class="logo-img">
            <div class="brand-name">@sphinx_piercing</div>
        </div>

        <div class="right-section">
            <h2>Buat Akun Baru</h2>
            <?php if ($error !== ''): ?>
                <p style="color: #ff6b6b; margin-bottom: 15px;"><?= htmlspecialchars($error) ?></p>
            <?php endif; ?>
            <form action="" method="POST">
                <input type="text" name="username" placeholder="Masukkan Username" required>
                <input type="tel" name="nohp" placeholder="Masukkan No HP" required>
                <input type="email" name="email" placeholder="Masukkan Email" required>
                <input type="password" name="password" placeholder="Masukkan Password" required>
                <button type="submit" name="register">Simpan</button>
            </form>
            <a href="Login.php" class="small-text">Sudah punya akun? Login di sini</a>
        </div>
    </div>

</body>
</html>