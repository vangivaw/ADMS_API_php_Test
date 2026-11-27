CREATE DATABASE IF NOT EXISTS db_absensi_percetakan;
USE db_absensi_percetakan;

-- Tabel Master Karyawan (PIN harus sama dengan ID di mesin fingerprint)
CREATE TABLE karyawan (
    pin VARCHAR(20) PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    divisi VARCHAR(50) NOT NULL
);

-- Tabel Log Absensi dari Mesin ADMS
CREATE TABLE log_absensi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pin VARCHAR(20) NOT NULL,
    waktu_scan DATETIME NOT NULL,
    status ENUM('MASUK', 'TERLAMBAT', 'PULANG') NOT NULL,
    metode VARCHAR(30) DEFAULT 'Sidik Jari',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX (waktu_scan),
    INDEX (pin)
);

-- Data Contoh Karyawan Percetakan
INSERT INTO karyawan (pin, nama, divisi) VALUES
('K-001', 'Ahmad Fauzi', 'Operator Cetak'),
('K-005', 'Budi Santoso', 'Pracetak (Prepress)'),
('K-012', 'Siti Rahma', 'Administrasi/HRD'),
('K-018', 'Dedi Kurniawan', 'Finishing & Jilid');