<?php
session_start();
require_once 'firebase.php';

$username = $_SESSION['customer_user'] ?? '';
if ($username === '' && empty($_SESSION['admin']) && !empty($_SESSION['user'])) {
	$username = (string)$_SESSION['user'];
}
$username = $username !== '' ? $username : "@sphnx_piercing";

// Ambil data profil pengguna dari Firebase
$profileData = $firestore->getDocument('profiles', $username);
$userData = $firestore->getDocument('users', $username);

$full_name = $profileData['full_name'] ?? $userData['username'] ?? $username;
$email = $profileData['email'] ?? $userData['email'] ?? '';
$phone = $profileData['phone'] ?? $userData['nohp'] ?? '';
$address = $profileData['address'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_profile'])) {
	$full_name = trim($_POST['full_name'] ?? $full_name);
	$email = trim($_POST['email'] ?? $email);
	$phone = trim($_POST['phone'] ?? $phone);
	$address = trim($_POST['address'] ?? $address);

	// Simpan perubahan profil ke Firebase
	$profileData = [
		'username' => $username,
		'full_name' => $full_name,
		'email' => $email,
		'phone' => $phone,
		'address' => $address,
		'updated_at' => date('Y-m-d H:i:s')
	];
	$result = $firestore->saveDocument('profiles', $username, $profileData);
	if (!isset($result['error'])) {
		$success_message = 'Profil berhasil diperbarui.';
	}

	// Proses perubahan password jika kolom diisi
	$pw_lama = $_POST['pw_lama'] ?? '';
	$pw_baru = $_POST['pw_baru'] ?? '';
	$pw_konfirmasi = $_POST['pw_konfirmasi'] ?? '';

	if ($pw_baru !== '') {
		if (strlen($pw_baru) < 6) {
			$pw_error = 'Password baru minimal 6 karakter.';
		} elseif ($pw_baru !== $pw_konfirmasi) {
			$pw_error = 'Konfirmasi password tidak cocok.';
		} else {
			$userDoc = $firestore->getDocument('users', $username);
			if ($userDoc && isset($userDoc['password']) && password_verify($pw_lama, $userDoc['password'])) {
				$firestore->saveDocument('users', $username, array_merge($userDoc, [
					'password' => password_hash($pw_baru, PASSWORD_DEFAULT)
				]));
				$pw_success = 'Password berhasil diubah.';
			} else {
				$pw_error = 'Password lama salah.';
			}
		}
	}
}

$current_page = basename($_SERVER['PHP_SELF']);
$page_css = '../css-customer/pengaturan.css';

