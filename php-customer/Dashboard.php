﻿<?php 
if (session_status() == PHP_SESSION_NONE) { session_start(); }
$current_session_username = $_SESSION['customer_user'] ?? '';
if ($current_session_username === '' && empty($_SESSION['admin']) && !empty($_SESSION['user'])) {
    $current_session_username = (string)$_SESSION['user'];
}
$is_logged_in = $current_session_username !== '';
$page_css = '../css-customer/Dashboard.css?v=6';
include 'header.php'; 
?>
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
$is_guest = $current_session_username === '';
$username = $is_guest ? 'Tamu' : $current_session_username;
$promo_title = "Promo Piercing";
$promo_sub = "Diskon 20%";

$current_page = basename($_SERVER['PHP_SELF']);
?>
<div class="app">
    <aside class="sidebar">
        <div class="brand">
            <img src="../gambar/logo2.jpeg" alt="Logo">
            <span><?= htmlspecialchars($username) ?></span>
        </div>

        <ul class="menu">
            <li><a href="dashboard.php" class="<?= ($current_page == 'dashboard.php' || $current_page == 'Dashboard.php') ? 'active' : '' ?>"><i class="fa-solid fa-house"></i>Dashboard</a></li>
            <?php if ($is_guest): ?>
                <li><a href="Login.php?redirect=riwayat.php" class="menu-locked"><i class="fa-solid fa-clock-rotate-left"></i>Riwayat Pemesanan <i class="fa-solid fa-lock menu-lock-icon"></i></a></li>
                <li><a href="Login.php?redirect=jadwal.php" class="menu-locked"><i class="fa-solid fa-calendar-check"></i>Jadwal Reservasi <i class="fa-solid fa-lock menu-lock-icon"></i></a></li>
                <li><a href="Login.php?redirect=pengaturan.php" class="menu-locked"><i class="fa-solid fa-gear"></i>Pengaturan Akun <i class="fa-solid fa-lock menu-lock-icon"></i></a></li>
            <?php else: ?>
                <li><a href="riwayat.php" class="<?= $current_page == 'riwayat.php' ? 'active' : '' ?>"><i class="fa-solid fa-clock-rotate-left"></i>Riwayat Pemesanan</a></li>
                <li><a href="jadwal.php" class="<?= $current_page == 'jadwal.php' ? 'active' : '' ?>"><i class="fa-solid fa-calendar-check"></i>Jadwal Reservasi</a></li>
                <li><a href="pengaturan.php" class="<?= $current_page == 'pengaturan.php' ? 'active' : '' ?>"><i class="fa-solid fa-gear"></i>Pengaturan Akun</a></li>
            <?php endif; ?>
        
        <div class="sidebar-footer">
            <ul class="menu">
                  <li><a href="bantuan.php" class="<?= $current_page == 'bantuan.php' ? 'active' : '' ?>"><i class="fa-solid fa-circle-question"></i>Bantuan</a></li>
            </ul>
        </div>
    </aside>

    <main class="main">
        <!-- Search Bar -->
        <div class="search-wrap">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="text" id="searchInput" class="search-input" placeholder="Cari produk piercing... (contoh: titanium, barbell)">
            <button class="search-clear" id="searchClear"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <p class="search-results-label" id="searchLabel">Menampilkan <span id="searchCount">0</span> hasil untuk "<span id="searchQuery"></span>"</p>

        <div style="margin: 0 0 20px; padding: 14px 18px; border: 1px solid rgba(255,255,255,0.08); border-radius: 14px; background: rgba(20,6,43,0.9); display:flex; justify-content:space-between; gap:16px; align-items:center; flex-wrap:wrap;">
            <div>
                <div style="font-size:14px; font-weight:700; color:var(--text); margin-bottom:4px;">
                    <?php if ($is_guest): ?>
                        <i class="fa-solid fa-eye" style="color:#7FFF00;margin-right:6px;"></i>Mode Tamu
                    <?php else: ?>
                        <i class="fa-solid fa-circle-check" style="color:#7FFF00;margin-right:6px;"></i>Selamat datang, <?= htmlspecialchars($username) ?>!
                    <?php endif; ?>
                </div>
                <div style="font-size:12px; color:var(--text-soft); line-height:1.5;">
                    <?php if ($is_guest): ?>
                        Kamu sedang browsing sebagai tamu. <strong style="color:#aaa;">Login</strong> untuk checkout, simpan pesanan, dan akses semua fitur.
                    <?php else: ?>
                        Akun kamu sudah masuk. Semua fitur tersedia.
                    <?php endif; ?>
                </div>
            </div>
            <?php if ($is_guest): ?>
            <div style="display:flex;gap:8px;flex-wrap:wrap;">
                <a href="Login.php" style="text-decoration:none; background:var(--lime); color:#111; padding:9px 16px; border-radius:999px; font-weight:700; font-size:12px; display:inline-flex; align-items:center; gap:6px; white-space:nowrap;">
                    <i class="fa-solid fa-right-to-bracket"></i> Login
                </a>
                <a href="BuatAkun.php" style="text-decoration:none; background:transparent; color:var(--lime); padding:9px 16px; border-radius:999px; font-weight:600; font-size:12px; display:inline-flex; align-items:center; gap:6px; border:1px solid var(--lime); white-space:nowrap;">
                    <i class="fa-solid fa-user-plus"></i> Daftar
                </a>
            </div>
            <?php endif; ?>
        </div>

        <!-- Iklan Produk Slideshow -->
        <div class="ad-slideshow" id="adSlideshow">

            <div class="ad-slide active">
                <div class="ad-slide-img"><img src="../gambar/cubic-zironia.jpg" alt="Cubic Zirconia"></div>
                <div class="ad-slide-info">
                    <span class="ad-slide-badge">Terlaris</span>
                    <div class="ad-slide-name">Cubic Zirconia</div>
                    <div class="ad-slide-desc">Desain klasik dengan batu cubic zirconia berkilau, cocok untuk tampilan elegan pada berbagai jenis piercing.</div>
                    <div class="ad-slide-sizes">
                        <span class="ad-size-tag">Diameter 6mm</span>
                        <span class="ad-size-tag">Diameter 8mm</span>
                        <span class="ad-size-tag">Diameter 10mm</span>
                        <span class="ad-size-tag">18G / 16G</span>
                    </div>
                    <div class="ad-slide-price">Rp 30.000</div>
                </div>
            </div>

            <div class="ad-slide">
                <div class="ad-slide-img"><img src="../gambar/titanium-earrings.jpg" alt="Titanium Earrings"></div>
                <div class="ad-slide-info">
                    <span class="ad-slide-badge">Hypoallergenic</span>
                    <div class="ad-slide-name">Titanium Earrings</div>
                    <div class="ad-slide-desc">Anting titanium ringan dan tahan karat, ideal untuk kulit sensitif dan pemakaian sehari-hari tanpa iritasi.</div>
                    <div class="ad-slide-sizes">
                        <span class="ad-size-tag">Panjang 6mm</span>
                        <span class="ad-size-tag">Panjang 8mm</span>
                        <span class="ad-size-tag">Panjang 10mm</span>
                        <span class="ad-size-tag">16G / 18G</span>
                    </div>
                    <div class="ad-slide-price">Rp 50.000</div>
                </div>
            </div>

            <div class="ad-slide">
                <div class="ad-slide-img"><img src="../gambar/kyoto-series.jpg" alt="Kyoto Series"></div>
                <div class="ad-slide-info">
                    <span class="ad-slide-badge">Edisi Khusus</span>
                    <div class="ad-slide-name">Kyoto Series</div>
                    <div class="ad-slide-desc">Koleksi bertema Jepang yang minimalis dan modern, menghadirkan nuansa estetika elegan dan unik.</div>
                    <div class="ad-slide-sizes">
                        <span class="ad-size-tag">Diameter 8mm</span>
                        <span class="ad-size-tag">Diameter 10mm</span>
                        <span class="ad-size-tag">14G / 16G</span>
                    </div>
                    <div class="ad-slide-price">Rp 40.000</div>
                </div>
            </div>

            <div class="ad-slide">
                <div class="ad-slide-img"><img src="../gambar/Circular-Barbell.png" alt="Circular Barbell"></div>
                <div class="ad-slide-info">
                    <span class="ad-slide-badge">Populer</span>
                    <div class="ad-slide-name">Circular Barbell</div>
                    <div class="ad-slide-desc">Piercing berbentuk lingkaran dengan bola pada kedua ujungnya, memberikan gaya klasik yang nyaman dipakai.</div>
                    <div class="ad-slide-sizes">
                        <span class="ad-size-tag">Diameter 10mm</span>
                        <span class="ad-size-tag">Diameter 12mm</span>
                        <span class="ad-size-tag">14G / 16G</span>
                    </div>
                    <div class="ad-slide-price">Rp 55.000</div>
                </div>
            </div>

            <div class="ad-slide">
                <div class="ad-slide-img"><img src="../gambar/spkie-ohrring.jpg" alt="Spike Ohrring"></div>
                <div class="ad-slide-info">
                    <span class="ad-slide-badge">Eksklusif</span>
                    <div class="ad-slide-name">Spike Ohrring</div>
                    <div class="ad-slide-desc">Desain spike tajam dan elegan, cocok untuk septum, telinga, atau berbagai pilihan piercing lainnya.</div>
                    <div class="ad-slide-sizes">
                        <span class="ad-size-tag">Panjang 8mm</span>
                        <span class="ad-size-tag">Panjang 10mm</span>
                        <span class="ad-size-tag">14G</span>
                    </div>
                    <div class="ad-slide-price">Rp 55.000</div>
                </div>
            </div>

            <!-- Alternatif Piercing -->
            <div class="ad-slide">
                <div class="ad-slide-img"><img src="../gambar/Contoh-Alis.png" alt="Piercing Alis"></div>
                <div class="ad-slide-info">
                    <span class="ad-slide-badge" style="background:#a54ccf;color:#fff;">Alternatif</span>
                    <div class="ad-slide-name">Piercing Alis</div>
                    <div class="ad-slide-desc">Piercing alis dengan bentuk rapi dan posisi estetis, cocok untuk memberi aksen wajah yang tajam dan modern.</div>
                    <div class="ad-slide-sizes">
                        <span class="ad-size-tag">16G / 18G</span>
                        <span class="ad-size-tag">Panjang 8mm</span>
                        <span class="ad-size-tag">Panjang 10mm</span>
                    </div>
                    <div class="ad-slide-price">Jasa Tindik</div>
                </div>
            </div>

            <div class="ad-slide">
                <div class="ad-slide-img"><img src="../gambar/Contoh-Bibir.png" alt="Piercing Bibir"></div>
                <div class="ad-slide-info">
                    <span class="ad-slide-badge" style="background:#a54ccf;color:#fff;">Alternatif</span>
                    <div class="ad-slide-name">Piercing Bibir</div>
                    <div class="ad-slide-desc">Piercing bibir yang stylish untuk model labret atau monroe, dengan teknik steril dan hasil simetris.</div>
                    <div class="ad-slide-sizes">
                        <span class="ad-size-tag">16G / 18G</span>
                        <span class="ad-size-tag">Labret</span>
                        <span class="ad-size-tag">Monroe</span>
                    </div>
                    <div class="ad-slide-price">Jasa Tindik</div>
                </div>
            </div>

            <div class="ad-slide">
                <div class="ad-slide-img"><img src="../gambar/Contoh-Hidung.png" alt="Piercing Hidung"></div>
                <div class="ad-slide-info">
                    <span class="ad-slide-badge" style="background:#a54ccf;color:#fff;">Alternatif</span>
                    <div class="ad-slide-name">Piercing Hidung</div>
                    <div class="ad-slide-desc">Piercing hidung untuk model nose stud atau hoop, menggunakan bahan steril agar hasilnya rapi dan nyaman dipakai.</div>
                    <div class="ad-slide-sizes">
                        <span class="ad-size-tag">20G / 18G</span>
                        <span class="ad-size-tag">Nose Stud</span>
                        <span class="ad-size-tag">Hoop</span>
                    </div>
                    <div class="ad-slide-price">Jasa Tindik</div>
                </div>
            </div>

            <div class="ad-slide">
                <div class="ad-slide-img"><img src="../gambar/Contoh-Telinga.jpeg" alt="Piercing Telinga"></div>
                <div class="ad-slide-info">
                    <span class="ad-slide-badge" style="background:#a54ccf;color:#fff;">Alternatif</span>
                    <div class="ad-slide-name">Piercing Telinga</div>
                    <div class="ad-slide-desc">Piercing telinga profesional untuk berbagai model seperti helix, tragus, dan daith, dengan hasil aman dan estetis.</div>
                    <div class="ad-slide-sizes">
                        <span class="ad-size-tag">16G / 18G</span>
                        <span class="ad-size-tag">Helix</span>
                        <span class="ad-size-tag">Tragus</span>
                        <span class="ad-size-tag">Daith</span>
                    </div>
                    <div class="ad-slide-price">Jasa Tindik</div>
                </div>
            </div>

            <div class="ad-slide">
                <div class="ad-slide-img"><img src="../gambar/Contoh-Lidah.png" alt="Piercing Lidah"></div>
                <div class="ad-slide-info">
                    <span class="ad-slide-badge" style="background:#a54ccf;color:#fff;">Alternatif</span>
                    <div class="ad-slide-name">Piercing Lidah</div>
                    <div class="ad-slide-desc">Piercing lidah aman dan nyaman, direkomendasikan untuk gaya yang berani dengan perawatan aftercare yang lengkap.</div>
                    <div class="ad-slide-sizes">
                        <span class="ad-size-tag">14G</span>
                        <span class="ad-size-tag">Barbell 16mm</span>
                        <span class="ad-size-tag">Titanium</span>
                    </div>
                    <div class="ad-slide-price">Jasa Tindik</div>
                </div>
            </div>

            <div class="ad-dots" id="adDots"></div>
        </div>

        <section class="features">
            <a class="feature-item" href="piercing_produk.php">
                <div class="feature-icon"><i class="fa-solid fa-gem"></i></div>
                <span>Piercing Produk</span>
            </a>
            <a class="feature-item" href="jasa_piercing.php">
                <div class="feature-icon"><i class="fa-regular fa-gem"></i></div>
                <span>Jasa Piercing</span>
            </a>
            <a class="feature-item" href="alternatif_piercing.php">
                <div class="feature-icon"><i class="fa-solid fa-ring"></i></div>
                <span>Katalog Piercing</span>
            </a>
            <a class="feature-item" href="piercing_disukai.php">
                <div class="feature-icon"><i class="fa-regular fa-heart"></i></div>
                <span>Piercing Yang Disukai</span>
            </a>
        </section>

        <section class="product-images">
            <div class="product-item" data-product="Cubic Zirconia" data-desc="Desain klasik dengan batu cubic zirconia yang berkilau, cocok untuk tampilan elegan pada berbagai jenis piercing." data-price="Rp 30.000" data-stock="12">
                <button class="product-love-btn" data-product="Cubic Zirconia"><i class="fa-regular fa-heart"></i></button>
                <img src="../gambar/cubic-zironia.jpg" alt="Product 1">
                <span>Cubic Zirconia</span>
            </div>
            <div class="product-item" data-product="Titanium Earrings" data-desc="Anting titanium ringan dan tahan karat, ideal untuk kulit sensitif dan pemakaian sehari-hari." data-price="Rp 50.000" data-stock="8">
                <button class="product-love-btn" data-product="Titanium Earrings"><i class="fa-regular fa-heart"></i></button>
                <img src="../gambar/titanium-earrings.jpg" alt="Product 2">
                <span>Titanium Earrings</span>
            </div>
            <div class="product-item" data-product="Spike Ohrring" data-desc="Piercing dengan desain spike yang tajam dan elegan. Cocok untuk septum, telinga, atau alternatif piercing lainnya." data-price="Rp 55.000" data-stock="10">
                <button class="product-love-btn" data-product="Spike Ohrring"><i class="fa-regular fa-heart"></i></button>
                <img src="../gambar/spkie-ohrring.jpg" alt="Product 3">
                <span>Spike Ohrring</span>
            </div>
            <div class="product-item" data-product="Kyoto Series" data-desc="Koleksi piercing bertema Jepang yang minimalis dan modern, memberikan nuansa estetika elegan." data-price="Rp 40.000" data-stock="7">
                <button class="product-love-btn" data-product="Kyoto Series"><i class="fa-regular fa-heart"></i></button>
                <img src="../gambar/kyoto-series.jpg" alt="Product 4">
                <span>Kyoto Series</span>
            </div>
            <div class="product-item" data-product="Ear Piercing Ball" data-desc="Piercing bentuk bola yang halus, cocok untuk berbagai variasi anting dan piercing telinga." data-price="Rp 45.000" data-stock="15">
                <button class="product-love-btn" data-product="Ear Piercing Ball"><i class="fa-regular fa-heart"></i></button>
                <img src="../gambar/ear-piercing-ball.jpg" alt="Product 5">
                <span>Ear Piercing Ball</span>
            </div>
            <div class="product-item" data-product="Barbell Earrings" data-desc="Anting barbell dengan desain kuat dan trendi untuk gaya piercing yang berani." data-price="Rp 35.000" data-stock="9">
                <button class="product-love-btn" data-product="Barbell Earrings"><i class="fa-regular fa-heart"></i></button>
                <img src="../gambar/barbell-earrings.jpg" alt="Product 6">
                <span>Barbell Earrings</span>
            </div>
            <div class="product-item" data-product="Tindik Bunga" data-desc="Piercing motif bunga yang feminin dan manis, cocok untuk tampilan lembut." data-price="Rp 50.000" data-stock="6">
                <button class="product-love-btn" data-product="Tindik Bunga"><i class="fa-regular fa-heart"></i></button>
                <img src="../gambar/Tindik-Bunga.png" alt="Product 7">
                <span>Tindik Bunga</span>
            </div>
            <div class="product-item" data-product="Anting Jepit" data-desc="Anting jepit praktis tanpa tindik, ideal untuk yang ingin tampil stylish tanpa komitmen permanen." data-price="Rp 35.000" data-stock="18">
                <button class="product-love-btn" data-product="Anting Jepit"><i class="fa-regular fa-heart"></i></button>
                <img src="../gambar/Anting-Jepit.png" alt="Product 8">
                <span>Anting Jepit</span>
            </div>
            <div class="product-item" data-product="Barre de Surface" data-desc="Piercing surface bar yang modern dan minimalis untuk area dada atau punggung." data-price="Rp 30.000" data-stock="5">
                <button class="product-love-btn" data-product="Barre de Surface"><i class="fa-regular fa-heart"></i></button>
                <img src="../gambar/Barre-de-surface.png" alt="Product 9">
                <span>Barre de Surface</span>
            </div>
            <div class="product-item" data-product="Circular Barbell" data-desc="Piercing berbentuk lingkaran dengan bola pada kedua ujungnya untuk gaya klasik yang nyaman." data-price="Rp 55.000" data-stock="11">
                <button class="product-love-btn" data-product="Circular Barbell"><i class="fa-regular fa-heart"></i></button>
                <img src="../gambar/Circular-Barbell.png" alt="Product 10">
                <span>Circular Barbell</span>
            </div>
            <div class="product-item" data-product="Dparis Model Bintang" data-desc="Desain unik dengan motif bintang yang menawan, cocok untuk tampilan berani dan ekspresif." data-price="Rp 35.000" data-stock="4">
                <button class="product-love-btn" data-product="Dparis Model Bintang"><i class="fa-regular fa-heart"></i></button>
                <img src="../gambar/Dparis-Model-Bintang.png" alt="Product 11">
                <span>Dparis Model Bintang</span>
            </div>
            <div class="product-item" data-product="Titanium Straight Barbel" data-desc="Barbel lurus titanium hypoallergenic, sempurna untuk piercing bridge atau septum." data-price="Rp 40.000" data-stock="8">
                <button class="product-love-btn" data-product="Titanium Straight Barbel"><i class="fa-regular fa-heart"></i></button>
                <img src="../gambar/Titanium-Straight-Barbel.png" alt="Product 12">
                <span>Titanium Straight Barbel</span>
            </div>
            <div class="product-item" data-product="Piercing Nostril Em Aço" data-desc="Piercing nostril berkualitas steel dengan desain geometri dan finish premium." data-price="Rp 35.000" data-stock="7">
                <button class="product-love-btn" data-product="Piercing Nostril Em Aço"><i class="fa-regular fa-heart"></i></button>
                <img src="../gambar/Piercing-Nostril-Em-Aço.png" alt="Product 13">
                <span>Piercing Nostril Em Aço</span>
            </div>
        </section>

        <!-- <div class="product-modal" id="productModal">
            <div class="product-modal-card">
                <button class="modal-close" id="closeModal"><i class="fa-solid fa-xmark"></i></button>
                <div class="product-modal-image">
                    <img id="modalProductImage" src="" alt="Detail Produk">
                </div>
                <h2 id="modalProductName"></h2>
                <p id="modalProductDesc"></p>
                <div class="product-modal-meta">
                    <div>
                        <span>Harga</span>
                        <strong id="modalProductPrice"></strong>
                    </div>
                    <div>
                        <span>Stock Tersedia</span>
                        <strong id="modalProductStock"></strong>
                    </div>
                </div>
                <a id="buyButton" class="btn-buy" href="#">Beli</a>
            </div>
        </div> -->

        <div class="product-modal" id="productModal">
            <div class="product-modal-card">
                <button class="modal-close" id="closeModal"><i class="fa-solid fa-xmark"></i></button>
                <div class="product-modal-image">
                    <img id="modalProductImage" src="" alt="Detail Produk">
                </div>
                <h2 id="modalProductName"></h2>
                <p id="modalProductDesc"></p>
                <div class="product-modal-meta">
                    <div>
                        <span>Harga</span>
                        <strong id="modalProductPrice"></strong>
                    </div>
                    <div>
                        <span>Stock Tersedia</span>
                        <strong id="modalProductStock"></strong>
                    </div>
                </div>
                
                <!-- <div class="modal-actions">
                    <button id="cartButton" class="btn-cart">
                        <i class="fa-solid fa-cart-plus"></i> Keranjang
                    </button>
                    <a id="buyButton" class="btn-buy" href="#">Beli Sekarang</a>
                </div> -->
                
                <div class="modal-actions">
                    <div id="cartQtyControl" class="cart-qty-control">
                        <button id="cartMinus" class="cart-qty-btn"><i class="fa-solid fa-minus"></i></button>
                        <span id="cartQtyValue" class="cart-qty-value">1</span>
                        <button id="cartPlus" class="cart-qty-btn"><i class="fa-solid fa-plus"></i></button>
                    </div>
                    <div class="modal-btn-row">
                        <button id="cartButton" class="btn-cart">
                            <i class="fa-solid fa-cart-plus"></i>
                        </button>
                        <a id="buyButton" class="btn-buy" href="#">Beli Sekarang</a>
                    </div>
                </div>

            </div>
        </div>
        
        <div class="checkout-modal" id="checkoutModal">
            <div class="checkout-modal-card">
                <button class="modal-close" id="closeCheckoutBtn"><i class="fa-solid fa-xmark"></i></button>
                <iframe id="checkoutIframe" src="" title="Checkout Produk"></iframe>
            </div>
        </div>

    </main>

