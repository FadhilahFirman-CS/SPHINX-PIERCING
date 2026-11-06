<?php
if (session_status() == PHP_SESSION_NONE) { session_start(); }
require_once 'config.php';

// Ambil data profil pengguna dari MySQL
$savedAddress = '';
$savedName = '';
$savedPhone = '';
$beliUsername = $_SESSION['customer_user'] ?? '';
if ($beliUsername === '' && empty($_SESSION['admin']) && !empty($_SESSION['user'])) {
    $beliUsername = (string)$_SESSION['user'];
}
if (!empty($beliUsername)) {
    $stmt = $conn->prepare("SELECT username, nama, telepon, alamat FROM customers WHERE username = ? LIMIT 1");
    if ($stmt) {
        $stmt->bind_param("s", $beliUsername);
        $stmt->execute();
        $result = $stmt->get_result();
        $customer = $result ? $result->fetch_assoc() : null;
        $stmt->close();
        if ($customer) {
            $savedAddress = $customer['alamat'] ?? '';
            $savedName    = $customer['nama'] ?? $customer['username'] ?? $beliUsername;
            $savedPhone   = $customer['telepon'] ?? '';
        }
    }
}

// Proses data produk / keranjang
$cartData = isset($_GET['cart']) ? json_decode(urldecode($_GET['cart']), true) : null;
$items = is_array($cartData) ? $cartData : [];
$total = 0;
$priceValue = 0;
$fromCart = !empty($items);
$initialQty = 1;
if ($fromCart) {
    foreach ($items as $item) {
        $priceNum = preg_replace('/[^0-9]/', '', $item['price'] ?? '0');
        $qty = intval($item['qty'] ?? $item['quantity'] ?? 1);
        $total += intval($priceNum) * $qty;
    }
    $priceFormatted = $total > 0 ? 'Rp ' . number_format($total, 0, ',', '.') : 'Rp 0';
    if (!empty($items)) {
        $firstName = trim((string)($items[0]['name'] ?? 'Produk'));
        $productName = count($items) === 1 ? $firstName : $firstName . ' (+' . (count($items) - 1) . ' lainnya)';
    } else {
        $productName = 'Produk';
    }
} else {
    $productName = isset($_GET['product']) ? $_GET['product'] : '';
    $priceValue = isset($_GET['price']) ? preg_replace('/[^0-9]/', '', $_GET['price']) : 0;
    $priceFormatted = $priceValue > 0 ? 'Rp ' . number_format($priceValue, 0, ',', '.') : 'Rp 0';
    $total = intval($priceValue);
    $initialQty = isset($_GET['qty']) ? max(1, intval($_GET['qty'])) : 1;
}

$current_page = basename($_SERVER['PHP_SELF']);
$page_css = '../css-customer/beli.css?v=6';
include 'header.php';
?>

