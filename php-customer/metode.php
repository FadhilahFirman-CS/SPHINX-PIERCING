<?php
$current_page = basename($_SERVER['PHP_SELF']);
$page_css = '../css-customer/metode.css?v=3';
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
            <li><a href="dashboard.php" class="<?= $current_page == 'dashboard.php' ? 'active' : '' ?>"><i class="fa-solid fa-house"></i>Dashboard</a></li>
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

        <div class="payment-options">
            <div class="pay-card" data-method="cash" onclick="selectMethod('cash', this)">
                <i class="fa-solid fa-money-bill"></i>
                <span>Cash</span>
                <span class="method-total">-</span>
            </div>
            <div class="pay-card" data-method="transfer" onclick="selectMethod('transfer', this)">
                <i class="fa-solid fa-money-bill-transfer"></i>
                <span>Transfer</span>
                <span class="method-total">-</span>
            </div>
            <div class="pay-card" data-method="qris" onclick="selectMethod('qris', this)">
                <i class="fa-solid fa-qrcode"></i>
                <span>QRIS</span>
                <span class="method-total">-</span>
            </div>
        </div>
        <div id="cancelMethodArea" style="display:none; margin-bottom:16px;">
            <button onclick="resetMethod()" style="background:rgba(255,255,255,0.08); color:var(--text-soft); border:1px solid rgba(255,255,255,0.15); padding:8px 20px; border-radius:8px; cursor:pointer; font-size:13px; transition:.2s;" onmouseover="this.style.background='rgba(255,255,255,0.15)'" onmouseout="this.style.background='rgba(255,255,255,0.08)'">
                <i class="fa-solid fa-rotate-left" style="margin-right:6px;"></i>Batal / Ganti Metode
            </button>
        </div>

        <div class="payment-details-area" id="detailArea">
            <div class="no-selection" id="defaultMsg">
                MEMUAT DATA...
            </div>

            <div class="details-grid" id="contentGrid" style="display: none;">
                <div class="bill-info">
                    <h3>Perlu Dibayar</h3>
                    <div class="bill-row">
                        <span class="bill-label">Jenis Produk</span><span class="bill-sep">:</span>
                        <span class="bill-val" id="selectedProductType">JASA/PRODUK</span>
                    </div>
                    <div class="bill-row">
                        <span class="bill-label">Nama Produk</span><span class="bill-sep">:</span>
                        <span class="bill-val" id="selectedProductName">PASANG/ANTING</span>
                    </div>
                    <div class="bill-row">
                        <span class="bill-label">Jumlah</span><span class="bill-sep">:</span>
                        <span class="bill-val" id="selectedQuantity">3 Anting</span>
                    </div>
                    <div class="bill-row">
                        <span class="bill-label">Total</span><span class="bill-sep">:</span>
                        <span class="bill-val" id="selectedTotal">Rp 120.000</span>
                    </div>
                    
                    <div class="method-row">
                        <span class="bill-label">Metode</span><span class="bill-sep">:</span>
                        <span class="bill-val" id="selectedMethodName" style="text-transform: capitalize;">-</span>
                    </div>
                </div>

                <div class="payment-instruction" id="rightContent">
                </div>
            </div>
        </div>
        <div class="payment-timer" id="paymentTimer">
            <span>Waktu tersisa untuk menyelesaikan pembayaran:</span>
            <strong id="timerValue">00:00</strong>
        </div>
    </main>
</div>

