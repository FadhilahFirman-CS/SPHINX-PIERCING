<?php
require_once __DIR__ . '/auth_admin.php';
require_once __DIR__ . '/pesanan_helper.php';

// =====================================================================
// AJAX: CRUD produk
// =====================================================================
if (isset($_POST['ajax_action'])) {
    header('Content-Type: application/json');

    if ($_POST['ajax_action'] === 'save') {
        $kode     = trim($_POST['kode']     ?? '');
        $nama     = trim($_POST['nama']     ?? '');
        $kategori = trim($_POST['kategori'] ?? '');
        $harga    = max(0, (int)($_POST['harga'] ?? 0));
        $stok     = max(0, (int)($_POST['stok']  ?? 0));
        if (!$kode || !$nama) { echo json_encode(['success'=>false,'message'=>'Kode dan nama wajib diisi.']); exit; }

        // Cek apakah update atau insert
        $exists = $conn->prepare("SELECT id FROM produk WHERE kode=? LIMIT 1");
        $exists->bind_param('s', $kode); $exists->execute();
        $existRow = $exists->get_result()->fetch_assoc(); $exists->close();

        if ($existRow) {
            $su = $conn->prepare("UPDATE produk SET nama=?,kategori=?,harga=?,stok=? WHERE kode=?");
            $su->bind_param('ssiss', $nama,$kategori,$harga,$stok,$kode);
            $ok = $su->execute(); $su->close();
        } else {
            $si = $conn->prepare("INSERT INTO produk (kode,nama,kategori,harga,stok,is_active) VALUES (?,?,?,?,?,1)");
            $si->bind_param('sssii', $kode,$nama,$kategori,$harga,$stok);
            $ok = $si->execute(); $si->close();
        }
        echo json_encode(['success'=>(bool)$ok]);
        exit;
    }

    if ($_POST['ajax_action'] === 'delete') {
        $kode = trim($_POST['kode'] ?? '');
        if (!$kode) { echo json_encode(['success'=>false]); exit; }
        $sd = $conn->prepare("UPDATE produk SET is_active=0 WHERE kode=?");
        $sd->bind_param('s', $kode); $ok = $sd->execute(); $sd->close();
        echo json_encode(['success'=>(bool)$ok]);
        exit;
    }
}

// =====================================================================
// Query produk dari DB
// =====================================================================
$search = trim($_GET['search'] ?? '');
$kategori_list = ['Jewelry','Jasa','Perawatan','Aksesori'];

$whereProd = ['is_active = 1'];
$pParams = []; $pTypes = '';
if ($search) {
    $whereProd[] = '(nama LIKE ? OR kode LIKE ? OR kategori LIKE ?)';
    $s = "%{$search}%";
    $pParams = [$s,$s,$s]; $pTypes = 'sss';
}
$sqlProd = "SELECT kode,nama,kategori,harga,stok FROM produk WHERE " . implode(' AND ', $whereProd) . " ORDER BY kode";
$stmtProd = $conn->prepare($sqlProd);
if ($pTypes) $stmtProd->bind_param($pTypes, ...$pParams);
$stmtProd->execute();
$produk_list = $stmtProd->get_result()->fetch_all(MYSQLI_ASSOC);
$stmtProd->close();

