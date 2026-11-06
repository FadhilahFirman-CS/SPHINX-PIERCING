<?php
$current_page = basename($_SERVER['PHP_SELF']);
$page_title = "Alternatif Piercing";
$page_heading = "Alternatif Piercing";
$page_description = "Temukan pilihan piercing alternatif yang unik dan menarik, cocok untuk gaya berbeda.";
$page_css = '../css-customer/piercing_produk.css';
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
            <h1>PRODUK PIERCING </h1>
            <p>Temukan pilihan piercing alternatif yang unik dan menarik, cocok untuk gaya berbeda.</p>
            
            <div class="products-grid">
        <div class="product-card" data-product="Spike Ohrring" data-price="Rp 55.000" data-stock="10" data-desc="Piercing dengan desain spike yang tajam dan elegan. Cocok untuk septum, telinga, atau alternatif piercing lainnya dengan tampilan aggressive yang stylish.">
                    <img src="../gambar/spkie-ohrring.jpg" alt="Spike Ohrring" class="product-image">
                    <div class="product-info">
                        <h3 class="product-name">Spike Ohrring</h3>
                        <p class="product-desc">Piercing dengan desain spike yang tajam dan elegan. Cocok untuk septum, telinga, atau alternatif piercing lainnya dengan tampilan aggressive yang stylish.</p>
                        <div class="card-actions"><button class="btn-card-cart"><i class="fa-solid fa-cart-plus"></i></button></div>
                    </div>
                </div>

                <div class="product-card" data-product="Circular Barbell" data-price="Rp 45.000" data-stock="8" data-desc="Piercing berbentuk lingkaran dengan bola pada kedua ujungnya. Desain klasik namun sophisticated, sangat populer untuk body piercing dan septum.">
                    <img src="../gambar/Circular-Barbell.png" alt="Circular Barbell" class="product-image">
                    <div class="product-info">
                        <h3 class="product-name">Circular Barbell</h3>
                        <p class="product-desc">Piercing berbentuk lingkaran dengan bola pada kedua ujungnya. Desain klasik namun sophisticated, sangat populer untuk body piercing dan septum.</p>
                        <div class="card-actions"><button class="btn-card-cart"><i class="fa-solid fa-cart-plus"></i></button></div>
                    </div>
                </div>

                <div class="product-card" data-product="Barre de Surface" data-price="Rp 60.000" data-stock="6" data-desc="Piercing surface bar yang modern dan minimalis. Ideal untuk surface piercing di area dada, punggung, atau bagian tubuh lainnya dengan efek visual unik.">
                    <img src="../gambar/Barre-de-surface.png" alt="Barre de Surface" class="product-image">
                    <div class="product-info">
                        <h3 class="product-name">Barre de Surface</h3>
                        <p class="product-desc">Piercing surface bar yang modern dan minimalis. Ideal untuk surface piercing di area dada, punggung, atau bagian tubuh lainnya dengan efek visual unik.</p>
                        <div class="card-actions"><button class="btn-card-cart"><i class="fa-solid fa-cart-plus"></i></button></div>
                    </div>
                </div>

                <div class="product-card" data-product="Titanium Straight Barbel" data-price="Rp 75.000" data-stock="5" data-desc="Barbel lurus berkualitas tinggi dari titanium. Hypoallergenic dan aman untuk semua jenis kulit, sempurna untuk piercing bridge atau septa dengan kenyamanan maksimal.">
                    <img src="../gambar/Titanium-Straight-Barbel.png" alt="Titanium Straight Barbel" class="product-image">
                    <div class="product-info">
                        <h3 class="product-name">Titanium Straight Barbel</h3>
                        <p class="product-desc">Barbel lurus berkualitas tinggi dari titanium. Hypoallergenic dan aman untuk semua jenis kulit, sempurna untuk piercing bridge atau septa dengan kenyamanan maksimal.</p>
                        <div class="card-actions"><button class="btn-card-cart"><i class="fa-solid fa-cart-plus"></i></button></div>
                    </div>
                </div>

                <div class="product-card" data-product="Dparis Model Bintang" data-price="Rp 50.000" data-stock="12" data-desc="Desain unik dengan motif bintang yang menawan. Memberikan statement yang berani dan crafty untuk mereka yang ingin tampil beda dan ekspresif.">
                    <img src="../gambar/Dparis-Model-Bintang.png" alt="Dparis Model Bintang" class="product-image">
                    <div class="product-info">
                        <h3 class="product-name">Dparis Model Bintang</h3>
                        <p class="product-desc">Desain unik dengan motif bintang yang menawan. Memberikan statement yang berani dan crafty untuk mereka yang ingin tampil beda dan ekspresif.</p>
                        <div class="card-actions"><button class="btn-card-cart"><i class="fa-solid fa-cart-plus"></i></button></div>
                    </div>
                </div>

                <div class="product-card" data-product="Piercing Nostril Em Aço" data-price="Rp 40.000" data-stock="15" data-desc="Piercing nostril berkualitas dengan material steel yang tahan lama. Sempurna untuk septum piercing dengan desain geometri dan finish yang premium.">
                    <img src="../gambar/Piercing-Nostril-Em-Aço.png" alt="Piercing Nostril Em Aço" class="product-image">
                    <div class="product-info">
                        <h3 class="product-name">Piercing Nostril Em Aço</h3>
                        <p class="product-desc">Piercing nostril berkualitas dengan material steel yang tahan lama. Sempurna untuk septum piercing dengan desain geometri dan finish yang premium.</p>
                        <div class="card-actions"><button class="btn-card-cart"><i class="fa-solid fa-cart-plus"></i></button></div>
                    </div>
                </div>

                <div class="product-card" data-product="Cubic Zirconia" data-price="Rp 85.000" data-stock="7" data-desc="Anting piercing dengan sentuhan kristal cubic zirconia, memberikan kilau mewah tanpa mengorbankan kenyamanan.">
                    <img src="../gambar/cubic-zironia.jpg" alt="Cubic Zirconia" class="product-image">
                    <div class="product-info">
                        <h3 class="product-name">Cubic Zirconia</h3>
                        <p class="product-desc">Anting piercing dengan sentuhan kristal cubic zirconia, memberikan kilau mewah tanpa mengorbankan kenyamanan.</p>
                        <div class="card-actions"><button class="btn-card-cart"><i class="fa-solid fa-cart-plus"></i></button></div>
                    </div>
                </div>

                <div class="product-card" data-product="Kyoto Series" data-price="Rp 70.000" data-stock="4" data-desc="Piercing koleksi Kyoto dengan bentuk elegan dan warna lembut, cocok untuk tampilan modern yang minimalis.">
                    <img src="../gambar/kyoto-series.jpg" alt="Kyoto Series" class="product-image">
                    <div class="product-info">
                        <h3 class="product-name">Kyoto Series</h3>
                        <p class="product-desc">Piercing koleksi Kyoto dengan bentuk elegan dan warna lembut, cocok untuk tampilan modern yang minimalis.</p>
                        <div class="card-actions"><button class="btn-card-cart"><i class="fa-solid fa-cart-plus"></i></button></div>
                    </div>
                </div>

                <div class="product-card" data-product="Ear Piercing Ball" data-price="Rp 35.000" data-stock="20" data-desc="Piercing model bola sederhana yang nyaman dipakai sehari-hari, ideal untuk tampilan ringkas dan modern.">
                    <img src="../gambar/ear-piercing-ball.jpg" alt="Ear Piercing Ball" class="product-image">
                    <div class="product-info">
                        <h3 class="product-name">Ear Piercing Ball</h3>
                        <p class="product-desc">Piercing model bola sederhana yang nyaman dipakai sehari-hari, ideal untuk tampilan ringkas dan modern.</p>
                        <div class="card-actions"><button class="btn-card-cart"><i class="fa-solid fa-cart-plus"></i></button></div>
                    </div>
                </div>

                <div class="product-card" data-product="Barbell Earrings" data-price="Rp 45.000" data-stock="9" data-desc="Desain barbel yang kuat dan stylish, sangat cocok untuk tampilan bold pada piercing telinga atau monroe.">
                    <img src="../gambar/barbell-earrings.jpg" alt="Barbell Earrings" class="product-image">
                    <div class="product-info">
                        <h3 class="product-name">Barbell Earrings</h3>
                        <p class="product-desc">Desain barbel yang kuat dan stylish, sangat cocok untuk tampilan bold pada piercing telinga atau monroe.</p>
                        <div class="card-actions"><button class="btn-card-cart"><i class="fa-solid fa-cart-plus"></i></button></div>
                    </div>
                </div>

                <div class="product-card" data-product="Tindik Bunga" data-price="Rp 50.000" data-stock="11" data-desc="Piercing motif bunga yang feminin dan manis, membuat tampilan lebih lembut dengan detail yang menarik.">
                    <img src="../gambar/Tindik-Bunga.png" alt="Tindik Bunga" class="product-image">
                    <div class="product-info">
                        <h3 class="product-name">Tindik Bunga</h3>
                        <p class="product-desc">Piercing motif bunga yang feminin dan manis, membuat tampilan lebih lembut dengan detail yang menarik.</p>
                        <div class="card-actions"><button class="btn-card-cart"><i class="fa-solid fa-cart-plus"></i></button></div>
                    </div>
                </div>

                <div class="product-card" data-product="Anting Jepit" data-price="Rp 30.000" data-stock="18" data-desc="Pilihan anting tanpa tindik untuk gaya piercing sementara dengan kenyamanan tinggi dan desain trendi.">
                    <img src="../gambar/Anting-Jepit.png" alt="Anting Jepit" class="product-image">
                    <div class="product-info">
                        <h3 class="product-name">Anting Jepit</h3>
                        <p class="product-desc">Pilihan anting tanpa tindik untuk gaya piercing sementara dengan kenyamanan tinggi dan desain trendi.</p>
                        <div class="card-actions"><button class="btn-card-cart"><i class="fa-solid fa-cart-plus"></i></button></div>
                    </div>
                </div>
            </div>
            
            <a class="btn-back" href="dashboard.php"><i class="fa-solid fa-arrow-left"></i>Kembali ke Dashboard</a>
        </section>
    </main>

    <!-- Product Modal -->
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
            <div class="modal-actions">
                <div class="cart-qty-control" id="cartQtyControl">
                    <button class="cart-qty-btn" id="cartMinus"><i class="fa-solid fa-minus"></i></button>
                    <span class="cart-qty-value" id="cartQtyValue">1</span>
                    <button class="cart-qty-btn" id="cartPlus"><i class="fa-solid fa-plus"></i></button>
                </div>
                <div style="display:flex; gap:10px; width:100%;">
                    <button class="btn-cart" id="cartButton"><i class="fa-solid fa-cart-plus"></i></button>
                    <a class="btn-buy" id="buyButton" href="#">Beli Sekarang</a>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
    const cartKey = 'sphinx_cart';
    const cartCountEl = document.getElementById('cartCount');
    const productModal = document.getElementById('productModal');
    const closeModal = document.getElementById('closeModal');
    const modalProductImage = document.getElementById('modalProductImage');
    const modalProductName = document.getElementById('modalProductName');
    const modalProductDesc = document.getElementById('modalProductDesc');
    const modalProductPrice = document.getElementById('modalProductPrice');
    const modalProductStock = document.getElementById('modalProductStock');
    const cartButton = document.getElementById('cartButton');
    const buyButton = document.getElementById('buyButton');
    const cartMinus = document.getElementById('cartMinus');
    const cartPlus = document.getElementById('cartPlus');
    const cartQtyValue = document.getElementById('cartQtyValue');

    let currentProduct = null;

    function loadCartCount() {
        const cart = JSON.parse(localStorage.getItem(cartKey) || '[]');
        const total = cart.reduce((sum, item) => sum + (item.qty || 1), 0);
        if (cartCountEl) cartCountEl.innerText = total;
    }

    function openModal(card) {
        const name  = card.dataset.product || '';
        const price = card.dataset.price  || '-';
        const desc  = card.dataset.desc   || '';
        const stock = card.dataset.stock  || '-';
        const img   = card.querySelector('.product-image');

        currentProduct = { name, price, image: img ? img.src : '' };

        modalProductName.innerText  = name;
        modalProductDesc.innerText  = desc;
        modalProductPrice.innerText = price;
        modalProductStock.innerText = stock;
        modalProductImage.src = img ? img.src : '';
        modalProductImage.alt = name;
        cartQtyValue.innerText = 1;

        const encoded = encodeURIComponent(name);
        const encodedPrice = encodeURIComponent(price);
        buyButton.href = `beli.php?product=${encoded}&price=${encodedPrice}`;

        productModal.classList.add('active');
    }

    // Buka modal saat ikon keranjang di kartu diklik
    document.querySelectorAll('.btn-card-cart').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            openModal(this.closest('.product-card'));
        });
    });

    // Buka modal juga saat kartu produk diklik langsung
    document.querySelectorAll('.product-card').forEach(card => {
        card.style.cursor = 'pointer';
        card.addEventListener('click', function(e) {
            if (e.target.closest('.btn-card-cart')) return;
            openModal(this);
        });
    });

    closeModal.addEventListener('click', () => productModal.classList.remove('active'));
    productModal.addEventListener('click', e => { if (e.target === productModal) productModal.classList.remove('active'); });

    cartMinus.addEventListener('click', () => {
        let v = parseInt(cartQtyValue.innerText) || 1;
        if (v > 1) cartQtyValue.innerText = v - 1;
    });
    cartPlus.addEventListener('click', () => {
        cartQtyValue.innerText = (parseInt(cartQtyValue.innerText) || 1) + 1;
    });

    cartButton.addEventListener('click', () => {
        if (!currentProduct) return;
        const qty = parseInt(cartQtyValue.innerText) || 1;
        let cart = JSON.parse(localStorage.getItem(cartKey) || '[]');
        const existing = cart.find(i => i.name === currentProduct.name);
        if (existing) existing.qty += qty;
        else cart.push({ name: currentProduct.name, price: currentProduct.price, image: currentProduct.image, qty });
        localStorage.setItem(cartKey, JSON.stringify(cart));
        loadCartCount();
        productModal.classList.remove('active');
    });

    buyButton.addEventListener('click', function(e) {
        e.preventDefault();
        if (!this.href || this.href === '#') return;
        const qty = parseInt(cartQtyValue.innerText) || 1;
        const base = this.href.split('&qty=')[0];
        productModal.classList.remove('active');
        window.location.href = base + '&qty=' + qty;
    });

    loadCartCount();
</script>