<script>
    // --- GUEST MODAL ---
    function showGuestModal() {
        document.getElementById('guestLoginModal').classList.add('active');
        // tutup modal produk jika terbuka
        productModal.classList.remove('active');
    }
    document.getElementById('guestModalClose').addEventListener('click', function() {
        document.getElementById('guestLoginModal').classList.remove('active');
    });
    document.getElementById('guestLoginModal').addEventListener('click', function(e) {
        if (e.target === this) this.classList.remove('active');
    });

    // --- PENCARIAN PRODUK ---
    const searchInput = document.getElementById('searchInput');
    const searchClear = document.getElementById('searchClear');
    const searchLabel = document.getElementById('searchLabel');
    const searchCountEl = document.getElementById('searchCount');
    const searchQueryEl = document.getElementById('searchQuery');
    const allProductItems = document.querySelectorAll('.product-item');
    const adSlideshow = document.getElementById('adSlideshow');
    const isLoggedIn = window.sphinxAuth ? window.sphinxAuth.isLoggedIn : <?= $is_logged_in ? 'true' : 'false' ?>;

    searchInput.addEventListener('input', function () {
        const q = this.value.trim().toLowerCase();
        searchClear.style.display = q ? 'block' : 'none';

        if (q === '') {
            allProductItems.forEach(item => item.style.display = '');
            adSlideshow.style.display = '';
            searchLabel.style.display = 'none';
            return;
        }

        // Sembunyikan slideshow saat pengguna sedang mencari
        adSlideshow.style.display = 'none';

        let count = 0;
        allProductItems.forEach(item => {
            const name = (item.dataset.product || '').toLowerCase();
            const desc = (item.dataset.desc || '').toLowerCase();
            const match = name.includes(q) || desc.includes(q);
            item.style.display = match ? '' : 'none';
            if (match) count++;
        });

        searchCountEl.textContent = count;
        searchQueryEl.textContent = this.value.trim();
        searchLabel.style.display = 'block';
    });

    searchClear.addEventListener('click', function () {
        searchInput.value = '';
        searchInput.dispatchEvent(new Event('input'));
        searchInput.focus();
    });

    const favoriteKey = 'piercing_favorites';

    const cartKey = 'sphinx_cart';

    // Elemen-elemen di halaman
    const cartCountEl = document.getElementById('cartCount');
    const productModal = document.getElementById('productModal');
    const productNameEl = document.getElementById('modalProductName');
    const productDescEl = document.getElementById('modalProductDesc');
    const productPriceEl = document.getElementById('modalProductPrice');
    const productStockEl = document.getElementById('modalProductStock');
    const productImageEl = document.getElementById('modalProductImage');
    
    // Tombol-tombol di dalam modal
    const buyButton = document.getElementById('buyButton');
    const cartButton = document.getElementById('cartButton');
    const cartQtyControl = document.getElementById('cartQtyControl');
    const cartMinus = document.getElementById('cartMinus');
    const cartPlus = document.getElementById('cartPlus');
    const cartQtyValue = document.getElementById('cartQtyValue');
    
    const closeModalBtn = document.getElementById('closeModal');
    const checkoutModal = document.getElementById('checkoutModal');
    const checkoutIframe = document.getElementById('checkoutIframe');
    const closeCheckoutBtn = document.getElementById('closeCheckoutBtn');

    let currentSelectedProduct = null;

    // Muat data produk favorit
    function loadFavorites() {
        try {
            var favs = JSON.parse(localStorage.getItem(favoriteKey) || '[]');
            favs.forEach(function(fav) {
                var productName = typeof fav === 'string' ? fav : fav.name;
                var btn = document.querySelector('.product-love-btn[data-product="' + productName + '"]');
                if (btn) {
                    btn.classList.add('active');
                    btn.innerHTML = '<i class="fa-solid fa-heart"></i>';
                }
            });
        } catch(e) {}
    }

    // Perbarui angka keranjang di topbar
    function loadCartCount() {
        if (!cartCountEl) return;
        try {
            var cart = JSON.parse(localStorage.getItem(cartKey) || '[]');
            var totalItems = cart.reduce(function(sum, item) {
                return sum + (parseInt(item.qty || item.quantity || 1, 10) || 1);
            }, 0);
            cartCountEl.innerText = totalItems;
        } catch(e) { cartCountEl.innerText = 0; }
    }

    // Cek apakah produk ini sudah ada di keranjang
    function updateCartButtonState(productName) {
        cartQtyValue.innerText = 1;
    }

    // Tangani klik tombol favorit (hati)
    document.querySelectorAll('.product-love-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            const productName = this.dataset.product;
            const productItem = this.closest('.product-item');
            const img = productItem ? productItem.querySelector('img') : null;
            const price = productItem ? (productItem.dataset.price || '') : '';
            let favorites = JSON.parse(localStorage.getItem(favoriteKey) || '[]');

            if (this.classList.contains('active')) {
                favorites = favorites.filter(f => (typeof f === 'string' ? f : f.name) !== productName);
                this.classList.remove('active');
                this.innerHTML = '<i class="fa-regular fa-heart"></i>';
            } else {
                if (!favorites.find(f => (typeof f === 'string' ? f : f.name) === productName)) {
                    favorites.push({ name: productName, image: img ? img.src : '', price: price });
                }
                this.classList.add('active');
                this.innerHTML = '<i class="fa-solid fa-heart"></i>';
            }
            localStorage.setItem(favoriteKey, JSON.stringify(favorites));
        });
    });

    // Buka modal detail produk
    document.querySelectorAll('.product-item').forEach(item => {
        item.addEventListener('click', function () {
            const name = this.dataset.product || '';
            const desc = this.dataset.desc || 'Deskripsi tidak tersedia.';
            const price = this.dataset.price || '-';
            const stock = this.dataset.stock || '-';
            const image = this.querySelector('img');

            currentSelectedProduct = { name, price, image: image ? image.src : '' };

            productNameEl.innerText = name;
            productDescEl.innerText = desc;
            productPriceEl.innerText = price;
            productStockEl.innerText = stock;
            productImageEl.src = image ? image.src : '';
            productImageEl.alt = image ? image.alt : name;
            // Arahkan tombol beli ke halaman checkout produk ini
            if (buyButton) {
                const encodedProduct = encodeURIComponent(name || '');
                const encodedPrice = encodeURIComponent(price || '');
                buyButton.href = `beli.php?product=${encodedProduct}&price=${encodedPrice}`;
            }
            // Sesuaikan tampilan tombol keranjang berdasarkan data produk
            updateCartButtonState(name);

            productModal.classList.add('active');
        });
    });

    // KLIK TOMBOL TAMBAH KE KERANJANG
    cartButton.addEventListener('click', function() {
        if (!isLoggedIn) {
            showGuestModal(); return;
        }
        if (!currentSelectedProduct) return;
        var qtyToAdd = parseInt(cartQtyValue.innerText) || 1;
        if (window.sphinxCart) {
            sphinxCart.addItem({
                name: currentSelectedProduct.name,
                price: currentSelectedProduct.price,
                image: currentSelectedProduct.image,
                qty: qtyToAdd
            });
        } else {
            try {
                var cart = JSON.parse(localStorage.getItem('sphinx_cart') || '[]');
                var existing = cart.find(function(i) { return i.name === currentSelectedProduct.name; });
                if (existing) existing.qty += qtyToAdd;
                else cart.push({ name: currentSelectedProduct.name, price: currentSelectedProduct.price, image: currentSelectedProduct.image, qty: qtyToAdd });
                localStorage.setItem('sphinx_cart', JSON.stringify(cart));
            } catch(e) {}
        }
        loadCartCount();
        cartQtyValue.innerText = 1;
        productModal.classList.remove('active');
    });

    // KLIK TOMBOL TAMBAH JUMLAH (+)
    cartPlus.addEventListener('click', function() {
        let qty = parseInt(cartQtyValue.innerText) || 1;
        cartQtyValue.innerText = qty + 1;
    });

    // KLIK TOMBOL KURANGI JUMLAH (-)
    cartMinus.addEventListener('click', function() {
        let qty = parseInt(cartQtyValue.innerText) || 1;
        if (qty > 1) cartQtyValue.innerText = qty - 1;
    });

    // Tangani klik Beli Sekarang
    buyButton.addEventListener('click', function (event) {
        event.preventDefault();
        if (!isLoggedIn) { showGuestModal(); return; }
        if (!this.href || this.href === '#') return;
        const qty = parseInt(cartQtyValue.innerText) || 1;
        const baseHref = this.href.split('&qty=')[0];
        productModal.classList.remove('active');
        // Arahkan ke beli.php dengan jumlah yang dipilih
        window.location.href = baseHref + '&qty=' + qty;
    });

    // Tutup modal saat tombol close diklik
    closeModalBtn.addEventListener('click', () => { productModal.classList.remove('active'); });
    productModal.addEventListener('click', (event) => {
        if (event.target === productModal) productModal.classList.remove('active');
    });
    closeCheckoutBtn.addEventListener('click', () => {
        checkoutModal.classList.remove('active'); checkoutIframe.src = '';
    });
    checkoutModal.addEventListener('click', (event) => {
        if (event.target === checkoutModal) {
            checkoutModal.classList.remove('active'); checkoutIframe.src = '';
        }
    });

    // Muat semua data pertama kali halaman dibuka
    window.addEventListener('DOMContentLoaded', () => {
        loadFavorites();
        loadCartCount();

        // --- SLIDESHOW IKLAN ---
        (function() {
            var slides = document.querySelectorAll('#adSlideshow .ad-slide');
            var dotsContainer = document.getElementById('adDots');
            if (!slides.length || !dotsContainer) return;
            var currentSlide = 0;

            slides.forEach(function(_, i) {
                var dot = document.createElement('span');
                dot.className = 'ad-dot' + (i === 0 ? ' active' : '');
                dot.addEventListener('click', function() { goToSlide(i); });
                dotsContainer.appendChild(dot);
            });

            function goToSlide(index) {
                if (!slides[currentSlide] || !dotsContainer.children[currentSlide]) return;
                slides[currentSlide].classList.remove('active');
                dotsContainer.children[currentSlide].classList.remove('active');
                currentSlide = (index + slides.length) % slides.length;
                if (!slides[currentSlide] || !dotsContainer.children[currentSlide]) return;
                slides[currentSlide].classList.add('active');
                dotsContainer.children[currentSlide].classList.add('active');
            }

            setInterval(function() { goToSlide(currentSlide + 1); }, 3000);
        })();
    });