function formatRp($n) { return 'Rp ' . number_format($n, 0, ',', '.'); }
function stokClass($stok) {
    if ($stok === 0) return 'rendah';
    if ($stok <= 5)  return 'cukup';
    return 'aman';
}
function statusFromStok(int $stok): string {
    if ($stok === 0)  return 'habis';
    if ($stok <= 5)   return 'sedikit';
    return 'aktif';
}
$icon_kat = ['Jewelry'=>'fa-ring','Jasa'=>'fa-hand-sparkles','Perawatan'=>'fa-spray-can','Aksesori'=>'fa-gem'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Produk - Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"/>
    <link rel="stylesheet" href="../css-admin/admin_dashboard.css?v=2">
    <link rel="stylesheet" href="../css-admin/responsive_unified.css?v=2">
    <link rel="stylesheet" href="../css-admin/produk.css?v=2">
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
            <li class="active"><a href="Produk-admin.php"><i class="fa-solid fa-box-open"></i><span>Produk</span></a></li>
            <li><a href="KelolaJadwal-admin.php"><i class="fa-solid fa-calendar-days"></i><span>Kelola Jadwal</span></a></li>
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
                <h1>Produk</h1>
                <span class="breadcrumb">Dashboard &gt; Produk</span>
            </div>
            <div class="topbar-icons">
                <a href="Notifikasi-admin.php" class="icon-btn" title="Notifikasi"><i class="fa-regular fa-bell"></i></a>
                <a href="Profile-admin.php" class="icon-btn" title="Profil"><i class="fa-regular fa-user"></i></a>
            </div>
        </div>

        <!-- Banner -->
        <div class="admin-banner">
            <a href="Produk-admin.php">Kelola Produk</a>
        </div>

        <!-- Produk Card -->
        <div class="produk-card">
            <div class="produk-card-header">
                <h2><i class="fa-solid fa-box-open"></i> Daftar Produk Saat Ini</h2>
                <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;">
                    <form class="search-produk" method="GET" style="margin:0;">
                        <input type="text" name="search" placeholder="Cari produk..." value="<?= htmlspecialchars($search) ?>">
                    </form>
                    <button class="btn-tambah-produk" onclick="bukaModal('tambah')">
                        <i class="fa-solid fa-plus"></i> Tambah Produk
                    </button>
                </div>
            </div>

            <div class="produk-table-wrap">
                <table class="produk-tbl">
                    <thead>
                        <tr>
                            <th>Foto</th>
                            <th>Kode</th>
                            <th>Nama Produk</th>
                            <th>Kategori</th>
                            <th>Harga</th>
                            <th>Stok</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($produk_list)): ?>
                        <tr><td colspan="8" style="text-align:center;color:var(--text-soft);padding:30px;">Tidak ada produk ditemukan.</td></tr>
                        <?php else: foreach ($produk_list as $idx => $p): ?>
                        <tr>
                            <td>
                                <div class="produk-foto">
                                    <?php if ($p['foto']): ?>
                                        <img src="<?= htmlspecialchars($p['foto']) ?>" alt="">
                                    <?php else: ?>
                                        <i class="fa-solid <?= $icon_kat[$p['kategori']] ?? 'fa-box' ?>"></i>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="kode-cell"><?= htmlspecialchars($p['kode']) ?></td>
                            <td style="font-weight:500;"><?= htmlspecialchars($p['nama']) ?></td>
                            <td>
                                <span style="font-size:12px;color:var(--text-soft);"><?= htmlspecialchars($p['kategori']) ?></span>
                            </td>
                            <td class="harga-cell"><?= formatRp($p['harga']) ?></td>
                            <td>
                                <span class="stok-cell <?= stokClass($p['stok']) ?>">
                                    <?= $p['stok'] ?>
                                </span>
                            </td>
                            <td>
                                <?php $dispStatus = statusFromStok($p['stok']); ?>
                                <span class="badge <?= htmlspecialchars($dispStatus) ?>">
                                    <?= ucfirst($dispStatus) ?>
                                </span>
                            </td>
                            <td>
                                <div class="aksi-btns">
                                    <button class="btn-edit-kecil" title="Edit"
                                        onclick="bukaModal('edit',<?= $idx ?>)"
                                    ><i class="fa-solid fa-pen"></i></button>
                                    <button class="btn-hapus-kecil" title="Hapus"
                                        onclick="bukaKonfirmHapus(<?= $idx ?>,'<?= htmlspecialchars($p['nama']) ?>')"
                                    ><i class="fa-solid fa-trash"></i></button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </main>
</div>

