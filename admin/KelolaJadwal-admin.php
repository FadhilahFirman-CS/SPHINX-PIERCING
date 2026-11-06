<?php
require_once __DIR__ . '/auth_admin.php';
require_once __DIR__ . '/pesanan_helper.php';

// =====================================================================
// Dynamic bulan/tahun (dari GET atau current)
// =====================================================================
$year  = isset($_GET['year'])  && ctype_digit($_GET['year'])  ? (int)$_GET['year']  : (int)date('Y');
$month = isset($_GET['month']) && ctype_digit($_GET['month']) ? (int)$_GET['month'] : (int)date('n');
$month = max(1, min(12, $month));

$today_num     = (int)date('j');
$today_month   = (int)date('n');
$today_year    = (int)date('Y');
$first_dow     = (int)date('w', mktime(0,0,0,$month,1,$year)); // 0=Sun
$days_in_month = (int)date('t', mktime(0,0,0,$month,1,$year));

// =====================================================================
// Query status per tanggal (berapa pesanan aktif per hari)
// =====================================================================
$qDates = $conn->prepare(
    "SELECT j.tanggal, COUNT(*) AS cnt,
            SUM(CASE WHEN p.status NOT IN ('batal','selesai') THEN 1 ELSE 0 END) AS active
     FROM jadwal j
     JOIN pesanan p ON p.id = j.pesanan_id
     WHERE YEAR(j.tanggal)=? AND MONTH(j.tanggal)=?
     GROUP BY j.tanggal"
);
$qDates->bind_param('ii', $year, $month);
$qDates->execute();
$datesRaw = $qDates->get_result()->fetch_all(MYSQLI_ASSOC);
$qDates->close();

$jadwal_dates = [];
$MAX_SLOT = 7; // slot per hari
foreach ($datesRaw as $dr) {
    $d = (int)date('j', strtotime($dr['tanggal']));
    $jadwal_dates[$d] = $dr['active'] >= $MAX_SLOT ? 'slot_penuh' : 'ada_pesanan';
}
$total_jadwal     = count($jadwal_dates);
$slot_penuh_count = count(array_filter($jadwal_dates, fn($v) => $v === 'slot_penuh'));

// =====================================================================
// Selected date (default = today in current month, else 1)
// =====================================================================
$sel_day = isset($_GET['day']) && ctype_digit($_GET['day']) ? (int)$_GET['day'] : null;
if (!$sel_day) {
    $sel_day = ($year === $today_year && $month === $today_month) ? $today_num : 1;
}
$sel_date_str = sprintf('%04d-%02d-%02d', $year, $month, $sel_day);

// =====================================================================
// Slot data untuk tanggal terpilih
// =====================================================================
$fixedSlots = ['08:00','09:00','10:00','11:00','13:00','14:00','15:00'];
$qSlots = $conn->prepare(
    "SELECT j.jam FROM jadwal j JOIN pesanan p ON p.id=j.pesanan_id
     WHERE j.tanggal=? AND p.status != 'batal' AND j.status != 'dibatalkan'"
);
$qSlots->bind_param('s', $sel_date_str); $qSlots->execute();
$takenJam = array_column($qSlots->get_result()->fetch_all(MYSQLI_ASSOC), 'jam');
$qSlots->close();
$slot_data_sel = [];
foreach ($fixedSlots as $slot) {
    $slot_data_sel[$slot] = in_array($slot, $takenJam) ? 'ada_pesanan' : 'tersedia';
}

// =====================================================================
// Pesanan per tanggal terpilih
// =====================================================================
$qPes = $conn->prepare(
    "SELECT p.kode AS id, c.nama AS customer, p.layanan, j.jam, p.status
     FROM pesanan p
     JOIN customers c ON c.id = p.customer_id
     JOIN jadwal j ON j.pesanan_id = p.id
     WHERE j.tanggal = ? AND p.layanan LIKE '%piercing%'
     ORDER BY j.jam"
);
$qPes->bind_param('s', $sel_date_str); $qPes->execute();
$pesanan_sel = $qPes->get_result()->fetch_all(MYSQLI_ASSOC);
$qPes->close();

$label_map = ['baru' => 'Baru', 'diproses' => 'Diproses', 'selesai' => 'Selesai', 'batal' => 'Batal'];
$hari_ini_pesanan = count($pesanan_sel);

