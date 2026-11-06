<?php
$current_page = basename($_SERVER['PHP_SELF']);
$page_title = "Piercing Yang Disukai";
$page_heading = "Piercing Yang Disukai";
$page_description = "Lihat daftar piercing favorit pengguna dan tren yang sedang populer di kalangan pelanggan.";
$page_css = '../css-customer/piercing_disukai.css?v=' . filemtime(__DIR__ . '/../css-customer/piercing_disukai.css');
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
            
            <div id="favoritesContainer">
                <!-- Favorites will be loaded here by JavaScript -->
            </div>
            
            <a class="btn-back" href="dashboard.php"><i class="fa-solid fa-arrow-left"></i>Kembali ke Dashboard</a>
        </section>
    </main>
</div>

<style>
/* Paksa reload modal - jangan hapus */
.fav-modal {
    position: fixed !important;
    top: 0 !important; left: 0 !important; right: 0 !important; bottom: 0 !important;
    background: rgba(0,0,0,0.78) !important;
    display: none !important;
    align-items: center !important;
    justify-content: center !important;
    padding: 20px !important;
    z-index: 9999 !important;
}
.fav-modal.active {
    display: flex !important;
}
.fav-modal-card {
    background: #14062b !important;
    border-radius: 18px;
    width: min(640px, 100%);
    max-height: 88vh;
    overflow-y: auto;
    padding: 28px;
    position: relative;
    box-shadow: 0 20px 60px rgba(0,0,0,0.5);
    flex-shrink: 0;
}
.fav-modal-img {
    width: 100%;
    height: 260px;
    overflow: hidden;
    border-radius: 14px;
    margin-bottom: 18px;
    background: rgba(255,255,255,0.04);
    display: flex;
    align-items: center;
    justify-content: center;
}
.fav-modal-img img {
    width: 100%;
    height: 100%;
    object-fit: contain;
    display: block;
    border-radius: 14px;
}
.fav-modal-close {
    position: absolute;
    top: 14px; right: 14px;
    width: 36px; height: 36px;
    border: none; border-radius: 50%;
    background: rgba(255,255,255,0.1);
    color: #f4f4f4;
    cursor: pointer;
    display: grid;
    place-items: center;
    font-size: 15px;
}
.fav-modal-close:hover { background: rgba(255,255,255,0.2); }
.fav-modal-card h2 { font-size: 22px; color: #82ff5b; margin-bottom: 10px; }
.fav-modal-card > p { color: #cdcdcd; line-height: 1.7; margin-bottom: 20px; font-size: 14px; }
.fav-modal-meta {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 14px;
    margin-bottom: 0;
}
.fav-modal-meta > div {
    background: rgba(255,255,255,0.05);
    border-radius: 10px;
    padding: 14px;
}
.fav-modal-meta span { display: block; font-size: 11px; color: #cdcdcd; margin-bottom: 4px; }
.fav-modal-meta strong { display: block; color: #f4f4f4; font-size: 15px; }
.fav-modal-actions { display: flex; flex-direction: column; gap: 10px; margin-top: 20px; }
.fav-qty-wrap {
    display: flex;
    align-items: center;
    border: 2px solid #82ff5b;
    border-radius: 10px;
    background: rgba(130,255,91,0.06);
    overflow: hidden;
}
.fav-qty-btn {
    flex: 0 0 44px; height: 44px;
    background: transparent; border: none;
    color: #82ff5b; font-size: 15px;
    cursor: pointer;
    display: flex; align-items: center; justify-content: center;
}
.fav-qty-btn:hover { background: rgba(130,255,91,0.15); }
.fav-qty-val { flex: 1; text-align: center; font-size: 16px; font-weight: 700; color: #82ff5b; }
.fav-btn-row { display: flex; gap: 10px; }
.fav-btn-cart {
    flex: 0 0 50px; height: 50px;
    border: 2px solid #82ff5b; border-radius: 10px;
    background: rgba(0,0,0,0.4); color: #82ff5b;
    font-size: 17px; cursor: pointer;
    display: grid; place-items: center;
}
.fav-btn-cart:hover { background: rgba(130,255,91,0.15); }
.fav-btn-buy {
    flex: 1; height: 50px;
    border: none; border-radius: 10px;
    background: #82ff5b; color: #111;
    font-weight: 700; font-size: 14px;
    cursor: pointer; text-decoration: none;
    display: flex; align-items: center; justify-content: center;
}
.fav-btn-buy:hover { opacity: 0.88; }
</style>

<!-- Modal Detail Produk -->
<div class="fav-modal" id="favModal">
    <div class="fav-modal-card">
        <button class="fav-modal-close" id="favModalClose"><i class="fa-solid fa-xmark"></i></button>
        <div class="fav-modal-img">
            <img id="favModalImg" src="" alt="">
        </div>
        <h2 id="favModalName"></h2>
        <p id="favModalDesc"></p>
        <div class="fav-modal-meta">
            <div>
                <span>Harga</span>
                <strong id="favModalPrice">-</strong>
            </div>
            <div>
                <span>Stock Tersedia</span>
                <strong id="favModalStock">-</strong>
            </div>
        </div>
        <div class="fav-modal-actions">
            <div class="fav-qty-wrap">
                <button class="fav-qty-btn" id="favQtyMinus"><i class="fa-solid fa-minus"></i></button>
                <span class="fav-qty-val" id="favQtyVal">1</span>
                <button class="fav-qty-btn" id="favQtyPlus"><i class="fa-solid fa-plus"></i></button>
            </div>
            <div class="fav-btn-row">
                <button class="fav-btn-cart" id="favBtnCart"><i class="fa-solid fa-cart-shopping"></i></button>
                <a class="fav-btn-buy" id="favBtnBuy" href="#">Beli Sekarang</a>
            </div>
        </div>
    </div>
</div>

<script>
    const favoriteKey = 'piercing_favorites';

    const productImages = {
        'Cubic Zirconia': '../gambar/cubic-zironia.jpg',
        'Titanium Earrings': '../gambar/titanium-earrings.jpg',
        'Spike Ohrring': '../gambar/spkie-ohrring.jpg',
        'Kyoto Series': '../gambar/kyoto-series.jpg',
        'Ear Piercing Ball': '../gambar/ear-piercing-ball.jpg',
        'Barbell Earrings': '../gambar/barbell-earrings.jpg',
        'Tindik Bunga': '../gambar/Tindik-Bunga.png',
        'Anting Jepit': '../gambar/Anting-Jepit.png',
        'Barre de Surface': '../gambar/Barre-de-surface.png',
        'Circular Barbell': '../gambar/Circular-Barbell.png',
        'Dparis Model Bintang': '../gambar/Dparis-Model-Bintang.png',
        'Titanium Straight Barbel': '../gambar/Titanium-Straight-Barbel.png',
        'Piercing Nostril Em Aço': '../gambar/Piercing-Nostril-Em-Aço.png'
    };

    const productDesc = {
        'Cubic Zirconia': 'Desain klasik dengan batu cubic zirconia yang berkilau, cocok untuk tampilan elegan pada berbagai jenis piercing.',
        'Titanium Earrings': 'Anting titanium ringan dan tahan karat, ideal untuk kulit sensitif dan pemakaian sehari-hari.',
        'Spike Ohrring': 'Piercing dengan desain spike yang tajam dan elegan. Cocok untuk septum, telinga, atau alternatif piercing lainnya.',
        'Kyoto Series': 'Koleksi piercing bertema Jepang yang minimalis dan modern, memberikan nuansa estetika elegan.',
        'Ear Piercing Ball': 'Piercing bentuk bola yang halus, cocok untuk berbagai variasi anting dan piercing telinga.',
        'Barbell Earrings': 'Anting barbell dengan desain kuat dan trendi untuk gaya piercing yang berani.',
        'Tindik Bunga': 'Piercing motif bunga yang feminin dan manis, cocok untuk tampilan lembut.',
        'Anting Jepit': 'Anting jepit praktis tanpa tindik, ideal untuk yang ingin tampil stylish tanpa komitmen permanen.',
        'Barre de Surface': 'Piercing surface bar yang modern dan minimalis untuk area dada atau punggung.',
        'Circular Barbell': 'Piercing berbentuk lingkaran dengan bola pada kedua ujungnya untuk gaya klasik yang nyaman.',
        'Dparis Model Bintang': 'Desain unik dengan motif bintang yang menawan, cocok untuk tampilan berani dan ekspresif.',
        'Titanium Straight Barbel': 'Barbel lurus titanium hypoallergenic, sempurna untuk piercing bridge atau septum.',
        'Piercing Nostril Em Aço': 'Piercing nostril berkualitas steel dengan desain geometri dan finish premium.'
    };

    const productStock = {
        'Cubic Zirconia':'12','Titanium Earrings':'8','Spike Ohrring':'10',
        'Kyoto Series':'7','Ear Piercing Ball':'15','Barbell Earrings':'9',
        'Tindik Bunga':'6','Anting Jepit':'18','Barre de Surface':'5',
        'Circular Barbell':'11','Dparis Model Bintang':'4',
        'Titanium Straight Barbel':'8','Piercing Nostril Em Aço':'7'
    };

    function getFavName(fav) { return typeof fav === 'string' ? fav : (fav.name || ''); }
    function getFavImage(fav) {
        if (typeof fav === 'object' && fav.image) return fav.image;
        return productImages[getFavName(fav)] || '';
    }
    function getFavPrice(fav) { return typeof fav === 'object' ? (fav.price || '') : ''; }
    function escapeHtml(str) {
        return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    // ===== MODAL DETAIL PRODUK =====
    const favModal      = document.getElementById('favModal');
    const favModalClose = document.getElementById('favModalClose');
    const favModalImg   = document.getElementById('favModalImg');
    const favModalName  = document.getElementById('favModalName');
    const favModalDesc  = document.getElementById('favModalDesc');
    const favModalPrice = document.getElementById('favModalPrice');
    const favModalStock = document.getElementById('favModalStock');
    const favQtyMinus   = document.getElementById('favQtyMinus');
    const favQtyPlus    = document.getElementById('favQtyPlus');
    const favQtyVal     = document.getElementById('favQtyVal');
    const favBtnCart    = document.getElementById('favBtnCart');
    const favBtnBuy     = document.getElementById('favBtnBuy');

    let currentFav = null;

    function openFavModal(fav) {
        currentFav = fav;
        const name  = getFavName(fav);
        const price = getFavPrice(fav) || '-';
        favModalImg.src           = getFavImage(fav) || '';
        favModalImg.alt           = name;
        favModalName.textContent  = name;
        favModalDesc.textContent  = productDesc[name] || 'Deskripsi tidak tersedia.';
        favModalPrice.textContent = price;
        favModalStock.textContent = productStock[name] || '-';
        favQtyVal.textContent     = '1';
        favBtnBuy.href = `beli.php?product=${encodeURIComponent(name)}&price=${encodeURIComponent(price)}&qty=1`;
        favModal.classList.add('active');
    }

    function closeFavModal() { favModal.classList.remove('active'); currentFav = null; }

    favModalClose.addEventListener('click', closeFavModal);
    favModal.addEventListener('click', function(e){ if(e.target === favModal) closeFavModal(); });

    favQtyMinus.addEventListener('click', function(){
        let q = parseInt(favQtyVal.textContent) || 1;
        if(q > 1) favQtyVal.textContent = q - 1;
    });
    favQtyPlus.addEventListener('click', function(){
        favQtyVal.textContent = (parseInt(favQtyVal.textContent) || 1) + 1;
    });

    favBtnCart.addEventListener('click', function(){
        if(!currentFav) return;
        const qty = parseInt(favQtyVal.textContent) || 1;
        if(window.sphinxCart){
            sphinxCart.addItem({ name: getFavName(currentFav), price: getFavPrice(currentFav), image: getFavImage(currentFav), qty });
        } else {
            let cart = JSON.parse(localStorage.getItem('sphinx_cart') || '[]');
            const name = getFavName(currentFav);
            let ex = cart.find(i => i.name === name);
            if(ex) ex.qty += qty;
            else cart.push({ name, price: getFavPrice(currentFav), image: getFavImage(currentFav), qty });
            localStorage.setItem('sphinx_cart', JSON.stringify(cart));
        }
        favQtyVal.textContent = '1';
        closeFavModal();
    });

    favBtnBuy.addEventListener('click', function(e){
        e.preventDefault();
        if(!this.getAttribute('href') || this.getAttribute('href') === '#') return;
        const qty = parseInt(favQtyVal.textContent) || 1;
        const base = this.href.split('&qty=')[0];
        closeFavModal();
        window.location.href = base + '&qty=' + qty;
    });

    // ===== TAMPILKAN KARTU FAVORIT =====
    function loadFavorites() {
        const favorites = JSON.parse(localStorage.getItem(favoriteKey) || '[]');
        const container = document.getElementById('favoritesContainer');

        if (favorites.length === 0) {
            container.innerHTML = `
                <div class="empty-state">
                    <i class="fa-regular fa-heart"></i>
                    <h2>Belum Ada Favorit</h2>
                    <p>Klik ikon hati di Dashboard untuk menambahkan piercing ke favorit Anda.</p>
                </div>`;
            return;
        }

        let html = '<div class="favorites-list">';
        favorites.forEach(fav => {
            const name     = getFavName(fav);
            const imageSrc = getFavImage(fav);
            const safeName = escapeHtml(name);
            html += `
                <div class="favorite-card" data-product="${safeName}">
                    <button class="remove-btn" data-product="${safeName}" title="Hapus dari favorit">
                        <i class="fa-solid fa-heart"></i>
                    </button>
                    ${imageSrc ? `<img src="${escapeHtml(imageSrc)}" alt="${safeName}" onerror="this.style.display='none'">` : ''}
                    <span>${safeName}</span>
                </div>`;
        });
        html += '</div>';
        container.innerHTML = html;

        document.querySelectorAll('.favorite-card').forEach(card => {
            card.addEventListener('click', function(e){
                if(e.target.closest('.remove-btn')) return;
                const fav = favorites.find(f => getFavName(f) === this.dataset.product);
                if(fav) openFavModal(fav);
            });
        });

        document.querySelectorAll('.remove-btn').forEach(btn => {
            btn.addEventListener('click', function(e){
                e.stopPropagation();
                const productName = this.dataset.product;
                let favs = JSON.parse(localStorage.getItem(favoriteKey) || '[]');
                favs = favs.filter(f => getFavName(f) !== productName);
                localStorage.setItem(favoriteKey, JSON.stringify(favs));
                loadFavorites();
            });
        });
    }

    window.addEventListener('DOMContentLoaded', loadFavorites);
</script>
</body>
</html>