<div class="app">
    <aside class="sidebar">
        <div class="brand">
            <img src="../gambar/logo2.jpeg" alt="Logo">
            <span><?= htmlspecialchars($beliUsername ?: '@sphnx_piercing') ?></span>
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
        <div class="beli-card">
            <h1>Checkout Produk</h1>
            <p>Lengkapi data pembelian dan pilih metode pembayaran di halaman berikutnya.</p>

            <form id="purchaseForm" action="#" method="GET">
                <input type="hidden" name="product" value="<?= sanitize_text($productName) ?>">
                <input type="hidden" name="price" id="productPrice" value="<?= sanitize_text($priceValue) ?>">
                <input type="hidden" name="total" id="totalPriceInput" value="<?= sanitize_text($priceValue) ?>">
                <input type="hidden" name="payment_method" id="paymentMethodInput" value="">

                <div class="form-group">
                    <label>Nama Produk</label>
                    <input type="text" class="form-control readonly-input" value="<?= sanitize_text($productName ?: 'Tidak ada produk') ?>" readonly>
                </div>

                <div class="form-group">
                    <label>Harga Satuan</label>
                    <input type="text" class="form-control readonly-input" value="<?= sanitize_text($priceFormatted) ?>" readonly>
                </div>

                <?php if (!$fromCart): ?>
                <div class="form-group" id="quantityGroup">
                    <label>Jumlah Produk</label>
                    <div class="qty-wrapper">
                        <input type="number" id="quantity" name="quantity" class="form-control" min="1" value="<?= $initialQty ?>">
                        <div class="qty-spinners">
                            <button type="button" class="qty-spin-btn" id="qtyPlus"><i class="fa-solid fa-chevron-up"></i></button>
                            <button type="button" class="qty-spin-btn" id="qtyMinus"><i class="fa-solid fa-chevron-down"></i></button>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <div class="form-group">
                    <label>Jenis Pengiriman</label>
                    <div class="radio-group">
                        <label class="radio-item"><input type="radio" name="shipping" value="gosend" checked> Dikirim menggunakan GoSend</label>
                        <label class="radio-item"><input type="radio" name="shipping" value="spx"> Dikirim menggunakan SPX</label>
                        <label class="radio-item"><input type="radio" name="shipping" value="ambil"> Ambil di Toko</label>
                    </div>
                </div>

                <div class="contact-fields" id="contactFields">
                    <div class="form-group">
                        <label>Nama Penerima</label>
                        <input type="text" id="recipientName" name="recipient_name" class="form-control" value="<?= htmlspecialchars($savedName) ?>">
                    </div>
                    <div class="form-group">
                        <label>No. HP</label>
                        <input type="text" id="recipientPhone" name="recipient_phone" class="form-control" value="<?= htmlspecialchars($savedPhone) ?>">
                    </div>
                    <div class="form-group" id="recipientAddressGroup">
                        <label>Alamat Pengiriman</label>
                        <textarea id="recipientAddress" name="recipient_address" class="form-control" rows="3" <?= !empty($savedAddress) ? 'readonly' : '' ?>><?= htmlspecialchars($savedAddress) ?></textarea>
                        <div style="margin-top:8px; display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
                            <?php if (!empty($savedAddress)): ?>
                            <span id="addressFromProfileLabel" style="font-size:12px; color:#82ff5b;"><i class="fa-solid fa-circle-check"></i> Alamat dari profil</span>
                            <?php endif; ?>
                            <button type="button" id="gantiAlamatBtn" style="background:transparent; border:1px solid rgba(255,255,255,0.2); color:#cdcdcd; border-radius:999px; padding:4px 14px; font-size:12px; cursor:pointer; transition:.2s;" onmouseover="this.style.borderColor='#82ff5b';this.style.color='#82ff5b';" onmouseout="this.style.borderColor='rgba(255,255,255,0.2)';this.style.color='';">
                                <i class="fa-solid fa-pen"></i> Ganti Alamat
                            </button>
                        </div>
                    </div>
                </div>

                <div class="store-address hidden" id="storeAddress">
                    <strong>Ambil di Toko</strong>
                    <p>Silakan ambil pesanan Anda di:</p>
                    <p>Jl. Plumbon, Modalan, Banguntapan, Kec. Banguntapan, Kabupaten Bantul, Daerah Istimewa Yogyakarta 55191<br>Kode Pos 55281<br>Jam buka: 10.00 - 20.00 WIB</p>
                </div>

                <div class="summary">
                    <h2>Ringkasan Pembayaran</h2>
                    <?php if ($fromCart): ?>
                        <div id="cartSummaryItems">
                            <?php foreach ($items as $it): ?>
                                <p><?= htmlspecialchars($it['name'] ?? 'Produk') ?> &times; <?= intval($it['qty'] ?? $it['quantity'] ?? 1) ?> &mdash; <?= htmlspecialchars($it['price'] ?? '-') ?></p>
                            <?php endforeach; ?>
                        </div>
                        <p>Jenis Pengiriman: <span id="summaryShipping">GoSend</span></p>
                        <p id="summaryOngkir">Ongkir: <span id="summaryOngkirVal">Rp 15.000</span></p>
                        <p id="summaryAddress"></p>
                        <strong>Total Harga: <span id="summaryTotal"><?= sanitize_text($priceFormatted) ?></span></strong>
                    <?php else: ?>
                        <p>Jumlah Produk: <span id="summaryQuantity">1</span></p>
                        <p>Harga Satuan: <span id="summaryPrice"><?= sanitize_text($priceFormatted) ?></span></p>
                        <p>Jenis Pengiriman: <span id="summaryShipping">GoSend</span></p>
                        <p id="summaryOngkir">Ongkir: <span id="summaryOngkirVal">Rp 15.000</span></p>
                        <strong>Total Harga: <span id="summaryTotal"><?= sanitize_text($priceFormatted) ?></span></strong>
                    <?php endif; ?>
                </div>

                <div class="actions">
                    <button type="button" id="openPaymentButton" class="btn-submit">Pilih Metode Pembayaran</button>
                    <a href="Dashboard.php" class="btn-back"><i class="fa-solid fa-arrow-left"></i> Kembali</a>
                </div>

                <div class="payment-summary" id="paymentSummary">
                    <h3>Metode Pembayaran Dipilih</h3>
                    <p>Metode: <strong id="summaryPaymentMethod">Belum memilih</strong></p>
                    <p>Total yang harus dibayar: <strong id="summaryTotalMethod"><?= sanitize_text($priceFormatted) ?></strong></p>
                    <button type="button" id="payNowButton" class="pay-now">Bayar Sekarang</button>
                </div>
            </form>
            <p class="hint">Pilih metode pembayaran dari popup. Setelah memilih, klik Bayar Sekarang untuk menuju halaman pembayaran.</p>
        </div><!-- /.beli-card -->

        <div class="payment-modal" id="paymentModal">
            <div class="payment-modal-card">
                <h2>Pilih Metode Pembayaran</h2>
                <div class="payment-method-list">
                    <div class="payment-method-card" data-method="Cash">
                        <div><span>Cash</span><small>Bayar langsung di kasir</small></div>
                        <i class="fa-solid fa-money-bill" style="font-size:22px; color:#82ff5b;"></i>
                    </div>
                    <div class="payment-method-card" data-method="QRIS">
                        <div><span>QRIS</span><small>Scan dan bayar melalui QR</small></div>
                        <i class="fa-solid fa-qrcode" style="font-size:22px; color:#82ff5b;"></i>
                    </div>
                    <div class="payment-method-card" data-method="Transfer Bank">
                        <div><span>Transfer Bank</span><small>Transfer melalui ATM/mobile banking</small></div>
                        <i class="fa-solid fa-money-bill-transfer" style="font-size:22px; color:#82ff5b;"></i>
                    </div>
                </div>
            </div>
        </div>
    </main><!-- /.main -->
