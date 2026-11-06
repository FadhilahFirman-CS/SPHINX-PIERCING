<?php
                // config.php sudah: session_start(), helper sanitize_text/format_rupiah,
                // normalisasi key sesi, dan daftar halaman publik.
                require_once __DIR__ . '/config.php';

                $customer_username = $_SESSION['customer_user'] ?? '';
                $username = $customer_username !== '' ? $customer_username : '@sphnx_piercing';
                $is_logged_in = $customer_username !== '';

                // Deteksi halaman saat ini secara case-insensitive agar tidak
                // bergantung pada kapitalisasi nama file di server.
                $_current_page_lc = strtolower(basename($_SERVER['PHP_SELF'] ?? ''));
                $_public_pages = array_map('strtolower', $GLOBALS['SPHINX_PUBLIC_PAGES'] ?? []);
                if (!$is_logged_in && !in_array($_current_page_lc, $_public_pages, true)) {
                    header('Location: Login.php');
                    exit;
                }
                ?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sphinx Piercing</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" />
    <link rel="stylesheet" href="../css-customer/header.css?v=<?= filemtime(__DIR__ . '/../css-customer/header.css') ?>">
    <?php if (!empty($page_css)): ?>
        <link rel="stylesheet" href="<?= htmlspecialchars($page_css, ENT_QUOTES, 'UTF-8') ?>">
    <?php endif; ?>
    <link rel="stylesheet" href="../css-customer/responsive_unified.css?v=<?= filemtime(__DIR__ . '/../css-customer/responsive_unified.css') ?>">
</head>