<!-- ===== MODAL TAMBAH / EDIT ===== -->
<div class="modal-overlay" id="modal-produk">
    <div class="modal-box">
        <div class="modal-header">
            <span class="modal-title" id="modal-produk-title">
                <i class="fa-solid fa-box-open"></i> Tambah Produk
            </span>
            <button class="modal-close" onclick="tutupModal('modal-produk')">&times;</button>
        </div>

        <div class="modal-grid">
            <div class="form-field">
                <label>Nama Produk</label>
                <input type="text" id="inp-nama" placeholder="Nama produk...">
            </div>
            <div class="form-field">
                <label>Harga (Rp)</label>
                <input type="number" id="inp-harga" placeholder="50000" min="0">
            </div>
            <div class="form-field">
                <label>Kategori</label>
                <select id="inp-kategori">
                    <?php foreach ($kategori_list as $kat): ?>
                    <option value="<?= $kat ?>"><?= $kat ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-field">
                <label>Stok</label>
                <input type="number" id="inp-stok" placeholder="0" min="0">
            </div>

            <div class="form-field">
                <label>Kode Produk</label>
                <input type="text" id="inp-kode" placeholder="Otomatis" readonly style="opacity:.6;">
            </div>
            <div class="form-field full">
                <label>Deskripsi (opsional)</label>
                <textarea id="inp-deskripsi" placeholder="Deskripsi singkat produk..."></textarea>
            </div>
            <div class="form-field full">
                <label>Upload Foto Produk</label>
                <div class="upload-area" id="upload-area">
                    <input type="file" accept="image/*" onchange="pratinjauGambar(event)">
                    <div id="upload-placeholder">
                        <div class="ua-icon"><i class="fa-solid fa-cloud-arrow-up"></i></div>
                        <div class="ua-text">Klik untuk upload foto<br><span style="font-size:11px;color:#6b6b80;">(JPG, PNG, max 2MB)</span></div>
                    </div>
                    <img id="preview-img" src="" alt="" style="display:none;max-height:120px;border-radius:8px;object-fit:cover;">
                </div>
            </div>
        </div>

        <div class="modal-actions">
            <button class="btn-reset" onclick="resetForm()">Reset</button>
            <button class="btn-simpan" id="btn-simpan-produk" onclick="simpanProduk()">
                <i class="fa-solid fa-floppy-disk"></i> Simpan Produk
            </button>
            <button class="btn-hapus-modal" id="btn-hapus-dari-edit" style="display:none;" onclick="hapusDariEdit()">
                <i class="fa-solid fa-trash"></i> Hapus
            </button>
        </div>
    </div>
</div>


<script>
// ===== DATA PRODUK (dari PHP, disimpan di JS untuk manipulasi client-side) =====
let produkData = <?= json_encode(array_values($produk_list), JSON_UNESCAPED_UNICODE) ?>;
let editIdx    = null;
let hapusIdx   = null;
let fotoBase64 = '';
const STORAGE_KEY = 'sphinx_produk_v1';

// Load dari localStorage jika ada (untuk persistensi CRUD)
function loadProduk() {
    const saved = localStorage.getItem(STORAGE_KEY);
    if (saved) { try { produkData = JSON.parse(saved); } catch(e){} }
}

function saveProduk() {
    localStorage.setItem(STORAGE_KEY, JSON.stringify(produkData));
}

function formatRp(n) {
    return 'Rp ' + Number(n).toLocaleString('id-ID');
}

function stokClass(stok) {
    if (stok == 0) return 'rendah';
    if (stok <= 5) return 'cukup';
    return 'aman';
}

function stokLabel(stok) {
    return stok;
}

function effectiveStatus(stok) {
    if (stok == 0)  return 'habis';
    if (stok <= 5)  return 'sedikit';
    return 'aktif';
}

const ikonKat = {Jewelry:'fa-ring',Jasa:'fa-hand-sparkles',Perawatan:'fa-spray-can',Aksesori:'fa-gem'};

