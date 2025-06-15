<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version202506150000002 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Insert 10 more employees';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("INSERT INTO employees (nip, nama_lengkap, email, no_telepon, jabatan, departemen, tanggal_masuk, gaji, status, alamat) VALUES
            ('EMP006', 'Maya Putri', 'maya.putri@company.com', '081234567895', 'UI/UX Designer', 'IT', '2023-09-01', 7500000.00, 'aktif', 'Jl. Asia Afrika No. 111, Bandung'),
            ('EMP007', 'Fajar Ramadhan', 'fajar.ramadhan@company.com', '081234567896', 'Sales Manager', 'Sales', '2022-12-15', 11000000.00, 'aktif', 'Jl. Diponegoro No. 222, Surabaya'),
            ('EMP008', 'Nina Wati', 'nina.wati@company.com', '081234567897', 'Content Writer', 'Marketing', '2024-01-10', 5500000.00, 'aktif', 'Jl. Veteran No. 333, Jakarta'),
            ('EMP009', 'Hendra Kusuma', 'hendra.kusuma@company.com', '081234567898', 'System Administrator', 'IT', '2023-03-20', 8500000.00, 'aktif', 'Jl. Pemuda No. 444, Jakarta'),
            ('EMP010', 'Lina Wijaya', 'lina.wijaya@company.com', '081234567899', 'Customer Service', 'Customer Support', '2024-02-01', 5000000.00, 'aktif', 'Jl. Merdeka No. 555, Jakarta'),
            ('EMP011', 'Rudi Hartono', 'rudi.hartono@company.com', '081234567900', 'Business Analyst', 'IT', '2023-11-15', 9000000.00, 'aktif', 'Jl. Sudirman No. 666, Jakarta'),
            ('EMP012', 'Sari Indah', 'sari.indah@company.com', '081234567901', 'HR Staff', 'Human Resources', '2024-03-01', 5500000.00, 'aktif', 'Jl. Gatot Subroto No. 777, Jakarta'),
            ('EMP013', 'Doni Pratama', 'doni.pratama@company.com', '081234567902', 'Network Engineer', 'IT', '2023-07-20', 8000000.00, 'cuti', 'Jl. Thamrin No. 888, Jakarta'),
            ('EMP014', 'Rina Melati', 'rina.melati@company.com', '081234567903', 'Finance Staff', 'Finance', '2024-01-15', 6000000.00, 'aktif', 'Jl. Kuningan No. 999, Jakarta'),
            ('EMP015', 'Ahmad Hidayat', 'ahmad.hidayat@company.com', '081234567904', 'Quality Assurance', 'IT', '2023-10-01', 7000000.00, 'aktif', 'Jl. Senayan No. 1000, Jakarta')
        ");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("DELETE FROM employees WHERE nip IN ('EMP006', 'EMP007', 'EMP008', 'EMP009', 'EMP010', 'EMP011', 'EMP012', 'EMP013', 'EMP014', 'EMP015')");
    }
} 