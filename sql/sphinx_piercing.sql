-- ============================================================
-- DATABASE: sphinx_piercing
-- Sistem Pemesanan Sphinx Piercing
-- Dibuat  : 2026-06-25
-- ============================================================

CREATE DATABASE IF NOT EXISTS sphinx_piercing
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE sphinx_piercing;

-- ============================================================
-- 1. ADMIN
-- ============================================================
CREATE TABLE admin (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nama          VARCHAR(100)  NOT NULL,
    username      VARCHAR(50)   NOT NULL UNIQUE,
    email         VARCHAR(150)  NOT NULL UNIQUE,
    password      VARCHAR(255)  NOT NULL,          -- bcrypt hash (password_hash())
    telepon       VARCHAR(20)   DEFAULT NULL,
    foto          VARCHAR(255)  DEFAULT NULL,       -- path gambar
    role          VARCHAR(50)   NOT NULL DEFAULT 'Admin',
    tgl_bergabung DATE          NOT NULL DEFAULT (CURDATE()),
    created_at    TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at    TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================
-- 2. CUSTOMERS
-- ============================================================
CREATE TABLE customers (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nama          VARCHAR(100)  NOT NULL,
    email         VARCHAR(150)  NOT NULL UNIQUE,
    password      VARCHAR(255)  NOT NULL,          -- bcrypt hash
    telepon       VARCHAR(20)   DEFAULT NULL,
    whatsapp      VARCHAR(20)   DEFAULT NULL,
    alamat        TEXT          DEFAULT NULL,
    tgl_lahir     DATE          DEFAULT NULL,
    foto          VARCHAR(255)  DEFAULT NULL,
    membership    ENUM('REGULER','MEMBER','SILVER','GOLD','PLATINUM') NOT NULL DEFAULT 'REGULER',
    loyalty_pts   INT UNSIGNED  NOT NULL DEFAULT 0,
    tgl_bergabung DATE          NOT NULL DEFAULT (CURDATE()),
    is_active     TINYINT(1)    NOT NULL DEFAULT 1,
    created_at    TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at    TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================
-- 3. PRODUK
-- ============================================================
CREATE TABLE produk (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    kode          VARCHAR(20)   NOT NULL UNIQUE,    -- format: PRD-001
    nama          VARCHAR(150)  NOT NULL,
    kategori      ENUM('Jewelry','Jasa','Perawatan','Aksesori') NOT NULL,
    harga         INT UNSIGNED  NOT NULL DEFAULT 0, -- dalam Rupiah
    stok          INT           NOT NULL DEFAULT 0,
    deskripsi     TEXT          DEFAULT NULL,
    foto          VARCHAR(255)  DEFAULT NULL,
    is_active     TINYINT(1)    NOT NULL DEFAULT 1,
    status_stok   VARCHAR(10)   GENERATED ALWAYS AS (
                      CASE
                          WHEN stok = 0  THEN 'habis'
                          WHEN stok <= 5 THEN 'sedikit'
                          ELSE                'aktif'
                      END
                  ) STORED,
    created_at    TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at    TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================
-- 4. PESANAN (Order Header)
-- ============================================================
CREATE TABLE pesanan (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    kode          VARCHAR(20)   NOT NULL UNIQUE,    -- format: ORD-082
    customer_id   INT UNSIGNED  NOT NULL,
    layanan       ENUM('Jasa Piercing','Produk') NOT NULL,
    status        ENUM('baru','diproses','selesai','batal') NOT NULL DEFAULT 'baru',
    progres       TINYINT(1)    NOT NULL DEFAULT 0, -- 0=Diterima, 1=Siap Ambil, 2=Selesai
    total         INT UNSIGNED  NOT NULL DEFAULT 0, -- dalam Rupiah
    catatan       TEXT          DEFAULT NULL,
    metode_bayar  VARCHAR(50)   DEFAULT NULL,        -- metode pembayaran
    pengiriman    VARCHAR(50)   DEFAULT NULL,        -- jenis pengiriman
    nama_penerima VARCHAR(100)  DEFAULT NULL,
    tlp_penerima  VARCHAR(20)   DEFAULT NULL,
    alamat_penerima TEXT        DEFAULT NULL,
    catatan_bayar TEXT          DEFAULT NULL,
    tgl_masuk     DATE          NOT NULL DEFAULT (CURDATE()),
    created_at    TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at    TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_pesanan_customer
        FOREIGN KEY (customer_id) REFERENCES customers(id)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- 5. PESANAN_ITEMS (Order Line Items)
-- ============================================================
CREATE TABLE pesanan_items (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    pesanan_id    INT UNSIGNED  NOT NULL,
    produk_id     INT UNSIGNED  DEFAULT NULL,       -- NULL jika produk sudah dihapus
    nama_produk   VARCHAR(150)  NOT NULL,           -- snapshot nama saat order dibuat
    harga         INT UNSIGNED  NOT NULL DEFAULT 0, -- snapshot harga saat order dibuat
    qty           INT UNSIGNED  NOT NULL DEFAULT 1,
    subtotal      INT UNSIGNED  GENERATED ALWAYS AS (harga * qty) STORED,
    CONSTRAINT fk_items_pesanan
        FOREIGN KEY (pesanan_id) REFERENCES pesanan(id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_items_produk
        FOREIGN KEY (produk_id) REFERENCES produk(id)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- 6. JADWAL (Penjadwalan — khusus Jasa Piercing)
-- ============================================================
CREATE TABLE jadwal (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    pesanan_id      INT UNSIGNED  NOT NULL UNIQUE,  -- 1 pesanan = 1 jadwal
    tanggal         DATE          NOT NULL,
    jam             TIME          NOT NULL,          -- 08:00 / 09:00 / 10:00 / 11:00 / 13:00 / 14:00 / 15:00
    kapasitas       TINYINT UNSIGNED NOT NULL DEFAULT 1,
    status          ENUM('tersedia','ada_pesanan','slot_penuh','dibatalkan') NOT NULL DEFAULT 'ada_pesanan',
    catatan_admin   VARCHAR(255)  DEFAULT NULL,
    created_at      TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_jadwal_pesanan
        FOREIGN KEY (pesanan_id) REFERENCES pesanan(id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    INDEX idx_jadwal_tanggal_jam (tanggal, jam)
) ENGINE=InnoDB;

-- ============================================================
-- 7. NOTIFIKASI
-- ============================================================
CREATE TABLE notifikasi (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tipe        ENUM('baru','diproses','selesai','batal','info') NOT NULL,
    judul       VARCHAR(150)  NOT NULL,
    pesan       TEXT          NOT NULL,
    pesanan_id  INT UNSIGNED  DEFAULT NULL,         -- NULL untuk notifikasi sistem (misal: stok menipis)
    is_read     TINYINT(1)    NOT NULL DEFAULT 0,
    created_at  TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_notif_pesanan
        FOREIGN KEY (pesanan_id) REFERENCES pesanan(id)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- INDEXES TAMBAHAN
-- ============================================================
CREATE INDEX idx_pesanan_status    ON pesanan(status);
CREATE INDEX idx_pesanan_tgl       ON pesanan(tgl_masuk);
CREATE INDEX idx_produk_kategori   ON produk(kategori);
CREATE INDEX idx_notif_read        ON notifikasi(is_read);
CREATE INDEX idx_customer_member   ON customers(membership);

-- ============================================================
-- DATA AWAL: Admin Default
-- Ganti password hash di bawah menggunakan:
--   php -r "echo password_hash('password_anda', PASSWORD_BCRYPT);"
-- ============================================================
INSERT INTO admin (nama, username, email, password, role, tgl_bergabung)
VALUES (
    'Sphinx Admin',
    'sphinx_admin',
    'admin@sphinx-piercing.com',
    '$2y$12$GANTI_DENGAN_BCRYPT_HASH_DISINI',
    'Admin',
    CURDATE()
);

-- ============================================================
-- DATA AWAL: Contoh Produk
-- ============================================================
INSERT INTO produk (kode, nama, kategori, harga, stok, deskripsi) VALUES
('PRD-001', 'Titanium Nose Ring',    'Jewelry',    85000,  20, 'Cincin hidung material titanium grade medis'),
('PRD-002', 'Steel Ear Stud',        'Jewelry',    65000,  35, 'Anting stud baja bedah anti-alergi'),
('PRD-003', 'Gold Helix Ring',       'Jewelry',   120000,  15, 'Ring helix lapis emas 18K'),
('PRD-004', 'Silver Septum Ring',    'Jewelry',    95000,  12, 'Ring septum perak sterling'),
('PRD-005', 'Titanium Labret Stud',  'Jewelry',    75000,   5, 'Labret stud titanium flat back'),
('PRD-006', 'Jasa Piercing Telinga', 'Jasa',       50000,   0, 'Layanan piercing telinga profesional'),
('PRD-007', 'Jasa Piercing Hidung',  'Jasa',       75000,   0, 'Layanan piercing hidung profesional'),
('PRD-008', 'Aftercare Spray',       'Perawatan',  45000,  30, 'Semprotan perawatan luka piercing'),
('PRD-009', 'Aftercare Saline',      'Perawatan',  35000,  25, 'Larutan saline steril untuk aftercare');