// ===== RENDER TABLE =====
function renderTable() {
    const query = (document.querySelector('.search-produk input')?.value ?? '').toLowerCase();
    const tbody = document.querySelector('table.produk-tbl tbody');
    const list  = produkData.filter(p =>
        !query ||
        p.nama.toLowerCase().includes(query) ||
        p.kode.toLowerCase().includes(query) ||
        p.kategori.toLowerCase().includes(query)
    );

    if (!list.length) {
        tbody.innerHTML = `<tr><td colspan="8" style="text-align:center;color:var(--text-soft);padding:30px;">Tidak ada produk ditemukan.</td></tr>`;
        return;
    }

    tbody.innerHTML = list.map((p, vi) => {
        const realIdx = produkData.indexOf(p);
        const fotoHtml = p.foto
            ? `<img src="${escHtml(p.foto)}" alt="" style="width:100%;height:100%;border-radius:8px;object-fit:cover;">`
            : `<i class="fa-solid ${ikonKat[p.kategori] ?? 'fa-box'}"></i>`;
        return `
        <tr>
            <td><div class="produk-foto">${fotoHtml}</div></td>
            <td class="kode-cell">${escHtml(p.kode)}</td>
            <td style="font-weight:500;">${escHtml(p.nama)}</td>
            <td><span style="font-size:12px;color:var(--text-soft);">${escHtml(p.kategori)}</span></td>
            <td class="harga-cell">${formatRp(p.harga)}</td>
            <td><span class="stok-cell ${stokClass(p.stok)}">${stokLabel(p.stok)}</span></td>
            <td><span class="badge ${effectiveStatus(p.stok)}">${ucFirst(effectiveStatus(p.stok))}</span></td>
            <td>
                <div class="aksi-btns">
                    <button class="btn-edit-kecil" title="Edit" onclick="bukaModal('edit',${realIdx})"><i class="fa-solid fa-pen"></i></button>
                    <button class="btn-hapus-kecil" title="Hapus" onclick="bukaKonfirmHapus(${realIdx},'${escHtml(p.nama)}')"><i class="fa-solid fa-trash"></i></button>
                </div>
            </td>
        </tr>`;
    }).join('');
}

// ===== MODAL =====
function bukaModal(mode, idx) {
    editIdx     = (mode === 'edit') ? idx : null;
    fotoBase64  = '';
    const title = document.getElementById('modal-produk-title');
    const btnHapus = document.getElementById('btn-hapus-dari-edit');
    const btnSimpan = document.getElementById('btn-simpan-produk');

    resetForm();

    if (mode === 'tambah') {
        title.innerHTML = '<i class="fa-solid fa-plus"></i> Tambah Produk';
        const nextNum = String(produkData.length + 1).padStart(3, '0');
        document.getElementById('inp-kode').value = 'PRD-' + nextNum;
        btnHapus.style.display = 'none';
        btnSimpan.innerHTML = '<i class="fa-solid fa-floppy-disk"></i> Simpan Produk';
    } else {
        const p = produkData[idx];
        title.innerHTML = '<i class="fa-solid fa-pen"></i> Edit Produk';
        document.getElementById('inp-kode').value         = p.kode;
        document.getElementById('inp-nama').value         = p.nama;
        document.getElementById('inp-harga').value        = p.harga;
        document.getElementById('inp-kategori').value     = p.kategori;
        document.getElementById('inp-stok').value         = p.stok;
        if (p.foto) {
            fotoBase64 = p.foto;
            document.getElementById('upload-placeholder').style.display = 'none';
            const prev = document.getElementById('preview-img');
            prev.src = p.foto;
            prev.style.display = 'block';
        }
        btnHapus.style.display = 'flex';
        btnSimpan.innerHTML = '<i class="fa-solid fa-floppy-disk"></i> Update Produk';
    }

    document.getElementById('modal-produk').classList.add('show');
}

function tutupModal(id) {
    document.getElementById(id).classList.remove('show');
}

