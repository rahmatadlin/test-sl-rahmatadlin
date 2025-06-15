CREATE TABLE employees (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nip VARCHAR(20) NOT NULL UNIQUE,
    nama_lengkap VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    no_telepon VARCHAR(15),
    jabatan VARCHAR(50) NOT NULL,
    departemen VARCHAR(50) NOT NULL,
    tanggal_masuk DATE NOT NULL,
    gaji DECIMAL(15,2) NOT NULL,
    status ENUM('aktif', 'non_aktif', 'cuti') DEFAULT 'aktif',
    alamat TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_nip (nip),
    INDEX idx_departemen (departemen),
    INDEX idx_status (status)
);