$bulan_nama  = ['','Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
$hari_pendek = ['Min','Sen','Sel','Rab','Kam','Jum','Sab'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Jadwal - Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"/>
    <link rel="stylesheet" href="../css-admin/admin_dashboard.css?v=2">
    <link rel="stylesheet" href="../css-admin/responsive_unified.css?v=2">
    <link rel="stylesheet" href="../css-admin/kelola_jadwal.css">
    <link rel="stylesheet" href="../css-admin/modal_global.css">
    <script src="../js-admin/modal_global.js"></script>
</head>
<body>

<div class="admin-wrapper">

    <!-- Sidebar -->
    <aside class="admin-sidebar">
        <div class="sidebar-logo">
            <img src="../gambar/logo2.jpeg" alt="Logo">
            <span>@sphinx_piercing</span>
        </div>
        <ul class="sidebar-menu">
            <li><a href="Dashboard-admin.php"><i class="fa-solid fa-house"></i><span>Dashboard</span></a></li>
            <li><a href="Customer-admin.php"><i class="fa-solid fa-users"></i><span>Customer</span></a></li>
            <li><a href="Produk-admin.php"><i class="fa-solid fa-box-open"></i><span>Produk</span></a></li>
            <li class="active"><a href="KelolaJadwal-admin.php"><i class="fa-solid fa-calendar-days"></i><span>Kelola Jadwal</span></a></li>
            <li><a href="KelolaPesanan-admin.php"><i class="fa-solid fa-cube"></i><span>Kelola Pesanan</span><?php if ($pending_pesanan_count > 0): ?><span class="sidebar-badge"><?= $pending_pesanan_count ?></span><?php endif; ?></a></li>
        </ul>
        <div class="sidebar-bottom">
            <a href="#" class="sidebar-bantuan"><i class="fa-solid fa-circle-question"></i><span>Bantuan</span></a>
            <a href="logout-admin.php" class="sidebar-logout"><i class="fa-solid fa-right-from-bracket"></i><span>Logout</span></a>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="admin-main">

        <!-- Top Bar -->
        <div class="admin-topbar">
            <div class="topbar-title">
                <h1>Kelola Jadwal</h1>
                <span class="breadcrumb">Dashboard &gt; Kelola Jadwal</span>
            </div>
            <div class="topbar-icons">
                <a href="Notifikasi-admin.php" class="icon-btn" title="Notifikasi"><i class="fa-regular fa-bell"></i></a>
                <a href="Profile-admin.php" class="icon-btn" title="Profil"><i class="fa-regular fa-user"></i></a>
            </div>
        </div>

        <!-- Banner -->
        <div class="admin-banner">
            <a href="KelolaJadwal-admin.php">Kelola Jadwal</a>
        </div>

        <!-- Stats -->
        <div class="jadwal-stats">
            <div class="jadwal-stat-card">
                <div class="jsc-icon green"><i class="fa-solid fa-calendar-check"></i></div>
                <div class="jsc-info">
                    <span class="jsc-label">Total Jadwal</span>
                    <span class="jsc-value"><?= $total_jadwal ?></span>
                </div>
            </div>
            <div class="jadwal-stat-card">
                <div class="jsc-icon blue"><i class="fa-regular fa-calendar"></i></div>
                <div class="jsc-info">
                    <span class="jsc-label">Hari Ini</span>
                    <span class="jsc-value"><?= $hari_ini_pesanan ?></span>
                </div>
            </div>
            <div class="jadwal-stat-card">
                <div class="jsc-icon red"><i class="fa-solid fa-ban"></i></div>
                <div class="jsc-info">
                    <span class="jsc-label">Slot Penuh</span>
                    <span class="jsc-value"><?= $slot_penuh_count ?></span>
                </div>
            </div>
        </div>

        <!-- Calendar Card -->
        <div class="kalender-card">
            <div class="kal-nav-header">
                <h2><i class="fa-regular fa-calendar"></i> PILIH TANGGAL</h2>
                <div class="kal-month-nav">
                    <button class="kal-nav-btn" onclick="gantibulan(-1)" title="Bulan sebelumnya"><i class="fa-solid fa-chevron-left" style="font-size:10px;"></i></button>
                    <span id="kal-month-label"></span>
                    <button class="kal-nav-btn" onclick="gantibulan(1)" title="Bulan berikutnya"><i class="fa-solid fa-chevron-right" style="font-size:10px;"></i></button>
                </div>
            </div>

            <!-- Day headers + grid rendered by JS -->
            <div id="kal-grid-wrap"></div>

            <!-- Legend -->
            <div class="legend">
                <div class="legend-item"><span class="legend-dot green"></span> Ada pesanan</div>
                <div class="legend-item"><span class="legend-dot red"></span> Slot Penuh</div>
                <div class="legend-item"><span class="legend-dot purple"></span> Hari ini</div>
                <div class="legend-item"><span class="legend-dot lime"></span> Dipilih</div>
            </div>
        </div>

        <!-- Slot & Order Section -->
        <div class="slot-card" id="slot-section">

            <!-- Slot Waktu -->
            <div>
                <div class="slot-header-title">
                    <i class="fa-regular fa-clock"></i>
                    SLOT WAKTU <span class="slot-date-highlight" id="slot-tgl-label"><?= $sel_day . ' ' . strtoupper($bulan_nama[$month]) ?></span>
                </div>
                <div class="slot-grid" id="slot-grid" style="margin-top:14px;">
                    <!-- Rendered by JS -->
                </div>
            </div>

            <!-- Pesanan Terhubung -->
            <div class="pesanan-linked-box">
                <div id="pending-assign-banner" style="display:none;margin-bottom:12px;padding:10px 12px;border:1px solid rgba(127,255,0,0.35);background:rgba(127,255,0,0.08);border-radius:10px;color:#d7ffd0;font-size:13px;"></div>
                <div class="pesanan-linked-title">
                    <i class="fa-solid fa-link"></i>
                    <span id="pesanan-linked-title">Pesanan Jasa Piercing masuk <?= $sel_day . ' ' . $bulan_nama[$month] ?> (<span id="pesanan-count"><?= count($pesanan_sel) ?></span> pesanan)</span>
                </div>
                <div id="pesanan-linked-list">
                    <!-- Rendered by JS -->
                </div>
                <div class="customer-block-panel">
                    <div class="customer-block-title">
                        <i class="fa-solid fa-user-lock"></i>
                        <span>Pilih Customer untuk Diblokir</span>
                    </div>
                    <div class="customer-block-help">Klik salah satu customer aktif di tanggal ini, lalu tekan blokir untuk menghapus semua jadwal aktif customer tersebut.</div>
                    <div id="customer-block-list" class="customer-block-list"></div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="jadwal-action-row">
                <button class="btn-blokir-slot" onclick="blokirTanggal()">
                    <i class="fa-solid fa-calendar-xmark"></i> Blokir Tanggal
                </button>
                <button class="btn-blokir-slot" onclick="blokirCustomer()">
                    <i class="fa-solid fa-user-lock"></i> Blokir Customer
                </button>
                <button class="btn-konfirm-semua" onclick="konfirmasiSemua()">
                    <i class="fa-solid fa-check"></i> Konfirmasi Semua
                </button>
            </div>

        </div>

    </main>
</div>

<script>
// ===== DATA AWAL DARI PHP (dari database) =====
const phpJadwalDates  = <?= json_encode($jadwal_dates, JSON_UNESCAPED_UNICODE) ?>;
const phpSlotData     = { <?= $sel_day ?>: <?= json_encode($slot_data_sel, JSON_UNESCAPED_UNICODE) ?> };
const phpPesananData  = { <?= $sel_day ?>: <?= json_encode($pesanan_sel, JSON_UNESCAPED_UNICODE) ?> };
const defaultSlots    = <?= json_encode(array_fill_keys(['08:00','09:00','10:00','11:00','13:00','14:00','15:00'],'tersedia'), JSON_UNESCAPED_UNICODE) ?>;
const labelMap        = <?= json_encode($label_map, JSON_UNESCAPED_UNICODE) ?>;
const todayNum        = <?= $sel_day ?>;
const todayMonth      = <?= $month ?>;
const todayYear       = <?= $year ?>;
const currentUrl      = 'KelolaJadwal-admin.php';
const PENDING_ASSIGN_KEY = 'sphinx_pending_jadwal_assign_v1';
const BLOCKED_CUSTOMERS_KEY = 'sphinx_blocked_customers_v1';
const EXTRA_NOTIF_KEY = 'sphinx_notifikasi_tambahan_v1';

// ===== MULTI-MONTH DATA (localStorage backed) =====
const STORAGE_KEY = 'sphinx_jadwal_v1';
let db = {}; // { "2026-6": { jadwalDates:{}, slotData:{}, pesananData:{} } }

function dbKey(y, m) { return `${y}-${m}`; }

function loadDb() {
    try { db = JSON.parse(localStorage.getItem(STORAGE_KEY) || '{}'); } catch(e) { db = {}; }
    // Seed June 2026 dari PHP jika belum ada
    const k = dbKey(todayYear, todayMonth);
    if (!db[k]) {
        db[k] = {
            jadwalDates: phpJadwalDates,
            slotData:    phpSlotData,
            pesananData: phpPesananData
        };
        saveDb();
    }
}

function saveDb() {
    localStorage.setItem(STORAGE_KEY, JSON.stringify(db));
}

function getMonthDb(y, m) {
    const k = dbKey(y, m);
    if (!db[k]) db[k] = { jadwalDates: {}, slotData: {}, pesananData: {} };
    return db[k];
}

function normalizeCustomerName(name) {
    return String(name || '').trim().toLowerCase();
}

function loadBlockedCustomers() {
    try {
        blockedCustomers = JSON.parse(localStorage.getItem(BLOCKED_CUSTOMERS_KEY) || '[]');
    } catch (e) {
        blockedCustomers = [];
    }
    if (!Array.isArray(blockedCustomers)) blockedCustomers = [];
    blockedCustomers = blockedCustomers.map(function(name) {
        return String(name || '').trim();
    }).filter(Boolean);
}

function saveBlockedCustomers() {
    localStorage.setItem(BLOCKED_CUSTOMERS_KEY, JSON.stringify(blockedCustomers));
}

function isBlockedCustomer(name) {
    const target = normalizeCustomerName(name);
    if (!target) return false;
    return blockedCustomers.some(function(item) {
        return normalizeCustomerName(item) === target;
    });
}

function removeCustomerFromMonthDb(mdb, customerName) {
    if (!mdb || !mdb.pesananData) return false;

    let changed = false;
    Object.keys(mdb.pesananData).forEach(function(tglKey) {
        const arr = Array.isArray(mdb.pesananData[tglKey]) ? mdb.pesananData[tglKey] : [];
        const filtered = arr.filter(function(p) {
            return normalizeCustomerName(p.customer) !== normalizeCustomerName(customerName);
        });

        if (filtered.length !== arr.length) {
            changed = true;
            if (filtered.length === 0) {
                delete mdb.pesananData[tglKey];
            } else {
                mdb.pesananData[tglKey] = filtered;
            }
            syncSlotAndDateStatus(mdb, tglKey);
        }
    });

    return changed;
}

function applyBlockedCustomersToDb() {
    let changed = false;
    blockedCustomers.forEach(function(customerName) {
        Object.keys(db).forEach(function(key) {
            changed = removeCustomerFromMonthDb(db[key], customerName) || changed;
        });
    });

    if (changed) saveDb();
}

// ===== CALENDAR STATE =====
let curYear  = todayYear;
let curMonth = todayMonth;
let selectedTgl  = todayNum;
let selectedSlot = null;
let pendingAssign = null;
let blockedCustomers = [];
let selectedCustomerToBlock = null;

const bulanNama  = ['','Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
const hariPendek = ['Min','Sen','Sel','Rab','Kam','Jum','Sab'];

// ===== RENDER CALENDAR =====
function renderKalender() {
    const mdb = getMonthDb(curYear, curMonth);
    const jadwalDates = mdb.jadwalDates;

    // Header month label
    document.getElementById('kal-month-label').textContent = bulanNama[curMonth] + ' ' + curYear;

    // Days in month & first day of week
    const firstDay   = new Date(curYear, curMonth - 1, 1).getDay(); // 0=Sun
    const daysInMonth = new Date(curYear, curMonth, 0).getDate();

    let html = '<div class="kal-grid">';
    hariPendek.forEach(h => { html += `<div class="kal-day-header">${h}</div>`; });

    // Empty leading cells
    for (let e = 0; e < firstDay; e++) html += '<div class="kal-cell empty"></div>';

    for (let d = 1; d <= daysInMonth; d++) {
        const status  = jadwalDates[d] ?? 'normal';
        const isToday = (d === todayNum && curMonth === todayMonth && curYear === todayYear);
        const isSel   = (d === selectedTgl && curMonth === curMonth);

        let cls = 'kal-cell';
        if (status === 'ada_pesanan') cls += ' ada-pesanan';
        else if (status === 'slot_penuh') cls += ' slot-penuh';
        if (isToday) cls += ' hari-ini';
        if (d === selectedTgl) cls += ' dipilih';

        html += `<div class="${cls}" data-tgl="${d}" onclick="pilihTanggal(${d})">${d}</div>`;
    }

    html += '</div>';
    document.getElementById('kal-grid-wrap').innerHTML = html;
}

function gantibulan(delta) {
    curMonth += delta;
    if (curMonth > 12) { curMonth = 1; curYear++; }
    if (curMonth < 1)  { curMonth = 12; curYear--; }
    selectedTgl  = (curYear === todayYear && curMonth === todayMonth) ? todayNum : 1;
    selectedSlot = null;
    selectedCustomerToBlock = null;
    renderKalender();
    renderSlotGrid(selectedTgl);
    renderPesanan(selectedTgl);
}

// ===== PILIH TANGGAL =====
function pilihTanggal(tgl) {
    selectedTgl  = tgl;
    selectedSlot = null;
    selectedCustomerToBlock = null;
    renderKalender();
    renderSlotGrid(tgl);
    renderPesanan(tgl);
    document.getElementById('slot-tgl-label').textContent = tgl + ' ' + bulanNama[curMonth].toUpperCase() + ' ' + curYear;
    document.getElementById('slot-section').scrollIntoView({ behavior: 'smooth', block: 'start' });
}

// ===== RENDER SLOT GRID (informational only) =====
function renderSlotGrid(tgl) {
    const mdb   = getMonthDb(curYear, curMonth);
    const slots = mdb.slotData[tgl] ?? defaultSlots;
    const grid  = document.getElementById('slot-grid');
    let html    = '';
    for (const [jam, status] of Object.entries(slots)) {
        const cls = status.replace(/_/g, '-');
        const selected = selectedSlot === jam ? ' selected' : '';
        html += `<button class="slot-btn ${cls}${selected}" data-jam="${jam}" onclick="pilihSlot('${jam}')">${jam}</button>`;
    }
    grid.innerHTML = html;
}

function loadPendingAssign() {
    try {
        pendingAssign = JSON.parse(localStorage.getItem(PENDING_ASSIGN_KEY) || 'null');
    } catch (e) {
        pendingAssign = null;
    }
    if (!pendingAssign || !pendingAssign.orderId) {
        pendingAssign = null;
        return;
    }
    if (isBlockedCustomer(pendingAssign.customer)) {
        localStorage.removeItem(PENDING_ASSIGN_KEY);
        pendingAssign = null;
    }
}

function saveExtraNotification(notif) {
    let list = [];
    try { list = JSON.parse(localStorage.getItem(EXTRA_NOTIF_KEY) || '[]'); } catch(e) { list = []; }
    list.unshift(notif);
    localStorage.setItem(EXTRA_NOTIF_KEY, JSON.stringify(list));
}

function updatePendingBanner() {
    const el = document.getElementById('pending-assign-banner');
    const actionBtn = document.querySelector('.btn-konfirm-semua');
    if (!el || !actionBtn) return;

    if (!pendingAssign) {
        el.style.display = 'none';
        el.textContent = '';
        actionBtn.innerHTML = '<i class="fa-solid fa-check"></i> Konfirmasi Semua';
        return;
    }

    el.style.display = 'block';
    el.textContent = 'Penjadwalan pending untuk ' + pendingAssign.orderId + ' (' + pendingAssign.customer + '). Pilih tanggal dan slot sesuai pilihan customer, lalu klik Konfirmasi Jadwal Customer.';
    actionBtn.innerHTML = '<i class="fa-solid fa-check"></i> Konfirmasi Jadwal Customer';
}

function applyRequestedDateFromPending() {
    if (!pendingAssign || !pendingAssign.requestedTanggal) return;
    const req = pendingAssign.requestedTanggal;
    if (!req || !req.tanggal || !req.bulan || !req.tahun) return;
    curYear = req.tahun;
    curMonth = req.bulan;
    selectedTgl = req.tanggal;
    selectedSlot = pendingAssign.requestedJam || null;
}

function pilihSlot(jam) {
    const mdb = getMonthDb(curYear, curMonth);
    const slots = mdb.slotData[selectedTgl] ?? defaultSlots;
    const status = slots[jam] || 'tersedia';

    if (pendingAssign && status !== 'tersedia') {
        SphinxModal.konfirmasi(
            'Slot Sudah Terisi',
            'Slot ' + jam + ' pada ' + selectedTgl + ' ' + bulanNama[curMonth] + ' sudah terisi/penuh. Kirim notifikasi ke customer agar memilih jadwal lain?',
            () => {
                saveExtraNotification({
                    tipe: 'info',
                    label: 'Info',
                    pesan: 'Notifikasi terkirim ke ' + pendingAssign.customer + ' (' + pendingAssign.orderId + '): slot ' + jam + ' pada ' + selectedTgl + ' ' + bulanNama[curMonth] + ' sudah terisi, diminta pilih jadwal lain.',
                    waktu: 'Baru saja',
                    baru: true,
                    createdAt: Date.now()
                });
                SphinxModal.berhasil('Notifikasi berhasil dikirim ke customer.');
            }
        );
        return;
    }

    selectedSlot = jam;
    renderSlotGrid(selectedTgl);
}

function syncSlotAndDateStatus(mdb, tgl) {
    const key = String(tgl);
    const slots = mdb.slotData[key];
    const orders = Array.isArray(mdb.pesananData[key]) ? mdb.pesananData[key] : [];

    if (slots) {
        Object.keys(slots).forEach(function(jam) {
            if (slots[jam] === 'slot_penuh') return;
            const used = orders.some(function(p) { return p.jam === jam; });
            slots[jam] = used ? 'ada_pesanan' : 'tersedia';
        });

        const allBlockedOrBooked = Object.values(slots).every(function(s) {
            return s === 'slot_penuh' || s === 'ada_pesanan';
        });
        const hasAnyBooked = Object.values(slots).some(function(s) { return s === 'ada_pesanan'; });

        if (allBlockedOrBooked) {
            mdb.jadwalDates[key] = 'slot_penuh';
        } else if (hasAnyBooked) {
            mdb.jadwalDates[key] = 'ada_pesanan';
        } else {
            delete mdb.jadwalDates[key];
        }
        return;
    }

    if (orders.length > 0) {
        mdb.jadwalDates[key] = 'ada_pesanan';
    } else {
        delete mdb.jadwalDates[key];
    }
}

function removeOrderFromMonthDb(mdb, orderId) {
    Object.keys(mdb.pesananData).forEach(function(tglKey) {
        const arr = Array.isArray(mdb.pesananData[tglKey]) ? mdb.pesananData[tglKey] : [];
        const filtered = arr.filter(function(p) { return p.id !== orderId; });

        if (filtered.length === 0) {
            delete mdb.pesananData[tglKey];
        } else {
            mdb.pesananData[tglKey] = filtered;
        }

        syncSlotAndDateStatus(mdb, tglKey);
    });
}

function removeOrderFromAllMonths(orderId) {
    Object.keys(db).forEach(function(key) {
        removeOrderFromMonthDb(db[key], orderId);
    });
}

// ===== RENDER PESANAN =====
function renderPesanan(tgl) {
    const mdb  = getMonthDb(curYear, curMonth);
    const list = mdb.pesananData[tgl] ?? [];
    const count = list.length;

    document.getElementById('pesanan-linked-title').innerHTML =
        `Pesanan Jasa Piercing masuk ${tgl} ${bulanNama[curMonth]} ${curYear} (<span id="pesanan-count">${count}</span> pesanan)`;

    const container = document.getElementById('pesanan-linked-list');
    if (count === 0) {
        container.innerHTML = `<div class="pesanan-linked-empty">
            <i class="fa-regular fa-calendar-xmark" style="margin-right:6px;"></i>
            Tidak ada pesanan Jasa Piercing pada tanggal ini.
        </div>`;
        renderCustomerBlockList([]);
        return;
    }

    let html = '';
    for (const p of list) {
        const badge = labelMap[p.status] ?? p.status;
        html += `
        <div class="order-card-jadwal">
            <div class="ocj-header">
                <span class="ocj-id">#${p.id}</span>
                <span class="badge ${p.status}">${badge}</span>
            </div>
            <div class="ocj-info">
                <span>Customer: <strong>${escHtml(p.customer)}</strong></span>
                <span>Layanan: <strong>Jasa Piercing</strong></span>
                <span>Ambil <strong>${escHtml(p.jam)}</strong></span>
            </div>
            <div class="ocj-link" style="display:flex;justify-content:space-between;align-items:center;">
                <a href="KelolaPesanan-admin.php?id=${escHtml(p.id)}">
                    <i class="fa-solid fa-arrow-up-right-from-square" style="font-size:11px;"></i>
                    Lihat detail pesanan &rarr;
                </a>
            </div>
        </div>`;
    }

    container.innerHTML = html;
    renderCustomerBlockList(list);
}

function renderCustomerBlockList(list) {
    const container = document.getElementById('customer-block-list');
    if (!container) return;

    const map = new Map();
    list.forEach(function(p) {
        if (!p || !p.customer || isBlockedCustomer(p.customer)) return;
        const key = normalizeCustomerName(p.customer);
        if (!map.has(key)) {
            map.set(key, { name: p.customer, count: 0 });
        }
        map.get(key).count += 1;
    });

    const customers = Array.from(map.values());
    if (selectedCustomerToBlock && !customers.some(function(item) {
        return normalizeCustomerName(item.name) === normalizeCustomerName(selectedCustomerToBlock);
    })) {
        selectedCustomerToBlock = null;
    }

    if (customers.length === 0) {
        container.innerHTML = '<div class="customer-block-empty">Tidak ada customer aktif yang bisa dipilih untuk diblokir pada tanggal ini.</div>';
        return;
    }

    container.innerHTML = customers.map(function(item) {
        const selected = normalizeCustomerName(item.name) === normalizeCustomerName(selectedCustomerToBlock) ? ' selected' : '';
        return '<button type="button" class="customer-chip' + selected + '" data-customer="' + escHtml(item.name) + '">'
            + '<span>' + escHtml(item.name) + '</span>'
            + '<small>' + item.count + ' pesanan aktif</small>'
            + '</button>';
    }).join('');
}

function pilihCustomerUntukBlokir(customerName) {
    selectedCustomerToBlock = customerName;
    renderCustomerBlockList(getMonthDb(curYear, curMonth).pesananData[selectedTgl] ?? []);
}

// ===== ACTION BUTTONS =====
function blokirTanggal() {
    const mdb = getMonthDb(curYear, curMonth);
    SphinxModal.konfirmasi(
        'Blokir Tanggal',
        'Blokir tanggal ' + selectedTgl + ' ' + bulanNama[curMonth] + ' ' + curYear + '? Semua slot pada tanggal ini akan ditutup.',
        () => {
            const blockedSlots = {};
            Object.keys(defaultSlots).forEach(function(jam) { blockedSlots[jam] = 'slot_penuh'; });
            mdb.slotData[selectedTgl] = blockedSlots;
            mdb.jadwalDates[selectedTgl] = 'slot_penuh';
            saveDb();
            selectedSlot = null;
            renderKalender();
            renderSlotGrid(selectedTgl);
            renderPesanan(selectedTgl);
            SphinxModal.berhasil('Tanggal ' + selectedTgl + ' ' + bulanNama[curMonth] + ' ' + curYear + ' berhasil diblokir.');
        }
    );
}

function blokirCustomer() {
    const customerName = selectedCustomerToBlock;
    if (!customerName) {
        SphinxModal.peringatan('Pilih salah satu customer terlebih dahulu sebelum melakukan blokir.');
        return;
    }

    SphinxModal.konfirmasi(
        'Blokir Customer',
        'Blokir customer ' + customerName + ' dari semua jadwal aktif? Semua pesanan aktif customer ini akan dihapus dari jadwal.',
        () => {
            if (!isBlockedCustomer(customerName)) {
                blockedCustomers.push(customerName);
                saveBlockedCustomers();
            }

            Object.keys(db).forEach(function(key) {
                removeCustomerFromMonthDb(db[key], customerName);
            });

            if (pendingAssign && normalizeCustomerName(pendingAssign.customer) === normalizeCustomerName(customerName)) {
                localStorage.removeItem(PENDING_ASSIGN_KEY);
                pendingAssign = null;
                updatePendingBanner();
            }

            saveDb();
            selectedCustomerToBlock = null;
            renderKalender();
            renderSlotGrid(selectedTgl);
            renderPesanan(selectedTgl);
            SphinxModal.berhasil('Customer ' + customerName + ' berhasil diblokir dari jadwal aktif.');
        }
    );
}

function konfirmasiSemua() {
    const mdb  = getMonthDb(curYear, curMonth);
    if (pendingAssign) {
        if (isBlockedCustomer(pendingAssign.customer)) {
            SphinxModal.peringatan('Customer ini sudah diblokir, sehingga jadwalnya tidak bisa dikonfirmasi lagi.');
            return;
        }

        if (!selectedSlot) {
            SphinxModal.peringatan('Pilih slot waktu terlebih dahulu untuk pesanan customer.');
            return;
        }

        const slots = mdb.slotData[selectedTgl] ?? { ...defaultSlots };
        if (slots[selectedSlot] !== 'tersedia') {
            SphinxModal.konfirmasi(
                'Slot Tidak Tersedia',
                'Slot yang dipilih sudah terisi. Kirim notifikasi ke customer untuk pilih jadwal lain?',
                () => {
                    saveExtraNotification({
                        tipe: 'info',
                        label: 'Info',
                        pesan: 'Notifikasi terkirim ke ' + pendingAssign.customer + ' (' + pendingAssign.orderId + '): slot yang dipilih customer sudah terisi.',
                        waktu: 'Baru saja',
                        baru: true,
                        createdAt: Date.now()
                    });
                    SphinxModal.berhasil('Notifikasi berhasil dikirim ke customer.');
                }
            );
            return;
        }

        SphinxModal.konfirmasi(
            'Konfirmasi Jadwal Customer',
            'Tetapkan ' + pendingAssign.orderId + ' ke tanggal ' + selectedTgl + ' ' + bulanNama[curMonth] + ' ' + curYear + ' jam ' + selectedSlot + '?',
            () => {
                removeOrderFromAllMonths(pendingAssign.orderId);
                if (!mdb.slotData[selectedTgl]) mdb.slotData[selectedTgl] = { ...defaultSlots };
                mdb.slotData[selectedTgl][selectedSlot] = 'ada_pesanan';
                if (!mdb.pesananData[selectedTgl]) mdb.pesananData[selectedTgl] = [];
                mdb.pesananData[selectedTgl].push({
                    id: pendingAssign.orderId,
                    customer: pendingAssign.customer,
                    layanan: 'Jasa Piercing',
                    jam: selectedSlot,
                    status: 'diproses'
                });
                syncSlotAndDateStatus(mdb, selectedTgl);
                saveDb();
                localStorage.removeItem(PENDING_ASSIGN_KEY);
                pendingAssign = null;
                updatePendingBanner();
                renderKalender();
                renderSlotGrid(selectedTgl);
                renderPesanan(selectedTgl);
                SphinxModal.berhasil('Jadwal customer berhasil dikonfirmasi.');
            }
        );
        return;
    }

    const list = mdb.pesananData[selectedTgl] ?? [];
    if (list.length === 0) {
        SphinxModal.peringatan('Tidak ada pesanan untuk dikonfirmasi.');
        return;
    }
    SphinxModal.konfirmasi(
        'Konfirmasi Semua',
        'Konfirmasi semua ' + list.length + ' pesanan pada ' + selectedTgl + ' ' + bulanNama[curMonth] + ' ' + curYear + '?',
        () => {
            mdb.pesananData[selectedTgl] = list.map(function(p) { return { ...p, status: 'selesai' }; });
            saveDb();
            renderPesanan(selectedTgl);
            SphinxModal.berhasil('Semua pesanan berhasil dikonfirmasi!');
        }
    );
}

function escHtml(str) {
    return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

const customerBlockListEl = document.getElementById('customer-block-list');
if (customerBlockListEl) {
    customerBlockListEl.addEventListener('click', function(e) {
        const chip = e.target.closest('.customer-chip');
        if (!chip) return;
        pilihCustomerUntukBlokir(chip.dataset.customer || '');
    });
}

// ===== INIT =====
loadDb();
loadBlockedCustomers();
applyBlockedCustomersToDb();
loadPendingAssign();
applyRequestedDateFromPending();
renderKalender();
renderSlotGrid(selectedTgl);
renderPesanan(selectedTgl);
updatePendingBanner();
document.getElementById('slot-tgl-label').textContent =
    selectedTgl + ' ' + bulanNama[curMonth].toUpperCase() + ' ' + curYear;
</script>

</body>
</html>
