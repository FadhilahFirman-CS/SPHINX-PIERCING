<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
require_once 'config.php'; // Pastikan di dalam config.php mendefinisikan koneksi PDO, misal: $pdo
$error = '';
$google_error = '';

// 1. PROSES RESET PASSWORD (AJAX FETCH)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'reset_password') {
    header('Content-Type: application/json');

    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $newPassword = $_POST['new_password'] ?? '';

    if ($username === '' || $email === '' || $newPassword === '') {
        echo json_encode(['success' => false, 'message' => 'Semua kolom wajib diisi.']);
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Format email tidak valid.']);
        exit;
    }

    if (strlen($newPassword) < 6) {
        echo json_encode(['success' => false, 'message' => 'Password minimal 6 karakter.']);
        exit;
    }

    // Mengambil data user berdasarkan username di MySQL menggunakan PDO
    try {
        $stmt = $pdo->prepare("SELECT * FROM customers WHERE username = :username LIMIT 1");
        $stmt->execute(['username' => $username]);
        $userDoc = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$userDoc) {
            echo json_encode(['success' => false, 'message' => 'Username tidak ditemukan.']);
            exit;
        }

        $savedEmail = strtolower(trim($userDoc['email'] ?? ''));
        if ($savedEmail === '' || $savedEmail !== strtolower($email)) {
            echo json_encode(['success' => false, 'message' => 'Email tidak cocok dengan akun.']);
            exit;
        }

        // Update password baru di MySQL
        $newPasswordHash = password_hash($newPassword, PASSWORD_DEFAULT);
        $updatedAt = date('Y-m-d H:i:s');
        
        $updateStmt = $pdo->prepare("UPDATE customers SET password = :password, updated_at = :updated_at WHERE username = :username");
        $result = $updateStmt->execute([
            'password' => $newPasswordHash,
            'updated_at' => $updatedAt,
            'username' => $username
        ]);

        if (!$result) {
            echo json_encode(['success' => false, 'message' => 'Gagal memperbarui password.']);
            exit;
        }

        echo json_encode(['success' => true, 'message' => 'Password berhasil diubah. Silakan login.']);
        exit;
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan database.']);
        exit;
    }
}

// 2. LOGIN VIA EMAIL (Modal Google palsu / custom email login)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'])) {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    try {
        // Cari akun berdasarkan email langsung di MySQL (jauh lebih cepat dibanding loop Firestore)
        $stmt = $pdo->prepare("SELECT * FROM customers WHERE LOWER(email) = LOWER(:email) LIMIT 1");
        $stmt->execute(['email' => $email]);
        $foundUser = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($foundUser && isset($foundUser['password']) && password_verify($password, $foundUser['password'])) {
            session_regenerate_id(true);
            unset($_SESSION['admin'], $_SESSION['admin_login_time'], $_SESSION['admin_ip']);
            
            $foundUsername = $foundUser['username'];
            $_SESSION['user'] = $foundUsername;
            $_SESSION['customer_user'] = $foundUsername;
            $_SESSION['user_data'] = $foundUser;
            $redirect = basename($_GET['redirect'] ?? '');
            $allowed  = ['riwayat.php','jadwal.php','metode.php','pengaturan.php','beli.php','Dashboard.php'];
            $dest = in_array($redirect, $allowed, true) ? $redirect : 'Dashboard.php';
            header('Location: ' . $dest);
            exit;
        } else {
            $google_error = 'Email atau password salah!';
        }
    } catch (PDOException $e) {
        $google_error = 'Terjadi kesalahan database.';
    }
}