include 'header.php';
?>
<div class="app">
	<aside class="sidebar">
		<div class="brand">
			<img src="../gambar/logo2.jpeg" alt="Logo">
			<span><?= htmlspecialchars($username) ?></span>
		</div>

		<ul class="menu">
			<li><a href="dashboard.php" class="<?= ($current_page == 'Dashboard.php' || $current_page == 'dashboard.php') ? 'active' : '' ?>"><i class="fa-solid fa-house"></i>Dashboard</a></li>
			<li><a href="riwayat.php" class="<?= $current_page == 'riwayat.php' ? 'active' : '' ?>"><i class="fa-solid fa-clock-rotate-left"></i>Riwayat Pemesanan</a></li>
			<li><a href="jadwal.php" class="<?= $current_page == 'jadwal.php' ? 'active' : '' ?>"><i class="fa-solid fa-calendar-check"></i>Jadwal Reservasi</a></li>

			<li><a href="pengaturan.php" class="<?= $current_page == 'pengaturan.php' ? 'active' : '' ?>"><i class="fa-solid fa-gear"></i>Pengaturan Akun</a></li>
		</ul>

		<div class="sidebar-footer">
			<ul class="menu">
				<li><a href="bantuan.php" class="<?= $current_page == 'bantuan.php' ? 'active' : '' ?>"><i class="fa-solid fa-circle-question"></i>Bantuan</a></li>
			</ul>
		</div>
	</aside>

	<main class="main">
		<div class="topbar">
			<div class="top-links">
				<!-- <a href="piercing_produk.php">Produk</a>
				<a href="jasa_piercing.php">Jasa</a>
				<a href="maps.php">Maps</a> -->
			</div>
			<div class="top-icons">
				<!-- <i class="fa-regular fa-bell"></i>
				<i class="fa-regular fa-user"></i> -->
			</div>
		</div>

		<section class="profile-section">
			<a href="dashboard.php" class="btn-back"><i class="fa-solid fa-arrow-left"></i> Kembali ke Dashboard</a>
			<div class="section-title">Profil Pengguna</div>

			<?php if (!empty($success_message)): ?>
				<div style="margin-bottom:16px; padding:12px 16px; background: rgba(130,255,91,0.15); color: var(--lime); border-radius: 10px; border: 1px solid rgba(130,255,91,0.3);">
					<?= htmlspecialchars($success_message) ?>
				</div>
			<?php endif; ?>
			<?php if (!empty($pw_success)): ?>
				<div style="margin-bottom:16px; padding:12px 16px; background: rgba(130,255,91,0.15); color: var(--lime); border-radius: 10px; border: 1px solid rgba(130,255,91,0.3);">
					<?= htmlspecialchars($pw_success) ?>
				</div>
			<?php endif; ?>
			<?php if (!empty($pw_error)): ?>
				<div style="margin-bottom:16px; padding:12px 16px; background: rgba(255,68,68,0.15); color: #ff4444; border-radius: 10px; border: 1px solid #ff4444;">
					<?= htmlspecialchars($pw_error) ?>
				</div>
			<?php endif; ?>

			<div class="profile-card">
				<div class="profile-details">
					<b id="display-name"><?= htmlspecialchars($full_name) ?></b>
					<div id="display-email"><?= htmlspecialchars($email) ?></div>
					<div id="display-phone"><?= htmlspecialchars($phone) ?></div>
						<?php if (!empty($address)): ?>
						<div id="display-address" style="margin-top:4px; font-size:13px; color:var(--text-soft);"><i class="fa-solid fa-location-dot" style="margin-right:6px;"></i><?= htmlspecialchars($address) ?></div>
						<?php else: ?>
						<div id="display-address" style="margin-top:4px; font-size:13px; color:#ff9900;"><i class="fa-solid fa-triangle-exclamation" style="margin-right:6px;"></i>Alamat belum diisi</div>
						<?php endif; ?>
				</div>

				<div>
					<button type="button" class="edit-btn" id="openEdit">Edit</button>
				</div>
			</div>

			<!-- Modal: Edit Profil -->
			<div id="modalOverlay" class="modal-overlay" style="display:none;">
				<div class="modal">
					<div class="modal-header">
						<h3>Edit Profil</h3>
						<button class="modal-close" id="closeEdit">×</button>
					</div>
					<form method="POST" class="modal-form">
						<label class="sr-only">Masukkan Username</label>
						<input name="full_name" type="text" placeholder="Masukkan Username" value="<?= htmlspecialchars($full_name) ?>" required>

						<label class="sr-only">Masukkan Email</label>
						<input name="email" type="email" placeholder="Masukkan Email" value="<?= htmlspecialchars($email) ?>" required>

						<label class="sr-only">Masukkan NoTelp</label>
						<input name="phone" type="tel" placeholder="Masukkan NoTelp" value="<?= htmlspecialchars($phone) ?>" required>

						<label class="sr-only">Alamat Lengkap</label>
						<textarea name="address" rows="3" placeholder="Masukkan alamat lengkap (untuk pengiriman GoSend/SPX)" style="width:100%; padding:12px 14px; border-radius:10px; border:1px solid rgba(255,255,255,0.1); background:rgba(255,255,255,0.05); color:var(--text); font-family:inherit; font-size:14px; resize:vertical;"><?= htmlspecialchars($address) ?></textarea>

						<!-- Ubah Password -->
						<div class="pw-section">
							<button type="button" class="pw-toggle" id="pwToggle">
								<i class="fa-solid fa-lock"></i> Ubah Password
								<i class="fa-solid fa-chevron-down pw-chevron" id="pwChevron"></i>
							</button>
							<div class="pw-fields" id="pwFields">
								<input name="pw_lama" type="password" placeholder="Password lama" autocomplete="current-password">
								<input name="pw_baru" type="password" placeholder="Password baru (min. 6 karakter)" autocomplete="new-password">
								<input name="pw_konfirmasi" type="password" placeholder="Konfirmasi password baru" autocomplete="new-password">
							</div>
						</div>

						<div style="display:flex;justify-content:flex-end;margin-top:18px;">
							<button type="submit" name="save_profile" class="save-btn">Simpan</button>
						</div>
					</form>
				</div>
			</div>
		</section>

	</main>
</div>

<script>
	// Logika buka/tutup modal edit profil
	const openBtn = document.getElementById('openEdit');
	const closeBtn = document.getElementById('closeEdit');
	const overlay = document.getElementById('modalOverlay');

	if (openBtn) openBtn.addEventListener('click', () => {
		overlay.style.display = 'flex';
		<?php if (!empty($pw_error)): ?>
		document.getElementById('pwFields').style.display = 'flex';
		document.getElementById('pwChevron').style.transform = 'rotate(180deg)';
		<?php endif; ?>
	});
	if (closeBtn) closeBtn.addEventListener('click', () => {
		overlay.style.display = 'none';
	});
	// Tutup modal saat area luar diklik
	if (overlay) overlay.addEventListener('click', (e) => {
		if (e.target === overlay) overlay.style.display = 'none';
	});

	// Tampilkan/sembunyikan kolom ubah password
	const pwToggle = document.getElementById('pwToggle');
	const pwFields = document.getElementById('pwFields');
	const pwChevron = document.getElementById('pwChevron');
	if (pwToggle) {
		pwToggle.addEventListener('click', () => {
			const open = pwFields.style.display === 'flex';
			pwFields.style.display = open ? 'none' : 'flex';
			pwChevron.style.transform = open ? 'rotate(0deg)' : 'rotate(180deg)';
		});
	}
</script>

</body>
</html>