function resetForm() {
    document.getElementById('inp-nama').value = '';
    document.getElementById('inp-harga').value = '';
    document.getElementById('inp-kategori').value = 'Jewelry';
    document.getElementById('inp-stok').value = '';
    document.getElementById('inp-deskripsi').value = '';
    document.getElementById('upload-placeholder').style.display = 'block';
    const prev = document.getElementById('preview-img');
    prev.src = '';
    prev.style.display = 'none';
    fotoBase64 = '';
}

function pratinjauGambar(event) {
    const file = event.target.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = e => {
        fotoBase64 = e.target.result;
        document.getElementById('upload-placeholder').style.display = 'none';
        const prev = document.getElementById('preview-img');
        prev.src = fotoBase64;
        prev.style.display = 'block';
    };
    reader.readAsDataURL(file);
}

function simpanProduk() {
    const nama    = document.getElementById('inp-nama').value.trim();
    const harga   = parseInt(document.getElementById('inp-harga').value) || 0;
    const kat     = document.getElementById('inp-kategori').value;
    const stok    = parseInt(document.getElementById('inp-stok').value) || 0;
    const kode    = document.getElementById('inp-kode').value;

    if (!nama) { SphinxModal.peringatan('Nama produk wajib diisi.'); return; }
    if (harga <= 0) { SphinxModal.peringatan('Harga harus lebih dari 0.'); return; }

    const isEdit = editIdx !== null;
    SphinxModal.konfirmasi(
        isEdit ? 'Update Produk' : 'Tambah Produk',
        'Apakah kamu yakin ingin melanjutkan perubahan ini?',
        async () => {
            const fd = new FormData();
            fd.append('ajax_action', 'save');
            fd.append('kode', kode);
            fd.append('nama', nama);
            fd.append('kategori', kat);
            fd.append('harga', harga);
            fd.append('stok', stok);
            try {
                const res  = await fetch('Produk-admin.php', { method:'POST', body:fd });
                const data = await res.json();
                if (!data.success) { SphinxModal.peringatan('Gagal menyimpan: ' + (data.message||'Error server.')); return; }
            } catch(e) { SphinxModal.peringatan('Terjadi kesalahan koneksi.'); return; }

            if (isEdit) {
                produkData[editIdx] = { ...produkData[editIdx], nama, harga, kategori: kat, stok };
            } else {
                produkData.push({ kode, nama, kategori: kat, harga, stok });
            }
            saveProduk();
            tutupModal('modal-produk');
            renderTable();
            SphinxModal.berhasil('Data telah berhasil disimpan.');
        }
    );
}

// ===== HAPUS =====
function bukaKonfirmHapus(idx, nama) {
    hapusIdx = idx;
    SphinxModal.hapus(
        '"' + nama + '" akan dihapus. Tindakan ini tidak dapat dibatalkan.',
        () => eksekusiHapus()
    );
}

function hapusDariEdit() {
    tutupModal('modal-produk');
    bukaKonfirmHapus(editIdx, produkData[editIdx].nama);
}

async function eksekusiHapus() {
    if (hapusIdx === null) return;
    const kode = produkData[hapusIdx].kode;
    const fd = new FormData();
    fd.append('ajax_action', 'delete');
    fd.append('kode', kode);
    try {
        const res  = await fetch('Produk-admin.php', { method:'POST', body:fd });
        const data = await res.json();
        if (!data.success) { SphinxModal.peringatan('Gagal menghapus.'); return; }
    } catch(e) { SphinxModal.peringatan('Terjadi kesalahan koneksi.'); return; }
    produkData.splice(hapusIdx, 1);
    saveProduk();
    renderTable();
    hapusIdx = null;
    SphinxModal.berhasil('Produk berhasil dihapus.');
}

// ===== UTILS =====
function escHtml(str) {
    return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
function ucFirst(str) {
    return str.charAt(0).toUpperCase() + str.slice(1);
}

// Close on backdrop click
document.getElementById('modal-produk').addEventListener('click', function(e) {
    if (e.target === this) tutupModal('modal-produk');
});

// Search live
document.querySelector('.search-produk input')?.addEventListener('input', renderTable);

// Init
loadProduk();
renderTable();
</script>

</body>
</html>
