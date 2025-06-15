<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Employee;
use App\Repository\EmployeeRepository;
use DateTime;
use InvalidArgumentException;

class EmployeeService
{
    private EmployeeRepository $repository;

    public function __construct(EmployeeRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getAllEmployees(int $page = 1, int $limit = 50, array $filters = []): array
    {
        $offset = ($page - 1) * $limit;
        $employees = $this->repository->findAll($limit, $offset, $filters);
        $total = $this->repository->count($filters);
        
        return [
            'data' => $employees,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $limit,
                'total' => $total,
                'total_pages' => (int) ceil($total / $limit),
                'has_next' => $page < ceil($total / $limit),
                'has_prev' => $page > 1,
            ]
        ];
    }

    public function getEmployeeById(int $id): ?Employee
    {
        return $this->repository->findById($id);
    }

    public function createEmployee(array $data): Employee
    {
        $this->validateEmployeeData($data);
        
        // Check for unique constraints
        if ($this->repository->findByNip($data['nip'])) {
            throw new InvalidArgumentException('NIP already exists');
        }
        
        if ($this->repository->findByEmail($data['email'])) {
            throw new InvalidArgumentException('Email already exists');
        }

        $employee = new Employee();
        $this->mapDataToEmployee($employee, $data);
        
        $this->repository->save($employee);
        
        return $employee;
    }

    public function updateEmployee(int $id, array $data): ?Employee
    {
        $employee = $this->repository->findById($id);
        if (!$employee) {
            return null;
        }

        $this->validateEmployeeData($data, $id);
        
        // Check for unique constraints (excluding current employee)
        if (isset($data['nip'])) {
            $existing = $this->repository->findByNip($data['nip']);
            if ($existing && $existing->getId() !== $id) {
                throw new InvalidArgumentException('NIP already exists');
            }
        }
        
        if (isset($data['email'])) {
            $existing = $this->repository->findByEmail($data['email']);
            if ($existing && $existing->getId() !== $id) {
                throw new InvalidArgumentException('Email already exists');
            }
        }

        $this->mapDataToEmployee($employee, $data);
        
        $this->repository->save($employee);
        
        return $employee;
    }

    public function deleteEmployee(int $id): bool
    {
        $employee = $this->repository->findById($id);
        if (!$employee) {
            return false;
        }

        $this->repository->remove($employee);
        return true;
    }

    public function getDepartments(): array
    {
        return $this->repository->getDepartments();
    }

    public function getPositions(): array
    {
        return $this->repository->getPositions();
    }

    public function getStatistics(): array
    {
        return $this->repository->getStatistics();
    }

    private function validateEmployeeData(array $data, ?int $excludeId = null): void
    {
        $requiredFields = ['nip', 'nama_lengkap', 'email', 'jabatan', 'departemen', 'tanggal_masuk', 'gaji'];
        
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                throw new InvalidArgumentException("Field {$field} is required");
            }
        }

        // Validate email format
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Invalid email format');
        }

        // Validate status
        if (isset($data['status']) && !in_array($data['status'], ['aktif', 'non_aktif', 'cuti'])) {
            throw new InvalidArgumentException('Invalid status value');
        }

        // Validate gaji
        if (!is_numeric($data['gaji']) || (float) $data['gaji'] < 0) {
            throw new InvalidArgumentException('Gaji must be a positive number');
        }

        // Validate tanggal_masuk
        if (!DateTime::createFromFormat('Y-m-d', $data['tanggal_masuk'])) {
            throw new InvalidArgumentException('Invalid tanggal_masuk format. Use Y-m-d');
        }
    }

    private function mapDataToEmployee(Employee $employee, array $data): void
    {
        if (isset($data['nip'])) {
            $employee->setNip($data['nip']);
        }
        
        if (isset($data['nama_lengkap'])) {
            $employee->setNamaLengkap($data['nama_lengkap']);
        }
        
        if (isset($data['email'])) {
            $employee->setEmail($data['email']);
        }
        
        if (isset($data['no_telepon'])) {
            $employee->setNoTelepon($data['no_telepon']);
        }
        
        if (isset($data['jabatan'])) {
            $employee->setJabatan($data['jabatan']);
        }
        
        if (isset($data['departemen'])) {
            $employee->setDepartemen($data['departemen']);
        }
        
        if (isset($data['tanggal_masuk'])) {
            $employee->setTanggalMasuk(DateTime::createFromFormat('Y-m-d', $data['tanggal_masuk']));
        }
        
        if (isset($data['gaji'])) {
            $employee->setGaji((float) $data['gaji']);
        }
        
        if (isset($data['status'])) {
            $employee->setStatus($data['status']);
        }
        
        if (isset($data['alamat'])) {
            $employee->setAlamat($data['alamat']);
        }
    }
}