</div><!-- /.app -->

<script>
    const purchaseForm = document.getElementById('purchaseForm');
    const quantityInput = document.getElementById('quantity');
    const qtyMinus = document.getElementById('qtyMinus');
    const qtyPlus = document.getElementById('qtyPlus');
    if (qtyMinus) {
        qtyMinus.addEventListener('click', function() {
            let v = Math.max(1, parseInt(quantityInput.value || '1', 10) - 1);
            quantityInput.value = v; updateSummary();
        });
    }
    if (qtyPlus) {
        qtyPlus.addEventListener('click', function() {
            let v = parseInt(quantityInput.value || '1', 10) + 1;
            quantityInput.value = v; updateSummary();
        });
    }
    const summaryQuantity = document.getElementById('summaryQuantity');
    const summaryShipping = document.getElementById('summaryShipping');
    const summaryOngkirVal = document.getElementById('summaryOngkirVal');
    const summaryOngkirRow = document.getElementById('summaryOngkir');
    const summaryAddress = document.getElementById('summaryAddress');
    const summaryTotal = document.getElementById('summaryTotal');
    const totalPriceInput = document.getElementById('totalPriceInput');
    const productPriceValue = parseInt(document.getElementById('productPrice').value || '0', 10);
    const openPaymentButton = document.getElementById('openPaymentButton');
    const paymentModal = document.getElementById('paymentModal');
    const paymentSummary = document.getElementById('paymentSummary');
    const summaryPaymentMethod = document.getElementById('summaryPaymentMethod');
    const summaryTotalMethod = document.getElementById('summaryTotalMethod');
    const paymentMethodInput = document.getElementById('paymentMethodInput');
    const payNowButton = document.getElementById('payNowButton');
    const storeAddress = document.getElementById('storeAddress');
    const recipientName = document.getElementById('recipientName');
    const recipientPhone = document.getElementById('recipientPhone');
    const recipientAddress = document.getElementById('recipientAddress');
    const recipientAddressGroup = document.getElementById('recipientAddressGroup');
    const fromCart = <?= $fromCart ? 'true' : 'false' ?>;
    const ONGKIR = { gosend: 15000, spx: 10000, ambil: 0 };
    const requireLogin = window.sphinxRequireLogin || function(message) {
        alert(message || 'Silakan login terlebih dahulu.');
        return false;
    };

    const VALID_SHIPPING = ['gosend', 'spx', 'ambil'];
    const VALID_PHONE = /^[0-9+\-\s]{8,16}$/;

    function normalizeText(value) {
        return String(value || '').replace(/\s+/g, ' ').trim();
    }

    function getSelectedShipping() {
        return document.querySelector('input[name="shipping"]:checked')?.value || 'gosend';
    }

    function formatPhone(value) {
        return normalizeText(value).replace(/[^0-9+\-\s]/g, '');
    }

    function getOngkir() {
        const sel = document.querySelector('input[name="shipping"]:checked')?.value || 'gosend';
        return ONGKIR[sel] ?? 0;
    }
    function formatRupiah(value) {
        return 'Rp ' + value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }
    function updateSummary() {
        const ongkir = getOngkir();
        const shipping = getSelectedShipping();
        if (summaryOngkirVal) summaryOngkirVal.textContent = ongkir > 0 ? formatRupiah(ongkir) : 'Gratis';
        if (fromCart) {
            const base = <?= intval($total) ?>;
            const grand = base + ongkir;
            totalPriceInput.value = grand;
            if (summaryTotal) summaryTotal.textContent = formatRupiah(grand);
            if (summaryTotalMethod) summaryTotalMethod.textContent = formatRupiah(grand);
            if (summaryShipping) summaryShipping.textContent = shipping === 'ambil' ? 'Ambil di Toko' : (shipping === 'spx' ? 'SPX' : 'GoSend');
        } else {
            const quantity = Math.max(1, parseInt(quantityInput ? quantityInput.value || '1' : '1', 10));
            const grand = productPriceValue * quantity + ongkir;
            if (summaryQuantity) summaryQuantity.textContent = quantity;
            if (summaryTotal) summaryTotal.textContent = formatRupiah(grand);
            if (summaryTotalMethod) summaryTotalMethod.textContent = formatRupiah(grand);
            totalPriceInput.value = grand;
            if (summaryShipping) summaryShipping.textContent = shipping === 'ambil' ? 'Ambil di Toko' : (shipping === 'spx' ? 'SPX' : 'GoSend');
        }
    }
    function selectPaymentMethod(method, cardEl) {
        document.querySelectorAll('.payment-method-card').forEach(c => {
            c.classList.remove('selected', 'disabled');
        });
        if (cardEl) cardEl.classList.add('selected');
        document.querySelectorAll('.payment-method-card').forEach(c => {
            if (c !== cardEl) c.classList.add('disabled');
        });
        paymentMethodInput.value = method;
        summaryPaymentMethod.textContent = method;
        paymentSummary.classList.add('active');
        paymentModal.classList.remove('active');
    }
    function toggleShippingFields(method) {
        if (method === 'ambil') {
            recipientAddressGroup.classList.add('hidden');
            recipientAddress.disabled = true;
            recipientAddress.removeAttribute('name');
            storeAddress.classList.remove('hidden');
            if (summaryShipping) summaryShipping.textContent = 'Ambil di Toko';
        } else {
            recipientAddressGroup.classList.remove('hidden');
            recipientAddress.disabled = false;
            recipientAddress.setAttribute('name', 'recipient_address');
            storeAddress.classList.add('hidden');
            if (summaryShipping) summaryShipping.textContent = (method === 'spx' ? 'SPX' : 'GoSend');
        }
    }
    function resetPaymentSelection() {
        paymentMethodInput.value = '';
        paymentSummary.classList.remove('active');
        document.querySelectorAll('.payment-method-card').forEach(c => c.classList.remove('selected', 'disabled'));
    }
    document.querySelectorAll('input[name="shipping"]').forEach(input => {
        input.addEventListener('change', function () {
            toggleShippingFields(this.value);
            updateSummary();
            resetPaymentSelection();
        });
    });
    if (quantityInput) quantityInput.addEventListener('input', updateSummary);
    const initialShipping = document.querySelector('input[name="shipping"]:checked')?.value || 'gosend';
    toggleShippingFields(initialShipping);
    updateSummary();

    const gantiAlamatBtn = document.getElementById('gantiAlamatBtn');
    if (gantiAlamatBtn) {
        gantiAlamatBtn.addEventListener('click', function() {
            recipientAddress.removeAttribute('readonly');
            recipientAddress.value = '';
            recipientAddress.focus();
            const label = document.getElementById('addressFromProfileLabel');
            if (label) label.style.display = 'none';
        });
    }

    openPaymentButton.addEventListener('click', () => {
        if (window.sphinxAuth && !window.sphinxAuth.isLoggedIn) {
            requireLogin('Silakan login terlebih dahulu untuk melakukan checkout.');
            return;
        }
        updateSummary();
        if (!validateForm()) return;
        const product = document.querySelector('input[name="product"]').value;
        const total = totalPriceInput.value;
        const quantity = fromCart ? '' : (quantityInput ? quantityInput.value : '1');
        const shipping = document.querySelector('input[name="shipping"]:checked')?.value || 'gosend';
        const rName  = recipientName  ? recipientName.value.trim()  : '';
        const rPhone = recipientPhone ? recipientPhone.value.trim() : '';
        const rAddr  = recipientAddress ? recipientAddress.value.trim() : '';
        const params = new URLSearchParams();
        params.set('product', product);
        params.set('total', total);
        if (quantity) params.set('quantity', quantity);
        params.set('shipping', shipping);
        params.set('shipping_cost', getOngkir());
        if (rName)  params.set('recipient_name', rName);
        if (rPhone) params.set('recipient_phone', rPhone);
        if (rAddr)  params.set('recipient_address', rAddr);
        window.location.href = 'metode.php?' + params.toString();
    });

    document.querySelectorAll('.payment-method-card').forEach(card => {
        card.addEventListener('click', function () {
            if (this.classList.contains('disabled')) return;
            selectPaymentMethod(this.dataset.method || 'Tidak Diketahui', this);
        });
    });

    payNowButton.addEventListener('click', () => {
        if (window.sphinxAuth && !window.sphinxAuth.isLoggedIn) {
            requireLogin('Silakan login terlebih dahulu untuk melakukan checkout.');
            return;
        }
        if (!paymentMethodInput.value) { alert('Silakan pilih metode pembayaran terlebih dahulu.'); return; }
        if (!validateForm()) return;
        if (fromCart) {
            try {
                const cartJson = encodeURIComponent(JSON.stringify(<?= json_encode($items) ?>));
                let cartInput = document.getElementById('cartJsonInput');
                if (!cartInput) {
                    cartInput = document.createElement('input');
                    cartInput.type = 'hidden'; cartInput.name = 'cart_json'; cartInput.id = 'cartJsonInput';
                    purchaseForm.appendChild(cartInput);
                }
                cartInput.value = cartJson;
            } catch (e) {}
        }
        if (fromCart && window.sphinxCart) window.sphinxCart.clear();
        purchaseForm.action = 'metode.php';
        purchaseForm.submit();
    });

    function validateForm() {
        const shipping = getSelectedShipping();
        const nameValue = normalizeText(recipientName.value);
        const phoneValue = formatPhone(recipientPhone.value);
        const addressValue = normalizeText(recipientAddress.value);

        if (!VALID_SHIPPING.includes(shipping)) {
            alert('Jenis pengiriman tidak valid.');
            return false;
        }

        if (shipping === 'ambil') {
            if (!nameValue || !phoneValue) {
                alert('Lengkapi nama dan nomor HP untuk pengambilan di toko.'); return false;
            }
            if (!VALID_PHONE.test(phoneValue)) {
                alert('Nomor HP tidak valid. Gunakan 8-16 karakter angka.');
                return false;
            }
        } else {
            if (!nameValue || !phoneValue || !addressValue) {
                alert('Lengkapi nama, nomor HP, dan alamat pengiriman terlebih dahulu.'); return false;
            }
            if (!VALID_PHONE.test(phoneValue)) {
                alert('Nomor HP tidak valid. Gunakan 8-16 karakter angka.');
                return false;
            }
            if (addressValue.length < 10) {
                alert('Alamat terlalu singkat. Masukkan alamat yang lengkap.');
                return false;
            }
        }
        return true;
    }

    paymentModal.addEventListener('click', event => {
        if (event.target === paymentModal) paymentModal.classList.remove('active');
    });
</script>
