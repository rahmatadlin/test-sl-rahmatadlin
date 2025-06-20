<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Employee;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;

class EmployeeRepository
{
    private EntityManager $em;
    private EntityRepository $repository;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
        $this->repository = $em->getRepository(Employee::class);
    }

    public function findAll(int $page = 1, int $limit = 50, array $filters = []): array
    {
        $qb = $this->repository->createQueryBuilder('e');

        // Apply filters
        if (!empty($filters['departemen'])) {
            $qb->andWhere('e.departemen = :departemen')
               ->setParameter('departemen', $filters['departemen']);
        }

        if (!empty($filters['status'])) {
            $qb->andWhere('e.status = :status')
               ->setParameter('status', $filters['status']);
        }

        if (!empty($filters['jabatan'])) {
            $qb->andWhere('e.jabatan = :jabatan')
               ->setParameter('jabatan', $filters['jabatan']);
        }

        if (!empty($filters['search'])) {
            $qb->andWhere('e.namaLengkap LIKE :search OR e.nip LIKE :search OR e.email LIKE :search')
               ->setParameter('search', '%' . $filters['search'] . '%');
        }

        // Apply sorting
        if (!empty($filters['sort_field'])) {
            $sortField = $filters['sort_field'];
            $sortOrder = !empty($filters['sort_order']) && strtoupper($filters['sort_order']) === 'DESC' ? 'DESC' : 'ASC';
            
            // Map frontend field names to database column names
            $fieldMapping = [
                'nip' => 'e.nip',
                'nama_lengkap' => 'e.namaLengkap',
                'email' => 'e.email',
                'no_telepon' => 'e.noTelepon',
                'jabatan' => 'e.jabatan',
                'departemen' => 'e.departemen',
                'tanggal_masuk' => 'e.tanggalMasuk',
                'status' => 'e.status'
            ];

            if (isset($fieldMapping[$sortField])) {
                $qb->orderBy($fieldMapping[$sortField], $sortOrder);
            }
        } else {
            // Default sorting by NIP if no sort field is specified
            $qb->orderBy('e.nip', 'ASC');
        }

        // Calculate pagination
        $total = $this->count($filters);
        $offset = ($page - 1) * $limit;

        $qb->setFirstResult($offset)
           ->setMaxResults($limit);

        $employees = $qb->getQuery()->getResult();

        return [
            'data' => $employees,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $limit,
                'total' => $total,
                'total_pages' => ceil($total / $limit)
            ]
        ];
    }

    public function count(array $filters = []): int
    {
        $qb = $this->repository->createQueryBuilder('e')
                              ->select('COUNT(e.id)');

        // Apply same filters as findAll
        if (!empty($filters['departemen'])) {
            $qb->andWhere('e.departemen = :departemen')
               ->setParameter('departemen', $filters['departemen']);
        }

        if (!empty($filters['status'])) {
            $qb->andWhere('e.status = :status')
               ->setParameter('status', $filters['status']);
        }

        if (!empty($filters['jabatan'])) {
            $qb->andWhere('e.jabatan = :jabatan')
               ->setParameter('jabatan', $filters['jabatan']);
        }

        if (!empty($filters['search'])) {
            $qb->andWhere('e.namaLengkap LIKE :search OR e.nip LIKE :search OR e.email LIKE :search')
               ->setParameter('search', '%' . $filters['search'] . '%');
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    public function findById(int $id): ?Employee
    {
        return $this->repository->find($id);
    }

    public function findByNip(string $nip): ?Employee
    {
        return $this->repository->findOneBy(['nip' => $nip]);
    }

    public function findByEmail(string $email): ?Employee
    {
        return $this->repository->findOneBy(['email' => $email]);
    }

    public function save(Employee $employee): void
    {
        $this->em->persist($employee);
        $this->em->flush();
    }

    public function remove(Employee $employee): void
    {
        $this->em->remove($employee);
        $this->em->flush();
    }

    public function getDepartments(): array
    {
        $qb = $this->repository->createQueryBuilder('e')
                              ->select('DISTINCT e.departemen')
                              ->orderBy('e.departemen', 'ASC');

        return array_column($qb->getQuery()->getResult(), 'departemen');
    }

    public function getPositions(): array
    {
        $qb = $this->repository->createQueryBuilder('e')
                              ->select('DISTINCT e.jabatan')
                              ->orderBy('e.jabatan', 'ASC');

        return array_column($qb->getQuery()->getResult(), 'jabatan');
    }

    public function getStatistics(): array
    {
        $qb = $this->repository->createQueryBuilder('e');
        
        // Total employees
        $total = $qb->select('COUNT(e.id)')
                   ->getQuery()
                   ->getSingleScalarResult();

        // By status
        $statusStats = $this->repository->createQueryBuilder('e')
                                       ->select('e.status, COUNT(e.id) as count')
                                       ->groupBy('e.status')
                                       ->getQuery()
                                       ->getResult();

        // By department
        $departmentStats = $this->repository->createQueryBuilder('e')
                                           ->select('e.departemen, COUNT(e.id) as count')
                                           ->groupBy('e.departemen')
                                           ->getQuery()
                                           ->getResult();

        return [
            'total' => (int) $total,
            'by_status' => $statusStats,
            'by_department' => $departmentStats,
        ];
    }
}