</script>

<!-- Customer Service Floating Button -->
<div class="cs-wrap">
    <div class="cs-popup" id="csPopup">
        <div class="cs-popup-header">
            <i class="fa-solid fa-headset"></i>
            <span>Customer Service</span>
        </div>
        <a href="https://wa.me/6281994799058" class="cs-item" target="_blank">
            <div class="cs-item-icon cs-wa"><i class="fa-brands fa-whatsapp"></i></div>
            <div class="cs-item-info">
                <strong>WhatsApp</strong>
                <span>081994799058</span>
            </div>
        </a>
        <a href="https://www.instagram.com/sphinx_piercingjogja" class="cs-item" target="_blank">
            <div class="cs-item-icon cs-ig"><i class="fa-brands fa-instagram"></i></div>
            <div class="cs-item-info">
                <strong>Instagram</strong>
                <span>@sphinx_piercingjogja</span>
            </div>
        </a>
    </div>
    <button class="cs-btn" id="csBtn" title="Customer Service">
        <i class="fa-solid fa-headset"></i>
    </button>
</div>

<script>
    // CS Popup toggle
    document.getElementById('csBtn').addEventListener('click', function(e) {
        e.stopPropagation();
        document.getElementById('csPopup').classList.toggle('active');
    });
    document.addEventListener('click', function() {
        document.getElementById('csPopup').classList.remove('active');
    });
    document.getElementById('csPopup').addEventListener('click', function(e) {
        e.stopPropagation();
    });
