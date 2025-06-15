<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version202506150000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create employees table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE employees (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nip VARCHAR(20) NOT NULL UNIQUE,
            nama_lengkap VARCHAR(100) NOT NULL,
            email VARCHAR(100) NOT NULL UNIQUE,
            no_telepon VARCHAR(15),
            jabatan VARCHAR(50) NOT NULL,
            departemen VARCHAR(50) NOT NULL,
            tanggal_masuk DATE NOT NULL,
            gaji DECIMAL(15,2) NOT NULL,
            status ENUM(\'aktif\', \'non_aktif\', \'cuti\') DEFAULT \'aktif\',
            alamat TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_nip (nip),
            INDEX idx_departemen (departemen),
            INDEX idx_status (status)
        )');

        // Insert sample data
        $this->addSql("INSERT INTO employees (nip, nama_lengkap, email, no_telepon, jabatan, departemen, tanggal_masuk, gaji, status, alamat) VALUES
            ('EMP001', 'Ahmad Wijaya', 'ahmad.wijaya@company.com', '081234567890', 'Software Engineer', 'IT', '2023-01-15', 8000000.00, 'aktif', 'Jl. Sudirman No. 123, Jakarta'),
            ('EMP002', 'Siti Nurhaliza', 'siti.nurhaliza@company.com', '081234567891', 'HR Manager', 'Human Resources', '2022-03-10', 12000000.00, 'aktif', 'Jl. Thamrin No. 456, Jakarta'),
            ('EMP003', 'Budi Santoso', 'budi.santoso@company.com', '081234567892', 'Marketing Specialist', 'Marketing', '2023-06-20', 6000000.00, 'aktif', 'Jl. Gatot Subroto No. 789, Jakarta'),
            ('EMP004', 'Dewi Sartika', 'dewi.sartika@company.com', '081234567893', 'Accountant', 'Finance', '2021-11-05', 7000000.00, 'cuti', 'Jl. Kuningan No. 321, Jakarta'),
            ('EMP005', 'Rizki Pratama', 'rizki.pratama@company.com', '081234567894', 'Project Manager', 'IT', '2020-08-12', 15000000.00, 'aktif', 'Jl. Senayan No. 654, Jakarta')
        ");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE employees');
    }
} 