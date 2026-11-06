<?php
$current_page = basename($_SERVER['PHP_SELF']);
$page_title = "Jasa Piercing";
$page_heading = "Jasa Piercing";
$page_description = "Lihat layanan jasa piercing profesional dengan prosedur aman dan nyaman untuk semua jenis piercing.";
$page_css = '../css-customer/jasa_piercing.css';
$username = $_SESSION['customer_user'] ?? '';
if ($username === '' && empty($_SESSION['admin']) && !empty($_SESSION['user'])) {
    $username = (string)$_SESSION['user'];
}
$username = $username !== '' ? $username : '@sphnx_piercing';
include 'header.php';
?>
<div class="app">
    <aside class="sidebar">
        <div class="brand">
            <img src="../gambar/logo2.jpeg" alt="Logo">
            <span><?= htmlspecialchars($username) ?></span>
        </div>
        <ul class="menu">
            <li><a href="dashboard.php"><i class="fa-solid fa-house"></i>Dashboard</a></li>
            <li><a href="riwayat.php"><i class="fa-solid fa-clock-rotate-left"></i>Riwayat Pemesanan</a></li>
            <li><a href="jadwal.php"><i class="fa-solid fa-calendar-check"></i>Jadwal Reservasi</a></li>

            <li><a href="pengaturan.php"><i class="fa-solid fa-gear"></i>Pengaturan Akun</a></li>
        </ul>
        <div class="sidebar-footer">
            <ul class="menu">
                <li><a href="bantuan.php"><i class="fa-solid fa-circle-question"></i>Bantuan</a></li>
            </ul>
        </div>
    </aside>
    <main class="main">
        <section class="feature-page">
            <h1><?= htmlspecialchars($page_heading) ?></h1>
            <p><?= htmlspecialchars($page_description) ?></p>
            <div class="feature-list">
                <div class="feature-card" style="grid-column:1 / -1;">
                    <h2>Slot Booking</h2>
                    <p>Jadwal yang sudah terisi ditandai dengan warna merah untuk menunjukkan slot penuh.</p>
                    <div class="slot-table-wrap">
                        <table class="slot-table">
                            <thead>
                                <tr>
                                    <th>Hari</th>
                                    <th>Tanggal</th>
                                    <th>Waktu</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Selasa</td>
                                    <td>16 April 2024</td>
                                    <td>14:00 - 15:00 WIB</td>
                                    <td class="status-full">Full</td>
                                </tr>
                                <tr>
                                    <td>Kamis</td>
                                    <td>18 April 2024</td>
                                    <td>11:00 - 12:00 WIB</td>
                                    <td class="status-full">Full</td>
                                </tr>
                                <tr>
                                    <td>Sabtu</td>
                                    <td>20 April 2024</td>
                                    <td>16:30 - 17:30 WIB</td>
                                    <td class="status-full">Full</td>
                                </tr>
                                <tr>
                                    <td>Minggu</td>
                                    <td>21 April 2024</td>
                                    <td>10:00 - 11:00 WIB</td>
                                    <td class="status-available">Tersedia</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="feature-card" style="grid-column:1 / -1;">
                    <h2>Layanan Profesional</h2>
                    <div class="service-block">
                        <div class="service-copy">
                            <p>Silakan pilih layanan berdasarkan jenis piercing dan penjelasan prosedur yang tersedia:</p>
                            <div class="service-grid">
                                <div class="service-card">
                                    <img src="../gambar/Contoh-Telinga.jpeg" alt="Telinga (Ear Lobe)">
                                    <div class="service-card-content">
                                        <h3>Telinga (Ear Lobe)</h3>
                                        <p>Piercing klasik di daun telinga dengan perawatan cepat dan minim nyeri.</p>
                                        <span class="price-badge"><i class="fa-solid fa-tag"></i>Rp 85.000</span>
                                    </div>
                                </div>
                                <div class="service-card">
                                    <img src="../gambar/Contoh-Hidung.png" alt="Hidung (Nose)">
                                    <div class="service-card-content">
                                        <h3>Hidung (Nose)</h3>
                                        <p>Piercing hidung profesional untuk model lubang kanan atau kiri.</p>
                                        <span class="price-badge"><i class="fa-solid fa-tag"></i>Rp 100.000</span>
                                    </div>
                                </div>
                                <div class="service-card">
                                    <img src="../gambar/Contoh-Alis.png" alt="Alis (Eyebrow)">
                                    <div class="service-card-content">
                                        <h3>Alis (Eyebrow)</h3>
                                        <p>Piercing alis rapi dengan posisi estetis dan aman.</p>
                                        <span class="price-badge"><i class="fa-solid fa-tag"></i>Rp 120.000</span>
                                    </div>
                                </div>
                                <div class="service-card">
                                    <img src="../gambar/Contoh-Bibir.png" alt="Bibir (Lip)">
                                    <div class="service-card-content">
                                        <h3>Bibir (Lip)</h3>
                                        <p>Piercing bibir atas/bawah dengan teknik steril dan aftercare lengkap.</p>
                                        <span class="price-badge"><i class="fa-solid fa-tag"></i>Rp 110.000</span>
                                    </div>
                                </div>
                                <div class="service-card">
                                    <img src="../gambar/Contoh-Lidah.png" alt="Lidah (Tongue)">
                                    <div class="service-card-content">
                                        <h3>Industrial Piercing</h3>
                                        <p>Piercing kompleks pada daun telinga dengan dua lubang sekaligus.</p>
                                        <span class="price-badge"><i class="fa-solid fa-tag"></i>Rp 150.000</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <a class="btn-back" href="dashboard.php"><i class="fa-solid fa-arrow-left"></i>Kembali ke Dashboard</a>
            <a class="btn-back" href="jadwal.php"><i class="fa-solid fa-calendar-check"></i>Reservasi ke Jadwal</a>
        </section>
    </main>
</div>
</body>
</html>