// 3. LOGIN VIA USERNAME STANDARD / ADMIN
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['email'])) {

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $adminUsername = 'admin-kece';
    $adminPassword = 'admin-kece';

    // Login Admin
    if ($username === $adminUsername && $password === $adminPassword) {

        session_regenerate_id(true);

        $_SESSION['admin'] = true;
        $_SESSION['user'] = $adminUsername;

        unset($_SESSION['customer_user']);
        unset($_SESSION['user_data']);

        $_SESSION['admin_login_time'] = time();
        $_SESSION['admin_ip'] = $_SERVER['REMOTE_ADDR'] ?? '';

        header("Location: ../admin/Dashboard-admin.php");
        exit;
    }

    // Login Customer
    $stmt = $conn->prepare("SELECT * FROM customers WHERE username=? LIMIT 1");

    if (!$stmt) {
        die("Prepare gagal : " . $conn->error);
    }

    $stmt->bind_param("s", $username);
    $stmt->execute();

    $result = $stmt->get_result();
    $userData = $result->fetch_assoc();

    if ($userData && password_verify($password, $userData['password'])) {

        session_regenerate_id(true);

        unset($_SESSION['admin']);
        unset($_SESSION['admin_login_time']);
        unset($_SESSION['admin_ip']);

        $_SESSION['user'] = $userData['username'];
        $_SESSION['customer_user'] = $userData['username'];
        $_SESSION['user_data'] = $userData;

        // Redirect ke halaman asal jika ada parameter ?redirect=
        $redirect = basename($_GET['redirect'] ?? '');
        $allowed  = ['riwayat.php','jadwal.php','metode.php','pengaturan.php','beli.php','Dashboard.php'];
        $dest = in_array($redirect, $allowed, true) ? $redirect : 'Dashboard.php';
        header('Location: ' . $dest);
        exit;

    } else {

        $error = "Username atau password salah!";

    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sphinx Piercing</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"/>
    <link rel="stylesheet" href="../css-customer/Login.css">
</head>
<body>

    <div class="container">
        <div class="left-section">
            <img src="../gambar/logo2.jpeg" alt="Logo Sphinx" class="logo-img">
            <div class="brand-name">@sphinx_piercing</div>
        </div>

        <div class="right-section">
            <?php if (isset($_GET['registered'])): ?>
                <div style="background: rgba(0,255,0,0.15); color: #7FFF00; padding: 12px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #7FFF00;">
                    Akun berhasil dibuat! Silakan login.
                </div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div style="background: rgba(255,0,0,0.2); color: #ff4444; padding: 12px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #ff4444;">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>
            <h2>Selamat Datang, Kawan!!</h2>
            
            <form action="" method="POST">
                <input type="text" name="username" placeholder="Username" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required>
                <input type="password" name="password" placeholder="Password" required>
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:4px;">
                    <button type="submit" class="btn">Masuk</button>
                    <button type="button" id="btnLupaPassword" style="background:none;border:none;color:#96DB70;font-size:0.8rem;cursor:pointer;padding:0;align-self:auto;transition:color 0.2s;"
                        onmouseover="this.style.color='#fff'" onmouseout="this.style.color='#96DB70'">Lupa Password?</button>
                </div>
            </form>

            <div class="divider">atau</div>

            <button type="button" class="btn-google" id="btnGoogleLogin">
                <img src="https://www.gstatic.com/firebasejs/ui/2.0.0/images/auth/google.svg" alt="Google">
                Masuk dengan Google
            </button>

            <a href="BuatAkun.php" class="small-text"> Buat Akun </a>

            <div style="text-align:center; margin-top:18px; padding-top:16px; border-top:1px solid rgba(255,255,255,0.1);">
                <p style="color:#888; font-size:12px; margin-bottom:10px;">Hanya ingin melihat-lihat?</p>
                <a href="Dashboard.php" style="
                    display:inline-flex; align-items:center; gap:8px;
                    color:#aaa; font-size:13px; text-decoration:none;
                    padding:8px 20px; border-radius:999px;
                    border:1px solid rgba(255,255,255,0.15);
                    background:rgba(255,255,255,0.04);
                    transition:all 0.2s;
                " onmouseover="this.style.borderColor='#7FFF00';this.style.color='#7FFF00';"
                   onmouseout="this.style.borderColor='rgba(255,255,255,0.15)';this.style.color='#aaa';">
                    <i class="fa-solid fa-eye"></i> Lanjutkan sebagai Tamu
                </a>
            </div>
        </div><!-- /right-section -->
    </div><!-- /container -->

    <div class="modal-overlay" id="googleModal">
        <div class="modal-box">
            <button type="button" class="modal-close-btn" id="closeGoogleModal"><i class="fa-solid fa-xmark"></i></button>
            <div class="modal-logo">
                <img src="https://www.gstatic.com/firebasejs/ui/2.0.0/images/auth/google.svg" alt="Google">
                <span>Login dengan Google</span>
            </div>
            <h3>Masuk Akun Google</h3>
            <p>Gunakan email Google kamu untuk masuk</p>
            <form action="Login.php" method="POST">
                <input type="hidden" name="google_login" value="1">
                <label>Email Google</label>
                <?php if ($google_error): ?>
                <div style="background:rgba(255,0,0,0.2);color:#ff4444;padding:10px 12px;border-radius:6px;margin-bottom:12px;border-left:4px solid #ff4444;font-size:13px;">
                    <?= htmlspecialchars($google_error) ?>
                </div>
                <?php endif; ?>
                <input type="email" name="email" placeholder="contoh@gmail.com" required>
                <label>Password</label>
                <input type="password" name="password" placeholder="Password akun" required>
                <button type="submit" class="modal-submit">Masuk</button>
            </form>
        </div>
    </div>

    <div class="modal-overlay" id="lupaPasswordModal">
        <div class="modal-box">
            <button type="button" class="modal-close-btn" id="closeLupaModal"><i class="fa-solid fa-xmark"></i></button>
            <div style="text-align:center; margin-bottom:18px;">
                <div style="width:52px;height:52px;background:rgba(127,255,0,0.12);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 12px;">
                    <i class="fa-solid fa-lock" style="color:#7FFF00;font-size:22px;"></i>
                </div>
            </div>
            <h3>Ubah Password</h3>
            <p>Masukkan data akun kamu untuk mengatur ulang password</p>
            <form id="formLupaPassword">
                <label>Username</label>
                <input type="text" id="resetUsername" placeholder="Username kamu" required>
                <label>Email</label>
                <input type="email" id="resetEmail" placeholder="Email terdaftar" required>
                <label>Password Baru</label>
                <input type="password" id="resetPasswordBaru" placeholder="Minimal 6 karakter" required>
                <label>Konfirmasi Password Baru</label>
                <input type="password" id="resetPasswordKonfirmasi" placeholder="Ulangi password baru" required>
                <div id="resetMsg" style="display:none; font-size:12px; padding:10px 12px; border-radius:6px; margin-bottom:14px;"></div>
                <button type="submit" class="modal-submit">Ubah Password</button>
            </form>
        </div>
    </div>

    <script>
        // --- Modal Login Google ---
        <?php if ($google_error): ?>
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('googleModal').classList.add('active');
        });
        <?php endif; ?>
        document.getElementById('btnGoogleLogin').addEventListener('click', function () {
            document.getElementById('googleModal').classList.add('active');
        });
        document.getElementById('closeGoogleModal').addEventListener('click', function () {
            document.getElementById('googleModal').classList.remove('active');
        });
        document.getElementById('googleModal').addEventListener('click', function (e) {
            if (e.target === this) this.classList.remove('active');
        });

        // --- Modal Lupa Password ---
        document.getElementById('btnLupaPassword').addEventListener('click', function () {
            document.getElementById('lupaPasswordModal').classList.add('active');
        });
        document.getElementById('closeLupaModal').addEventListener('click', function () {
            document.getElementById('lupaPasswordModal').classList.remove('active');
        });
        document.getElementById('lupaPasswordModal').addEventListener('click', function (e) {
            if (e.target === this) this.classList.remove('active');
        });

        document.getElementById('formLupaPassword').addEventListener('submit', function (e) {
            e.preventDefault();
            const baru = document.getElementById('resetPasswordBaru').value;
            const konfirmasi = document.getElementById('resetPasswordKonfirmasi').value;
            const username = document.getElementById('resetUsername').value.trim();
            const email = document.getElementById('resetEmail').value.trim();
            const msg = document.getElementById('resetMsg');

            if (baru.length < 6) {
                msg.style.display = 'block';
                msg.style.background = 'rgba(255,68,68,0.15)';
                msg.style.color = '#ff4444';
                msg.style.border = '1px solid #ff4444';
                msg.textContent = 'Password minimal 6 karakter.';
                return;
            }
            if (baru !== konfirmasi) {
                msg.style.display = 'block';
                msg.style.background = 'rgba(255,68,68,0.15)';
                msg.style.color = '#ff4444';
                msg.style.border = '1px solid #ff4444';
                msg.textContent = 'Konfirmasi password tidak cocok.';
                return;
            }

            fetch('Login.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8'
                },
                body: new URLSearchParams({
                    action: 'reset_password',
                    username: username,
                    email: email,
                    new_password: baru
                })
            })
            .then((response) => response.json())
            .then((data) => {
                msg.style.display = 'block';
                if (data.success) {
                    msg.style.background = 'rgba(127,255,0,0.12)';
                    msg.style.color = '#7FFF00';
                    msg.style.border = '1px solid #7FFF00';
                    this.reset();
                    setTimeout(() => {
                        document.getElementById('lupaPasswordModal').classList.remove('active');
                        msg.style.display = 'none';
                    }, 1700);
                } else {
                    msg.style.background = 'rgba(255,68,68,0.15)';
                    msg.style.color = '#ff4444';
                    msg.style.border = '1px solid #ff4444';
                }
                msg.textContent = data.message || 'Terjadi kesalahan saat memproses permintaan.';
            })
            .catch(() => {
                msg.style.display = 'block';
                msg.style.background = 'rgba(255,68,68,0.15)';
                msg.style.color = '#ff4444';
                msg.style.border = '1px solid #ff4444';
                msg.textContent = 'Gagal terhubung ke server. Coba lagi.';
            });
        });
    </script>

</body>
</html>