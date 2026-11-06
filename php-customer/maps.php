<?php
$current_page = basename($_SERVER['PHP_SELF']);
$page_css = '../css-customer/maps.css';
include 'header.php';
?>
    <div class="app">
        <!-- SIDEBAR -->
        <aside class="sidebar">
            <div class="brand">
                <img src="../gambar/logo2.jpeg" alt="Logo">
                <span>Sphinx Piercing</span>
            </div>

            <ul class="menu">
                <li><a href="dashboard.php" class="<?= ($current_page == 'dashboard.php' || $current_page == 'Dashboard.php') ? 'active' : '' ?>"><i class="fa-solid fa-house"></i>Dashboard</a></li>
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

        <!-- MAIN CONTENT -->
        <div class="main">
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

            <!-- BACK BUTTON -->
            <div style="margin-bottom: 16px;">
                <a href="dashboard.php" style="display:inline-flex; align-items:center; gap:8px; text-decoration:none; color:var(--text-soft); font-size:14px; background:var(--bg-card); padding:8px 16px; border-radius:999px; border:1px solid rgba(255,255,255,0.1); transition:.2s;" onmouseover="this.style.color='#82ff5b'; this.style.borderColor='#82ff5b';" onmouseout="this.style.color=''; this.style.borderColor='rgba(255,255,255,0.1)';">
                    <i class="fa-solid fa-arrow-left"></i> Kembali ke Dashboard
                </a>
            </div>

            <!-- MAPS CONTENT -->
            <div class="maps-container">
                <!-- Google Maps -->
                <div class="maps-frame">
                    <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3953.3!2d110.40183!3d-7.83012!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e7a5707b1b1b1b1%3A0x1b1b1b1b1b1b1b1b!2sJl.%20Plumbon%2C%20Modalan%2C%20Banguntapan!5e0!3m2!1sid!2sid!4v1700000000000" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                </div>

                <!-- Location Info -->
                <div class="location-info">
                    <div>
                        <h2>Studio Piercing Sphinx</h2>
                        <span style="color: var(--text-soft); font-size: 14px;">Lokasi Toko Kami</span>
                    </div>

                    <div class="info-item">
                        <i class="fa-solid fa-location-dot"></i>
                        <div class="info-item-content">
                            <strong>Alamat</strong>
                            <span>Ada motor mio putih, No 1 C, Jalan Unggas Rt 11 Taman Pratama,<br>Jl. Plumbon, Modalan, Banguntapan,<br>Kec. Banguntapan, Kabupaten Bantul,<br>Daerah Istimewa Yogyakarta 55191</span>
                        </div>
                    </div>

                    <div class="info-item">
                        <i class="fa-solid fa-clock"></i>
                        <div class="info-item-content">
                            <strong>Jam Operasional</strong>
                            <span>Senin - Minggu<br>10:00 - 20:00 WIB</span>
                        </div>
                    </div>

                    <div class="info-item">
                        <i class="fa-solid fa-phone"></i>
                        <div class="info-item-content">
                            <strong>Hubungi Kami</strong>
                            <span>081994799058</span>
                        </div>
                    </div>

                    <div class="info-item">
                        <i class="fa-brands fa-instagram"></i>
                        <div class="info-item-content">
                            <strong>Instagram</strong>
                            <a href="https://www.instagram.com/sphinx_piercingjogja" target="_blank" style="color:var(--lime);text-decoration:none;">@sphinx_piercingjogja</a>
                        </div>
                    </div>

                    <div class="contact-buttons">
                        <a href="https://wa.me/6281994799058" class="contact-btn whatsapp" target="_blank">
                            <i class="fa-brands fa-whatsapp"></i> WhatsApp
                        </a>
                        <a href="tel:081994799058" class="contact-btn call">
                            <i class="fa-solid fa-phone"></i> Hubungi
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
