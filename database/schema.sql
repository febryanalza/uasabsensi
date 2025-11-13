-- ========================================================
-- Database: absensi
-- Sistem Informasi Absensi & Penggajian Karyawan
-- MySQL/MariaDB Compatible
-- Generated for Laravel Migration
-- ========================================================

CREATE DATABASE IF NOT EXISTS absensi CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE absensi;

-- ========================================================
-- TABEL: available_rfid
-- Deskripsi: Master kartu RFID yang tersedia
-- ========================================================

CREATE TABLE available_rfid (
    id CHAR(36) PRIMARY KEY,
    card_number VARCHAR(255) UNIQUE NOT NULL,
    card_type VARCHAR(100) NULL,
    status ENUM('AVAILABLE','ASSIGNED','DAMAGED','LOST','INACTIVE') DEFAULT 'AVAILABLE',
    assigned_at DATETIME NULL,
    notes TEXT NULL,
    karyawan_id CHAR(36) NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_card_number (card_number),
    INDEX idx_status (status),
    INDEX idx_karyawan_id (karyawan_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================================
-- TABEL: karyawan
-- Deskripsi: Master data karyawan
-- ========================================================

CREATE TABLE karyawan (
    id CHAR(36) PRIMARY KEY,
    nip VARCHAR(100) UNIQUE NOT NULL,
    rfid_card_number VARCHAR(255) UNIQUE NULL,
    nama VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    jabatan VARCHAR(255) NOT NULL,
    departemen VARCHAR(255) NOT NULL,
    telepon VARCHAR(50) NULL,
    alamat TEXT NULL,
    tanggal_masuk DATE NULL,
    status ENUM('AKTIF','CUTI','RESIGN') DEFAULT 'AKTIF',
    
    -- Komponen gaji
    gaji_pokok DECIMAL(15,2) DEFAULT 0.00,
    tunjangan_jabatan DECIMAL(15,2) DEFAULT 0.00,
    tunjangan_transport DECIMAL(15,2) DEFAULT 0.00,
    tunjangan_makan DECIMAL(15,2) DEFAULT 0.00,
    
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_nip (nip),
    INDEX idx_email (email),
    INDEX idx_rfid_card_number (rfid_card_number),
    
    CONSTRAINT fk_karyawan_rfid 
        FOREIGN KEY (rfid_card_number) 
        REFERENCES available_rfid(card_number) 
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================================
-- TABEL: users
-- Deskripsi: User login dan autentikasi
-- ========================================================

CREATE TABLE users (
    id CHAR(36) PRIMARY KEY,
    email VARCHAR(255) UNIQUE NULL,
    name VARCHAR(255) NULL,
    role ENUM('ADMIN','USER','MANAGER') DEFAULT 'USER',
    karyawan_id CHAR(36) NULL,
    password VARCHAR(255) NOT NULL,
    remember_token VARCHAR(100) NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_karyawan_id (karyawan_id),
    
    CONSTRAINT fk_users_karyawan 
        FOREIGN KEY (karyawan_id) 
        REFERENCES karyawan(id) 
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================================
-- TABEL: password_reset_tokens
-- Deskripsi: Token reset password
-- ========================================================

CREATE TABLE password_reset_tokens (
    email VARCHAR(255) PRIMARY KEY,
    token VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================================
-- TABEL: sessions
-- Deskripsi: Session management
-- ========================================================

CREATE TABLE sessions (
    id VARCHAR(255) PRIMARY KEY,
    user_id BIGINT UNSIGNED NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    payload LONGTEXT NOT NULL,
    last_activity INT NOT NULL,
    
    INDEX idx_user_id (user_id),
    INDEX idx_last_activity (last_activity)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================================
-- TABEL: absensi
-- Deskripsi: Data absensi harian karyawan
-- ========================================================

CREATE TABLE absensi (
    id CHAR(36) PRIMARY KEY,
    karyawan_id CHAR(36) NOT NULL,
    tanggal DATE NOT NULL,
    jam_masuk DATETIME NULL,
    jam_keluar DATETIME NULL,
    status ENUM('HADIR','IZIN','SAKIT','ALPHA','CUTI') NOT NULL,
    keterangan TEXT NULL,
    lokasi VARCHAR(255) NULL,
    foto_masuk VARCHAR(255) NULL,
    foto_keluar VARCHAR(255) NULL,
    rfid_masuk VARCHAR(255) NULL,
    rfid_keluar VARCHAR(255) NULL,
    
    -- Perhitungan potongan
    menit_terlambat INT DEFAULT 0,
    menit_pulang_cepat INT DEFAULT 0,
    potongan_terlambat DECIMAL(15,2) DEFAULT 0.00,
    potongan_alpha DECIMAL(15,2) DEFAULT 0.00,
    
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    UNIQUE KEY unique_karyawan_tanggal (karyawan_id, tanggal),
    INDEX idx_karyawan_id (karyawan_id),
    INDEX idx_tanggal (tanggal),
    INDEX idx_status (status),
    
    CONSTRAINT fk_absensi_karyawan 
        FOREIGN KEY (karyawan_id) 
        REFERENCES karyawan(id) 
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================================
-- TABEL: lembur
-- Deskripsi: Data lembur karyawan
-- ========================================================

CREATE TABLE lembur (
    id CHAR(36) PRIMARY KEY,
    karyawan_id CHAR(36) NOT NULL,
    tanggal DATE NOT NULL,
    jam_mulai DATETIME NOT NULL,
    jam_selesai DATETIME NOT NULL,
    durasi_jam DECIMAL(5,2) NOT NULL,
    keterangan TEXT NULL,
    status ENUM('PENDING','DISETUJUI','DITOLAK') DEFAULT 'PENDING',
    tarif_per_jam DECIMAL(15,2) NOT NULL,
    total_kompensasi DECIMAL(15,2) NOT NULL,
    disetujui_oleh VARCHAR(255) NULL,
    tanggal_disetujui DATETIME NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_karyawan_id (karyawan_id),
    INDEX idx_tanggal (tanggal),
    INDEX idx_status (status),
    
    CONSTRAINT fk_lembur_karyawan 
        FOREIGN KEY (karyawan_id) 
        REFERENCES karyawan(id) 
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================================
-- TABEL: gaji
-- Deskripsi: Data penggajian karyawan
-- ========================================================

CREATE TABLE gaji (
    id CHAR(36) PRIMARY KEY,
    karyawan_id CHAR(36) NOT NULL,
    bulan INT NOT NULL,
    tahun INT NOT NULL,
    
    -- Komponen pendapatan
    gaji_pokok DECIMAL(15,2) NOT NULL,
    tunjangan_jabatan DECIMAL(15,2) NOT NULL,
    tunjangan_transport DECIMAL(15,2) NOT NULL,
    tunjangan_makan DECIMAL(15,2) NOT NULL,
    tunjangan_lembur DECIMAL(15,2) DEFAULT 0.00,
    bonus_kehadiran DECIMAL(15,2) DEFAULT 0.00,
    bonus_kpi DECIMAL(15,2) DEFAULT 0.00,
    
    -- Komponen potongan
    potongan_terlambat DECIMAL(15,2) DEFAULT 0.00,
    potongan_alpha DECIMAL(15,2) DEFAULT 0.00,
    potongan_lainnya DECIMAL(15,2) DEFAULT 0.00,
    keterangan_potongan TEXT NULL,
    
    -- Potongan wajib
    bpjs_kesehatan DECIMAL(15,2) DEFAULT 0.00,
    bpjs_ketenagakerjaan DECIMAL(15,2) DEFAULT 0.00,
    pph21 DECIMAL(15,2) DEFAULT 0.00,
    
    -- Total
    total_pendapatan DECIMAL(15,2) NOT NULL,
    total_potongan DECIMAL(15,2) NOT NULL,
    gaji_bersih DECIMAL(15,2) NOT NULL,
    
    -- Statistik kehadiran
    jumlah_hadir INT DEFAULT 0,
    jumlah_izin INT DEFAULT 0,
    jumlah_sakit INT DEFAULT 0,
    jumlah_alpha INT DEFAULT 0,
    jumlah_terlambat INT DEFAULT 0,
    total_jam_lembur DECIMAL(5,2) DEFAULT 0.00,
    
    -- Status dan metadata
    status ENUM('DRAFT','FINAL','DIBAYAR') DEFAULT 'DRAFT',
    tanggal_dibuat DATETIME DEFAULT CURRENT_TIMESTAMP,
    tanggal_dibayar DATETIME NULL,
    dibuat_oleh VARCHAR(255) NULL,
    catatan_admin TEXT NULL,
    
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    UNIQUE KEY unique_karyawan_periode (karyawan_id, bulan, tahun),
    INDEX idx_karyawan_id (karyawan_id),
    INDEX idx_periode (bulan, tahun),
    INDEX idx_status (status),
    
    CONSTRAINT fk_gaji_karyawan 
        FOREIGN KEY (karyawan_id) 
        REFERENCES karyawan(id) 
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================================
-- TABEL: kpi
-- Deskripsi: Penilaian KPI karyawan
-- ========================================================

CREATE TABLE kpi (
    id CHAR(36) PRIMARY KEY,
    karyawan_id CHAR(36) NOT NULL,
    bulan INT NOT NULL,
    tahun INT NOT NULL,
    
    -- Target dan realisasi kehadiran
    target_kehadiran INT DEFAULT 0,
    realisasi_kehadiran INT DEFAULT 0,
    persen_kehadiran DECIMAL(5,2) DEFAULT 0.00,
    
    -- Target dan realisasi penyelesaian tugas
    target_penyelesaian_tugas INT DEFAULT 0,
    realisasi_penyelesaian_tugas INT DEFAULT 0,
    persen_penyelesaian_tugas DECIMAL(5,2) DEFAULT 0.00,
    
    -- Nilai kinerja
    nilai_kedisiplinan DECIMAL(5,2) DEFAULT 0.00,
    nilai_kualitas_kerja DECIMAL(5,2) DEFAULT 0.00,
    nilai_kerjasama DECIMAL(5,2) DEFAULT 0.00,
    nilai_inisiatif DECIMAL(5,2) DEFAULT 0.00,
    
    -- Hasil akhir
    skor_total DECIMAL(5,2) DEFAULT 0.00,
    kategori ENUM('SANGAT_BAIK','BAIK','CUKUP','KURANG','SANGAT_KURANG') DEFAULT 'CUKUP',
    bonus_kpi DECIMAL(15,2) DEFAULT 0.00,
    catatan TEXT NULL,
    
    -- Metadata penilaian
    dinilai_oleh VARCHAR(255) NULL,
    tanggal_penilaian DATETIME NULL,
    
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    UNIQUE KEY unique_karyawan_periode (karyawan_id, bulan, tahun),
    INDEX idx_karyawan_id (karyawan_id),
    INDEX idx_periode (bulan, tahun),
    INDEX idx_kategori (kategori),
    
    CONSTRAINT fk_kpi_karyawan 
        FOREIGN KEY (karyawan_id) 
        REFERENCES karyawan(id) 
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================================
-- TABEL: aturan_perusahaan
-- Deskripsi: Konfigurasi aturan dan kebijakan perusahaan
-- ========================================================

CREATE TABLE aturan_perusahaan (
    id CHAR(36) PRIMARY KEY,
    
    -- Jam kerja
    jam_masuk_kerja VARCHAR(10) DEFAULT '08:00',
    jam_pulang_kerja VARCHAR(10) DEFAULT '17:00',
    
    -- Aturan keterlambatan
    toleransi_terlambat INT DEFAULT 15,
    potongan_per_menit_terlambat DECIMAL(15,2) DEFAULT 0.00,
    potongan_per_hari_alpha DECIMAL(15,2) DEFAULT 0.00,
    
    -- Tarif lembur
    tarif_lembur_per_jam DECIMAL(15,2) DEFAULT 0.00,
    tarif_lembur_libur DECIMAL(15,2) DEFAULT 0.00,
    
    -- Bonus kehadiran
    bonus_kehadiran_penuh DECIMAL(15,2) DEFAULT 0.00,
    minimal_hadir_bonus INT DEFAULT 22,
    
    -- Konfigurasi umum
    hari_kerja_per_bulan INT DEFAULT 22,
    is_active BOOLEAN DEFAULT TRUE,
    
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================================
-- TABEL: hari_libur
-- Deskripsi: Data hari libur nasional dan perusahaan
-- ========================================================

CREATE TABLE hari_libur (
    id CHAR(36) PRIMARY KEY,
    tanggal DATE NOT NULL,
    nama VARCHAR(255) NOT NULL,
    deskripsi TEXT NULL,
    is_nasional BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_tanggal (tanggal)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================================
-- TABEL: cache & cache_locks (Laravel Default)
-- ========================================================

CREATE TABLE cache (
    `key` VARCHAR(255) PRIMARY KEY,
    value MEDIUMTEXT NOT NULL,
    expiration INT NOT NULL,
    INDEX idx_expiration (expiration)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE cache_locks (
    `key` VARCHAR(255) PRIMARY KEY,
    owner VARCHAR(255) NOT NULL,
    expiration INT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================================
-- TABEL: jobs, job_batches, failed_jobs (Laravel Queue)
-- ========================================================

CREATE TABLE jobs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    queue VARCHAR(255) NOT NULL,
    payload LONGTEXT NOT NULL,
    attempts TINYINT UNSIGNED NOT NULL,
    reserved_at INT UNSIGNED NULL,
    available_at INT UNSIGNED NOT NULL,
    created_at INT UNSIGNED NOT NULL,
    INDEX idx_queue (queue)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE job_batches (
    id VARCHAR(255) PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    total_jobs INT NOT NULL,
    pending_jobs INT NOT NULL,
    failed_jobs INT NOT NULL,
    failed_job_ids LONGTEXT NOT NULL,
    options MEDIUMTEXT NULL,
    cancelled_at INT NULL,
    created_at INT NOT NULL,
    finished_at INT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE failed_jobs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid VARCHAR(255) UNIQUE NOT NULL,
    connection TEXT NOT NULL,
    queue TEXT NOT NULL,
    payload LONGTEXT NOT NULL,
    exception LONGTEXT NOT NULL,
    failed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================================
-- RELASI TAMBAHAN
-- ========================================================

ALTER TABLE available_rfid
ADD CONSTRAINT fk_rfid_karyawan 
    FOREIGN KEY (karyawan_id) 
    REFERENCES karyawan(id) 
    ON DELETE SET NULL;

-- ========================================================
-- DATA SAMPLE / SEEDER (Optional)
-- ========================================================

-- Insert aturan perusahaan default
INSERT INTO aturan_perusahaan (
    id, 
    jam_masuk_kerja, 
    jam_pulang_kerja, 
    toleransi_terlambat,
    potongan_per_menit_terlambat,
    potongan_per_hari_alpha,
    tarif_lembur_per_jam,
    tarif_lembur_libur,
    bonus_kehadiran_penuh,
    minimal_hadir_bonus,
    hari_kerja_per_bulan,
    is_active
) VALUES (
    UUID(),
    '08:00',
    '17:00',
    15,
    5000.00,
    100000.00,
    25000.00,
    50000.00,
    500000.00,
    22,
    22,
    TRUE
);

-- ========================================================
-- END OF SCHEMA
-- ========================================================
