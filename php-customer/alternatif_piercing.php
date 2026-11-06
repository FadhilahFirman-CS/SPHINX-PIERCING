<?php
$current_page = basename($_SERVER['PHP_SELF']);
$page_title = "Katalog Piercing";
$page_heading = "Katalog Piercing";
$page_description = "Temukan pilihan piercing katalog yang unik dan menarik, cocok untuk gaya berbeda.";
$page_css = '../css-customer/alternatif_piercing.css';
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
            
            <div class="products-grid">
                <div class="product-card">
                    <img src="../gambar/Contoh-Alis.png" alt="Contoh Alis" class="product-image">
                    <div class="product-info">
                        <h3 class="product-name">Piercing Alis</h3>
                        <p class="product-desc">Piercing alis dengan bentuk rapi dan posisi estetis, cocok untuk memberi aksen wajah yang tajam dan modern.</p>
                    </div>
                </div>

                <div class="product-card">
                    <img src="../gambar/Contoh-Bibir.png" alt="Contoh Bibir" class="product-image">
                    <div class="product-info">
                        <h3 class="product-name">Piercing Bibir</h3>
                        <p class="product-desc">Piercing bibir yang stylish untuk model labret atau monroe, dengan teknik steril dan hasil simetris.</p>
                    </div>
                </div>

                <div class="product-card">
                    <img src="../gambar/Contoh-Lidah.png" alt="Contoh Lidah" class="product-image">
                    <div class="product-info">
                        <h3 class="product-name">Piercing Lidah</h3>
                        <p class="product-desc">Piercing lidah aman dan nyaman, direkomendasikan untuk gaya yang berani dengan perawatan aftercare yang lengkap.</p>
                    </div>
                </div>

                <div class="product-card">
                    <img src="../gambar/Contoh-Telinga.jpeg" alt="Contoh Telinga" class="product-image">
                    <div class="product-info">
                        <h3 class="product-name">Piercing Telinga</h3>
                        <p class="product-desc">Piercing telinga profesional untuk berbagai model seperti helix, tragus, dan daith, dengan hasil aman dan estetis.</p>
                    </div>
                </div>

                <!-- <div class="product-card">
                    <img src="../gambar/contoh-bintang.png" alt="Dparis Model Bintang" class="product-image">
                    <div class="product-info">
                        <h3 class="product-name">Dparis Model Bintang</h3>
                        <p class="product-desc">Desain unik dengan motif bintang yang menawan. Memberikan statement yang berani dan crafty untuk mereka yang ingin tampil beda dan ekspresif.</p>
                    </div>
                </div> -->

                <div class="product-card">
                    <img src="../gambar/Contoh-Hidung.png" alt="Contoh Hidung" class="product-image">
                    <div class="product-info">
                        <h3 class="product-name">Piercing Hidung</h3>
                        <p class="product-desc">Piercing hidung untuk model nose stud atau hoop, menggunakan bahan steril agar hasilnya rapi dan nyaman dipakai.</p>
                    </div>
                </div>

                <div class="product-card">
                    <img src="../gambar/Contoh1.png" alt="Contoh 1" class="product-image">
                    <div class="product-info">
                        <h3 class="product-name">Piercing Kontemporer 1</h3>
                        <p class="product-desc">Piercing modern dengan detail elegan, cocok untuk tampil beda dengan aksen yang halus dan penuh gaya.</p>
                    </div>
                </div>

                <div class="product-card">
                    <img src="../gambar/Contoh2.png" alt="Contoh 2" class="product-image">
                    <div class="product-info">
                        <h3 class="product-name">Piercing Kontemporer 2</h3>
                        <p class="product-desc">Model piercing elegan dengan sentuhan minimalis, memberikan tampilan bersih dan stylish untuk penampilan sehari-hari.</p>
                    </div>
                </div>

                <div class="product-card">
                    <img src="../gambar/Contoh3.png" alt="Contoh 3" class="product-image">
                    <div class="product-info">
                        <h3 class="product-name">Piercing Kontemporer 3</h3>
                        <p class="product-desc">Piercing serbaguna dengan tampilan bold, ideal untuk menambahkan aksen kuat pada gaya kasual atau formal.</p>
                    </div>
                </div>

                <div class="product-card">
                    <img src="../gambar/Contoh4.png" alt="Contoh 4" class="product-image">
                    <div class="product-info">
                        <h3 class="product-name">Piercing Kontemporer 4</h3>
                        <p class="product-desc">Desain piercing kontemporer dengan garis tegas dan finishing halus, sempurna untuk tampilan modern.</p>
                    </div>
                </div>

                <div class="product-card">
                    <img src="../gambar/Contoh5.png" alt="Contoh 5" class="product-image">
                    <div class="product-info">
                        <h3 class="product-name">Piercing Kontemporer 5</h3>
                        <p class="product-desc">Model piercing kreatif dengan detail unik, cocok untuk menonjolkan gaya personal yang berani.</p>
                    </div>
                </div>

                <div class="product-card">
                    <img src="../gambar/Contoh6.png" alt="Contoh 6" class="product-image">
                    <div class="product-info">
                        <h3 class="product-name">Piercing Kontemporer 6</h3>
                        <p class="product-desc">Piercing dengan detail menarik dan tekstur khusus, dibuat untuk memberi aksen berbeda pada penampilan Anda.</p>
                    </div>
                </div>

                <div class="product-card">
                    <img src="../gambar/Contoh7.png" alt="Contoh 7" class="product-image">
                    <div class="product-info">
                        <h3 class="product-name">Piercing Kontemporer 7</h3>
                        <p class="product-desc">Aksesori piercing modern dengan desain edgy, sempurna untuk tampilan urban yang berani dan trendi.</p>
                    </div>
                </div>

                <!-- <div class="product-card">
                    <img src="../gambar/contoh-bintang.png" alt="Dparis Model Bintang" class="product-image">
                    <div class="product-info">
                        <h3 class="product-name">Dparis Model Bintang</h3>
                        <p class="product-desc">Desain unik dengan motif bintang yang menawan. Memberikan statement yang berani dan crafty untuk mereka yang ingin tampil beda dan ekspresif.</p>
                    </div>
                </div> -->

                <!-- <div class="product-card">
                    <img src="../gambar/contoh-bintang.png" alt="Dparis Model Bintang" class="product-image">
                    <div class="product-info">
                        <h3 class="product-name">Dparis Model Bintang</h3>
                        <p class="product-desc">Desain unik dengan motif bintang yang menawan. Memberikan statement yang berani dan crafty untuk mereka yang ingin tampil beda dan ekspresif.</p>
                    </div>
                </div> -->
            </div>
            
            <a class="btn-back" href="dashboard.php"><i class="fa-solid fa-arrow-left"></i>Kembali ke Dashboard</a>
        </section>
    </main>
</div>
</body>
</html>