</script>

<!-- Guest Login Modal -->
<div class="modal-overlay" id="guestLoginModal" style="z-index:9999;">
    <div style="background:#2a1450;border:1px solid rgba(127,255,0,0.25);border-radius:18px;padding:36px 32px;max-width:360px;width:90%;text-align:center;position:relative;">
        <button id="guestModalClose" style="position:absolute;top:14px;right:16px;background:none;border:none;color:#aaa;font-size:18px;cursor:pointer;"><i class="fa-solid fa-xmark"></i></button>
        <div style="width:60px;height:60px;background:rgba(127,255,0,0.1);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
            <i class="fa-solid fa-lock" style="font-size:24px;color:#7FFF00;"></i>
        </div>
        <h3 style="color:#fff;font-size:17px;font-weight:700;margin-bottom:8px;">Fitur ini membutuhkan login</h3>
        <p style="color:#aaa;font-size:13px;line-height:1.6;margin-bottom:24px;">
            Kamu sedang browsing sebagai tamu.<br>Login atau buat akun untuk mulai checkout dan menyimpan pesanan.
        </p>
        <div style="display:flex;flex-direction:column;gap:10px;">
            <a href="Login.php" style="background:#7FFF00;color:#111;padding:12px;border-radius:999px;font-weight:700;font-size:14px;text-decoration:none;display:flex;align-items:center;justify-content:center;gap:8px;">
                <i class="fa-solid fa-right-to-bracket"></i> Login Sekarang
            </a>
            <a href="BuatAkun.php" style="background:transparent;color:#7FFF00;padding:11px;border-radius:999px;font-weight:600;font-size:14px;text-decoration:none;display:flex;align-items:center;justify-content:center;gap:8px;border:1px solid #7FFF00;">
                <i class="fa-solid fa-user-plus"></i> Buat Akun Gratis
            </a>
            <button onclick="document.getElementById('guestLoginModal').classList.remove('active')" style="background:none;border:none;color:#666;font-size:12px;cursor:pointer;padding:6px;font-family:inherit;">
                Lanjutkan sebagai Tamu
            </button>
        </div>
    </div>
</div>

</body>
</html>
