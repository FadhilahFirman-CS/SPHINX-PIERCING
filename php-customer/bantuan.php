<?php
$current_page = basename($_SERVER['PHP_SELF']);
$page_css = '../css-customer/bantuan.css';
include 'header.php';
?>

<div class="app">
    <aside class="sidebar">
        <div class="brand">
            <img src="../gambar/logo2.jpeg" alt="Logo">
            <span><?= isset($username) ? htmlspecialchars($username) : 'Tamu' ?></span>
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

    <main class="main">
        <div class="header-top">
            <h1 class="page-title">Bantuan & Kebijakan</h1>
        </div>

        <!-- <div class="toc">
            <a href="#privasi">Kebijakan Privasi</a>
            <a href="#bantuan">Bantuan</a>
            <a href="#syarat">Syarat & Ketentuan</a>
        </div> -->

        <section id="privasi" class="card">
            <h2>Kebijakan Privasi</h2>
            <p>
                Kami menghargai privasi Anda. Informasi yang dikumpulkan (mis. nama, alamat email, data pemesanan) digunakan untuk memproses layanan, meningkatkan pengalaman, dan komunikasi terkait akun.
                Data tidak akan dibagikan kepada pihak ketiga tanpa persetujuan kecuali bila diwajibkan oleh hukum. Anda dapat meminta akses, koreksi, atau penghapusan data melalui pengaturan akun atau menghubungi layanan pelanggan.
            </p>
            <p>
                Untuk keamanan, kami menerapkan langkah teknis dan kebijakan internal untuk melindungi data dari akses yang tidak sah. Dengan menggunakan layanan ini, Anda menyetujui pengumpulan dan pemrosesan data sesuai kebijakan kami.
            </p>
        </section>

        <section id="bantuan" class="card">
            <h2>Bantuan</h2>
            <p>
                Jika Anda menemui masalah saat melakukan pemesanan, pembayaran, atau ingin mengubah jadwal, silakan ikuti langkah berikut:
            </p>
            <ol style="color:var(--text-soft); margin-left:18px; margin-top:8px;">
                <li>Periksa bagian Riwayat atau Jadwal pada akun Anda untuk status dan detail pemesanan.</li>
                <li>Gunakan fitur Pengaturan untuk memperbarui informasi akun atau metode pembayaran.</li>
                <li>Jika masih bermasalah, hubungi tim dukungan melalui email: support@example.com atau telepon: 0812-3456-7890.</li>
            </ol>
            <p style="margin-top:8px;">Jam layanan pelanggan: Senin–Jumat, 09:00–17:00 WIB.</p>
        </section>

        <section id="syarat" class="card">
            <h2>Syarat & Ketentuan</h2>
            <p>
                Dengan menggunakan layanan kami, Anda setuju untuk mematuhi syarat dan ketentuan berikut: layanan hanya boleh dipakai untuk tujuan yang sah; Anda bertanggung jawab atas keakuratan informasi yang diberikan; pihak kami berhak menolak atau membatalkan pesanan yang melanggar ketentuan.
            </p>
            <p>
                Pembayaran yang sudah dikonfirmasi bersifat final kecuali ada kebijakan pengembalian dana yang berlaku. Kami dapat mengubah syarat ini sewaktu-waktu; perubahan akan diumumkan di situs dan berlaku sejak tanggal publikasi.
            </p>
        </section>

    </main>
</div>

</body>
</html>