<body>
    <?php if (!$is_logged_in): ?>
    <div class="guest-top-bar" id="guestTopBar">
        <span><i class="fa-solid fa-eye"></i> Kamu sedang browsing sebagai <strong>Tamu</strong>. Beberapa fitur terkunci.</span>
        <div class="guest-top-bar-actions">
            <a href="Login.php"><i class="fa-solid fa-right-to-bracket"></i> Login</a>
            <a href="BuatAkun.php" class="outline"><i class="fa-solid fa-user-plus"></i> Daftar</a>
            <button onclick="document.getElementById('guestTopBar').style.display='none'" aria-label="Tutup"><i class="fa-solid fa-xmark"></i></button>
        </div>
    </div>
    <?php endif; ?>
    <header class="cart-bar">
        <div class="logo">Sphinx Piercing <i class="fas fa-gem"></i></div>
        <div class="hamburger" id="hamburger">
            <span></span><span></span><span></span>
        </div>
        <div class="header-actions">
            <button class="header-notif-btn" type="button" id="notifBtn" aria-label="Bantuan" aria-haspopup="true" aria-expanded="false" aria-controls="notifMenu" onclick="window.location.href='bantuan.php'">
                <i class="fa-regular fa-bell" aria-hidden="true"></i>
            </button>
            <button class="cart-icon" type="button" id="cartIcon" aria-label="Keranjang belanja" aria-haspopup="true" aria-expanded="false" aria-controls="cartDropdown">
                <i class="fas fa-shopping-cart" aria-hidden="true"></i>
                <span class="cart-count" id="cartCount">0</span>
            </button>
            <?php if ($is_logged_in): ?>
                <a class="header-maps-link" href="maps.php" aria-label="Buka peta lokasi">
                    <i class="fa-solid fa-location-dot"></i> <span>Maps</span>
                </a>
                <div class="profile-wrap" id="profileWrap">
                    <span class="profile-name"><?= htmlspecialchars($username) ?></span>
                    <button class="profile-btn" id="profileBtn" type="button" aria-label="Buka menu profil" aria-haspopup="true" aria-expanded="false" aria-controls="profileDropdown">
                        <i class="fa-solid fa-user" aria-hidden="true"></i>
                    </button>
                    <div class="profile-dropdown" id="profileDropdown" role="menu" aria-hidden="true">
                        <a href="pengaturan.php" role="menuitem"><i class="fa-solid fa-gear"></i> Pengaturan Profil</a>
                        <a href="logout.php" role="menuitem"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
                    </div>
                </div>
            <?php else: ?>
                <a class="profile-login-btn" href="Login.php" aria-label="Login">
                    <i class="fa-solid fa-user" aria-hidden="true"></i>
                    <span>Login</span>
                </a>
            <?php endif; ?>
        </div>
        <div class="cart-dropdown" id="cartDropdown">
            <div id="cartItems"></div>
            <div class="cart-total" id="cartTotal">Total: Rp 0</div>
            <button type="button" class="btn-checkout" id="checkoutBtn" style="display: none;">Checkout Keranjang</button>
        </div>
    </header>

    <script>
        window.sphinxAuth = {
            isLoggedIn: <?= $is_logged_in ? 'true' : 'false' ?>,
            loginUrl: 'Login.php'
        };

        <?php if (!$is_logged_in): ?>
        // Tambahkan class guest-mode ke body agar header dan sidebar offset ke bawah
        document.documentElement.classList.add('guest-mode');
        document.body.classList.add('guest-mode');
        <?php endif; ?>

        // Ukur tinggi header yang sebenarnya (termasuk saat wrap ke 2 baris)
        // dan publish ke CSS variable --header-h-real agar konten di bawah
        // header tidak ter-clip. Dijalankan setelah layout siap dan di-resize.
        (function() {
            function publishHeaderHeight() {
                var bar = document.querySelector('.cart-bar');
                if (!bar) return;
                var h = Math.max(bar.offsetHeight, 48);
                document.documentElement.style.setProperty('--header-h-real', h + 'px');
            }
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', publishHeaderHeight);
            } else {
                publishHeaderHeight();
            }
            window.addEventListener('load', publishHeaderHeight);
            window.addEventListener('resize', publishHeaderHeight);
            if (window.ResizeObserver) {
                var ro = new ResizeObserver(publishHeaderHeight);
                var bar = document.querySelector('.cart-bar');
                if (bar) ro.observe(bar);
            }
        })();

        window.sphinxRequireLogin = function(message, redirect = false) {
            if (window.sphinxAuth.isLoggedIn) return true;
            alert(message || 'Silakan login terlebih dahulu.');
            if (redirect) {
                window.location.href = window.sphinxAuth.loginUrl;
            }
            return false;
        };

        // Buka/tutup dropdown profil
        const profileBtn = document.getElementById('profileBtn');
        const profileDropdown = document.getElementById('profileDropdown');
        if (profileBtn && profileDropdown) {
            profileBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                const isOpen = profileDropdown.classList.toggle('active');
                profileBtn.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
                profileDropdown.setAttribute('aria-hidden', isOpen ? 'false' : 'true');
                // tutup dropdown keranjang kalau sedang terbuka
                const cart = document.getElementById('cartDropdown');
                if (cart) cart.classList.remove('active');
                const cartIcon = document.getElementById('cartIcon');
                if (cartIcon) cartIcon.setAttribute('aria-expanded', 'false');
            });
            document.addEventListener('click', function() {
                if (profileDropdown.classList.contains('active')) {
                    profileDropdown.classList.remove('active');
                    profileBtn.setAttribute('aria-expanded', 'false');
                    profileDropdown.setAttribute('aria-hidden', 'true');
                }
            });
            profileDropdown.addEventListener('click', function(e) {
                e.stopPropagation();
            });
            // Tutup dengan tombol Escape
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && profileDropdown.classList.contains('active')) {
                    profileDropdown.classList.remove('active');
                    profileBtn.setAttribute('aria-expanded', 'false');
                    profileDropdown.setAttribute('aria-hidden', 'true');
                    profileBtn.focus();
                }
            });
        }

        // Tombol hamburger untuk membuka/tutup sidebar
        (function() {
            const hamburger = document.getElementById('hamburger');
            if (!hamburger) return;

            // Buat backdrop untuk mobile
            const backdrop = document.createElement('div');
            backdrop.className = 'mobile-sidebar-backdrop';
            document.body.appendChild(backdrop);

            function isMobile() {
                return window.innerWidth <= 480;
            }

            hamburger.addEventListener('click', function() {
                this.classList.toggle('active');
                const sidebar = document.querySelector('.sidebar');
                if (!sidebar) return;

                if (isMobile()) {
                    // Mobile: sidebar sebagai overlay drawer
                    const isOpen = sidebar.classList.toggle('mobile-open');
                    backdrop.classList.toggle('active', isOpen);
                    document.body.style.overflow = isOpen ? 'hidden' : '';
                } else {
                    // Desktop/tablet: toggle hidden seperti semula
                    sidebar.classList.toggle('hidden');
                }
            });

            // Tutup sidebar saat klik backdrop
            backdrop.addEventListener('click', function() {
                const sidebar = document.querySelector('.sidebar');
                if (sidebar) sidebar.classList.remove('mobile-open');
                backdrop.classList.remove('active');
                hamburger.classList.remove('active');
                document.body.style.overflow = '';
            });

            // Handle resize: bersihkan state mobile jika pindah ke desktop
            window.addEventListener('resize', function() {
                if (!isMobile()) {
                    const sidebar = document.querySelector('.sidebar');
                    if (sidebar) sidebar.classList.remove('mobile-open');
                    backdrop.classList.remove('active');
                    document.body.style.overflow = '';
                }
            });
        })();

        // Inisialisasi keranjang ditangani oleh script sphinxCart di bawah
    </script>

    <div class="main-content">

        <script>
            // Helper keranjang yang tersedia di semua halaman
            window.sphinxCart = (function() {
                const KEY = 'sphinx_cart';

                function load() {
                    try {
                        return JSON.parse(localStorage.getItem(KEY) || '[]');
                    } catch (e) {
                        return [];
                    }
                }

                function save(cart) {
                    localStorage.setItem(KEY, JSON.stringify(cart || []));
                }

                function totalAmount(cart) {
                    cart = cart || load();
                    return cart.reduce((sum, it) => {
                        const price = parseInt(String(it.price || '').replace(/[^0-9]/g, '')) || 0;
                        return sum + (price * (parseInt(it.qty || it.quantity || 1, 10) || 1));
                    }, 0);
                }

                function formatRupiah(v) {
                    return 'Rp ' + String(v).replace(/\B(?=(\d{3})+(?!\d))/g, '.');
                }

                function updateCartBadge() {
                    const el = document.getElementById('cartCount');
                    if (!el) return;
                    const cart = load();
                    const totalItems = cart.reduce((s, i) => s + (parseInt(i.qty || i.quantity || 1, 10) || 1), 0);
                    el.textContent = totalItems;
                }

                function renderDropdown() {
                    const cart = load();
                    const container = document.getElementById('cartItems');
                    const totalEl = document.getElementById('cartTotal');
                    const checkoutBtn = document.getElementById('checkoutBtn');
                    if (!container || !totalEl) return;
                    container.innerHTML = '';
                    if (cart.length === 0) {
                        container.innerHTML = '<div class="cart-empty">Keranjang kosong</div>';
                        totalEl.textContent = 'Total: Rp 0';
                        if (checkoutBtn) checkoutBtn.style.display = 'none';
                        return;
                    }

                    function escapeHtml(str) {
                        return String(str == null ? '' : str)
                            .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
                            .replace(/"/g, '&quot;').replace(/'/g, '&#39;');
                    }
                    const PLACEHOLDER_IMG = '../gambar/placeholder.png';
                    cart.forEach((it, idx) => {
                        const div = document.createElement('div');
                        div.className = 'cart-item';
                        const img = document.createElement('img');
                        const rawImage = (typeof it.image === 'string') ? it.image.trim() : '';
                        // Render placeholder ketika sumber tidak ada; encode src
                        // yang valid agar tidak memecah attribute pada karakter/spasi.
                        img.src = rawImage ? encodeURI(rawImage) : PLACEHOLDER_IMG;
                        img.alt = escapeHtml(it.name || 'Produk');
                        img.onerror = function() {
                            this.onerror = null;
                            this.src = PLACEHOLDER_IMG;
                        };
                        const info = document.createElement('div');
                        info.className = 'cart-item-info';
                        const name = document.createElement('strong');
                        name.textContent = it.name || 'Produk';
                        const price = document.createElement('div');
                        price.className = 'cart-item-price';
                        price.textContent = (it.qty || 1) + ' × ' + (it.price || '-');
                        info.appendChild(name);
                        info.appendChild(price);
                        const rm = document.createElement('button');
                        rm.className = 'cart-remove';
                        rm.setAttribute('data-idx', String(idx));
                        rm.setAttribute('aria-label', 'Hapus item');
                        const icon = document.createElement('i');
                        icon.className = 'fa-solid fa-trash';
                        rm.appendChild(icon);
                        div.appendChild(img);
                        div.appendChild(info);
                        div.appendChild(rm);
                        container.appendChild(div);
                    });
                    const total = totalAmount(cart);
                    totalEl.textContent = 'Total: ' + formatRupiah(total);
                    if (checkoutBtn) {
                        checkoutBtn.style.display = 'block';
                        // Sambungkan ke checkoutFromCart agar perilakunya konsisten
                        try {
                            checkoutBtn.onclick = checkoutFromCart;
                        } catch (e) {}
                    }
                }

                function addItem(item) {
                    const cart = load();
                    const existing = cart.find(i => i.name === item.name);
                    if (existing) {
                        existing.qty = (parseInt(existing.qty || 1, 10) || 1) + (parseInt(item.qty || 1, 10) || 1);
                    } else cart.push(Object.assign({}, item));
                    save(cart);
                    updateCartBadge();
                    renderDropdown();
                }

                function removeItem(index) {
                    const cart = load();
                    if (index < 0 || index >= cart.length) return;
                    cart.splice(index, 1);
                    save(cart);
                    updateCartBadge();
                    renderDropdown();
                }

                function clear() {
                    save([]);
                    updateCartBadge();
                    renderDropdown();
                }

                function checkoutFromCart() {
                    const cart = load();
                    if (!cart || cart.length === 0) {
                        alert('Keranjang kosong');
                        return;
                    }
                    const encoded = encodeURIComponent(JSON.stringify(cart));
                    // redirect to beli.php with cart param
                    window.location.href = 'beli.php?cart=' + encoded;
                }
                // Pasang event listener setelah DOM siap
                document.addEventListener('DOMContentLoaded', function() {
                    updateCartBadge();
                    renderDropdown();
                    const icon = document.getElementById('cartIcon');
                    const dropdown = document.getElementById('cartDropdown');
                    if (icon && dropdown) {
                        icon.addEventListener('click', function(e) {
                            e.stopPropagation();
                            const isOpen = dropdown.classList.toggle('active');
                            icon.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
                        });
                        document.addEventListener('click', function(ev) {
                            if (!icon.contains(ev.target) && !dropdown.contains(ev.target) && dropdown.classList.contains('active')) {
                                dropdown.classList.remove('active');
                                icon.setAttribute('aria-expanded', 'false');
                            }
                        });
                        document.addEventListener('keydown', function(e) {
                            if (e.key === 'Escape' && dropdown.classList.contains('active')) {
                                dropdown.classList.remove('active');
                                icon.setAttribute('aria-expanded', 'false');
                                icon.focus();
                            }
                        });
                    }

                    // Tombol checkout tetap tampil; aksi akhirnya dibatasi di checkoutFromCart.
                    // Bind langsung; biarkan ReferenceError muncul di console (saat
                    // development) bila checkoutFromCart tidak tersedia.
                    const checkoutBtnEl = document.getElementById('checkoutBtn');
                    if (checkoutBtnEl) {
                        checkoutBtnEl.onclick = checkoutFromCart;
                    }

                    document.getElementById('cartItems')?.addEventListener('click', function(ev) {
                        const btn = ev.target.closest('.cart-remove');
                        if (!btn) return;
                        const idx = parseInt(btn.dataset.idx, 10);
                        removeItem(idx);
                    });
                });
                return {
                    load,
                    save,
                    addItem,
                    removeItem,
                    clear,
                    updateCartBadge,
                    renderDropdown,
                    checkoutFromCart,
                    totalAmount
                };
            })();
        </script>