<script>
    function formatRupiah(value) {
        if (!value || isNaN(parseInt(value))) return value;
        return 'Rp ' + parseInt(value, 10).toLocaleString('id-ID');
    }

    let paymentTimerInterval = null;
    let currentPaymentType = null;
    let currentTimerSeconds = 0;
    let paymentExpired = false;
    
    // Penanda apakah pengguna sedang dalam proses transaksi
    let isTransactionActive = false; 

    function formatTimer(seconds, type) {
        if (type === 'cash') {
            const hours = Math.floor(seconds / 3600);
            const minutes = Math.floor((seconds % 3600) / 60);
            const secs = seconds % 60;
            return `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:${String(secs).padStart(2, '0')}`;
        }
        const mins = Math.floor(seconds / 60);
        const secs = seconds % 60;
        return `${String(mins).padStart(2, '0')}:${String(secs).padStart(2, '0')}`;
    }

    function setTimerText(type, seconds) {
        const timerBox = document.getElementById('paymentTimer');
        const timerValue = document.getElementById('timerValue');
        timerValue.innerText = formatTimer(seconds, type);
        timerBox.style.display = 'flex';
    }

    function clearPaymentTimer() {
        if (paymentTimerInterval) {
            clearInterval(paymentTimerInterval);
            paymentTimerInterval = null;
        }
    }

    function startPaymentTimer(type) {
        clearPaymentTimer();
        currentPaymentType = type;
        paymentExpired = false;
        currentTimerSeconds = type === 'cash' ? 24 * 60 * 60 : 60;
        setTimerText(type, currentTimerSeconds);

        paymentTimerInterval = setInterval(() => {
            currentTimerSeconds -= 1;
            if (currentTimerSeconds < 0) {
                clearPaymentTimer();
                paymentExpired = true;
                document.getElementById('timerValue').innerText = 'EXPIRED';
                alert('Pembayaran gagal: Waktu pembayaran habis. Silakan pilih metode lagi.');
                return;
            }
            setTimerText(type, currentTimerSeconds);
        }, 1000);
    }

    function completePayment(type, amount, orderStatus) {
        const params = parseQueryParams();
        const shippingVal = params.get('shipping') || '';
        const isDelivery = (shippingVal === 'gosend' || shippingVal === 'spx');
        const isPaidOnline = (type === 'qris' || type === 'transfer');

        // Tentukan status pesanan dan catatan pembayaran secara otomatis
        if (!orderStatus) {
            orderStatus = 'Pesanan Masuk';
        }
        let paymentNote = '';
        if (isDelivery && isPaidOnline) {
            paymentNote = 'Pembayaran Lunas via ' + getMethodLabel(type);
        } else if (isDelivery && type === 'cash') {
            paymentNote = 'Pembayaran Cash dengan Kurir';
        }
        if (paymentExpired) {
            alert('Pembayaran sudah hangus. Silakan pilih kembali metode pembayaran.');
            return;
        }
        
        clearPaymentTimer();
        document.getElementById('paymentTimer').style.display = 'none';
        document.getElementById('rightContent').innerHTML = `
            <div style="text-align:center; color:var(--text-soft);">
                <i class="fa-solid fa-circle-check" style="font-size:50px; color:var(--lime); margin-bottom:15px;"></i>
                <p>Pembayaran berhasil untuk metode <strong>${getMethodLabel(type)}</strong>.<br>Terima kasih telah bertransaksi.</p>
            </div>
        `;
        alert('Pembayaran berhasil! Mengarahkan ke Riwayat...');

        const product = params.get('product') || '';
        const quantity = params.get('quantity') || '1';
        const total = params.get('total') || amount || '0';
        const paymentMethod = params.get('payment_method') || getMethodLabel(type);
        const shipping = params.get('shipping') || '';
        const recipientName = params.get('recipient_name') || '';
        const recipientPhone = params.get('recipient_phone') || '';
        const recipientAddress = params.get('recipient_address') || '';
        
        const orderId = `order_${Date.now()}_${Math.floor(Math.random() * 10000)}`;
        
        const historyParams = new URLSearchParams();
        historyParams.set('order_complete', '1');
        historyParams.set('order_id', orderId);
        historyParams.set('product', product);
        historyParams.set('quantity', quantity);
        historyParams.set('total', total);
        historyParams.set('payment_method', paymentMethod);
        if (shipping)        historyParams.set('shipping',          shipping);
        if (recipientName)   historyParams.set('recipient_name',    recipientName);
        if (recipientPhone)  historyParams.set('recipient_phone',   recipientPhone);
        if (recipientAddress) historyParams.set('recipient_address', recipientAddress);
        historyParams.set('order_status', orderStatus);
        if (paymentNote) historyParams.set('payment_note', paymentNote);

        window.location.href = 'riwayat.php?' + historyParams.toString();
    }

    function updateMethodTotals(selected, totalValue) {
        const cards = document.querySelectorAll('.pay-card');
        cards.forEach(card => {
            const totalEl = card.querySelector('.method-total');
            if (!totalEl) return;
            if (card.dataset.method === selected) {
                totalEl.innerText = formatRupiah(totalValue);
            } else {
                totalEl.innerText = '';
            }
        });
    }

    function getMethodLabel(type) {
        if (!type) return '-';
        if (type === 'cash') return 'Cash';
        if (type === 'qris') return 'QRIS';
        if (type === 'transfer') return 'Transfer Bank';
        return type.charAt(0).toUpperCase() + type.slice(1);
    }

    function resetMethod() {
        clearPaymentTimer();
        document.getElementById('paymentTimer').style.display = 'none';
        document.querySelectorAll('.pay-card').forEach(card => {
            card.classList.remove('active', 'disabled');
        });
        document.getElementById('defaultMsg').style.display = 'block';
        document.getElementById('defaultMsg').innerText = 'PILIH METODE PEMBAYARAN TERLEBIH DAHULU DI ATAS!';
        document.getElementById('contentGrid').style.display = 'none';
        document.getElementById('rightContent').innerHTML = '';
        document.getElementById('cancelMethodArea').style.display = 'none';
        currentPaymentType = null;
    }

    function selectMethod(type, element) {
        // CEK PENTING: Jika tidak ada transaksi aktif, abaikan klik!
        if (!isTransactionActive) return; 

        const cards = document.querySelectorAll('.pay-card');
        cards.forEach(card => {
            card.classList.remove('active');
            card.classList.add('disabled');
        });

        element.classList.add('active');
        element.classList.remove('disabled');
        document.getElementById('cancelMethodArea').style.display = 'block';

        document.getElementById('defaultMsg').style.display = 'none';
        document.getElementById('contentGrid').style.display = 'grid';

        document.getElementById('selectedMethodName').innerText = getMethodLabel(type);
        updateMethodTotals(type, window.__methodTotalValue || 0);

        const rightBox = document.getElementById('rightContent');
        
        if (type === 'qris') {
            rightBox.innerHTML = `
                <div class="qr-frame">
                    <span class="qr-title">PIERCING</span>
                    <span class="qr-id">NMID : ID10190000213</span>
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=PembayaranSphinx" alt="QRIS">
                    <span style="font-size:10px; margin-top:5px; display:block;">Scan untuk membayar</span>
                    <button class="btn-bayar" id="paymentActionButton">Bayar Sekarang</button>
                </div>
            `;
        } else if (type === 'transfer') {
            rightBox.innerHTML = `
                <div class="transfer-info">
                    <h3 style="color:var(--lime); margin-bottom:15px; text-align:center;">Transfer</h3>
                    <div class="bank-row">
                        <span class="bank-label">Bank</span>
                        <span class="bank-val">BCA</span>
                    </div>
                    <div class="bank-row">
                        <span class="bank-label">No. Rekening</span>
                        <span class="bank-val">13256784321</span>
                    </div>
                    <div class="bank-row">
                        <span class="bank-label">Nama Pemilik</span>
                        <span class="bank-val">PiercingSpxJogja</span>
                    </div>
                </div>
                <button class="btn-bayar" id="paymentActionButton">Bayar Sekarang</button>
            `;
        } else if (type === 'cash') {
            const shippingVal = parseQueryParams().get('shipping') || '';
            const isCOD = (shippingVal === 'gosend' || shippingVal === 'spx');
            if (isCOD) {
                rightBox.innerHTML = `
                    <div style="text-align:center;">
                        <i class="fa-solid fa-truck" style="font-size:50px; color:var(--lime); margin-bottom:15px;"></i>
                        <p style="font-weight:700; font-size:14px; color:var(--lime); letter-spacing:.5px; text-transform:uppercase; margin-bottom:10px;">Lakukan pembayaran di kurir<br>jika barang sudah diterima</p>
                        <p style="font-size:12px; color:var(--text-soft); margin-bottom:20px;">Pesanan akan segera dikirimkan ke alamat Anda.</p>
                        <button class="btn-bayar" id="codOkeButton">OKE</button>
                    </div>
                `;
            } else {
                rightBox.innerHTML = `
                    <div style="text-align:center; color:var(--text-soft);">
                        <i class="fa-solid fa-cash-register" style="font-size:50px; color:var(--lime); margin-bottom:15px;"></i>
                        <p>Silakan lakukan pembayaran<br>di meja kasir.</p>
                    </div>
                    <button class="btn-bayar" id="paymentActionButton">Bayar di Kasir</button>
                `;
            }
        }

        startPaymentTimer(type);
    }

    document.addEventListener('click', function(event) {
        if (event.target && event.target.id === 'paymentActionButton') {
            completePayment(currentPaymentType, window.__methodTotalValue || 0);
        }
        if (event.target && event.target.id === 'codOkeButton') {
            completePayment(currentPaymentType, window.__methodTotalValue || 0, 'Dalam Pengiriman');
        }
    });

    function parseQueryParams() {
        return new URLSearchParams(window.location.search);
    }

    function normalizeMethod(method) {
        if (!method) return null;
        const key = method.toLowerCase();
        if (key.includes('cash')) return 'cash';
        if (key.includes('gopay')) return 'cash';
        if (key.includes('qris')) return 'qris';
        if (key.includes('transfer')) return 'transfer';
        return null;
    }

    window.addEventListener('DOMContentLoaded', () => {
        const params = parseQueryParams();
        const methodParam = normalizeMethod(params.get('payment_method'));
        const productParam = params.get('product');
        const quantityParam = params.get('quantity');
        const totalParam = params.get('total');

        // Cek apakah URL mengandung data produk atau total harga
        isTransactionActive = !!(productParam || totalParam);

        if (!isTransactionActive) {
            // 1. Belum ada transaksi — kunci semua tombol metode
            document.querySelectorAll('.pay-card').forEach(card => card.classList.add('disabled'));
            document.getElementById('defaultMsg').innerText = "BELUM ADA TRANSAKSI. SILAKAN LAKUKAN PEMBELIAN TERLEBIH DAHULU.";
        } else {
            // 2. Ada transaksi — aktifkan dan isi data dari halaman checkout
            document.getElementById('defaultMsg').innerText = "PILIH METODE PEMBAYARAN TERLEBIH DAHULU DI ATAS!";
            
            document.getElementById('selectedProductType').innerText = 'Produk/Jasa';
            document.getElementById('selectedProductName').innerText = productParam || 'Pesanan Anda';
            document.getElementById('selectedQuantity').innerText = quantityParam ? `${quantityParam} pcs` : '1 pcs';
            document.getElementById('selectedTotal').innerText = totalParam ? formatRupiah(totalParam) : 'Rp 0';
            
            window.__methodTotalValue = totalParam || 0;

            // Jika pengguna sudah memilih metode sebelumnya, langsung pilihkan
            if (methodParam) {
                const card = Array.from(document.querySelectorAll('.pay-card')).find(c => c.dataset.method === methodParam);
                if (card) {
                    selectMethod(methodParam, card);
                }
            }
        }
    });
</script>

</body>
</html>