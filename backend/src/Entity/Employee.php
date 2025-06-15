<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use DateTime;
use JsonSerializable;

#[ORM\Entity]
#[ORM\Table(name: 'employees')]
#[ORM\Index(name: 'idx_nip', columns: ['nip'])]
#[ORM\Index(name: 'idx_departemen', columns: ['departemen'])]
#[ORM\Index(name: 'idx_status', columns: ['status'])]
class Employee implements JsonSerializable
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 20, unique: true)]
    private string $nip;

    #[ORM\Column(type: 'string', length: 100, name: 'nama_lengkap')]
    private string $namaLengkap;

    #[ORM\Column(type: 'string', length: 100, unique: true)]
    private string $email;

    #[ORM\Column(type: 'string', length: 15, nullable: true, name: 'no_telepon')]
    private ?string $noTelepon = null;

    #[ORM\Column(type: 'string', length: 50)]
    private string $jabatan;

    #[ORM\Column(type: 'string', length: 50)]
    private string $departemen;

    #[ORM\Column(type: 'date', name: 'tanggal_masuk')]
    private DateTime $tanggalMasuk;

    #[ORM\Column(type: 'decimal', precision: 15, scale: 2)]
    private float $gaji;

    #[ORM\Column(type: 'string', columnDefinition: "ENUM('aktif', 'non_aktif', 'cuti')", options: ['default' => 'aktif'])]
    private string $status = 'aktif';

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $alamat = null;

    #[ORM\Column(type: 'datetime', name: 'created_at', options: ['default' => 'CURRENT_TIMESTAMP'])]
    private DateTime $createdAt;

    #[ORM\Column(type: 'datetime', name: 'updated_at', options: ['default' => 'CURRENT_TIMESTAMP'])]
    private DateTime $updatedAt;

    #[ORM\Column(type: 'datetime', name: 'deleted_at', nullable: true)]
    private ?DateTime $deletedAt = null;

    public function __construct()
    {
        $this->createdAt = new DateTime();
        $this->updatedAt = new DateTime();
    }

    #[ORM\PreUpdate]
    public function preUpdate(): void
    {
        $this->updatedAt = new DateTime();
    }

    // Getters
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNip(): string
    {
        return $this->nip;
    }

    public function getNamaLengkap(): string
    {
        return $this->namaLengkap;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getNoTelepon(): ?string
    {
        return $this->noTelepon;
    }

    public function getJabatan(): string
    {
        return $this->jabatan;
    }

    public function getDepartemen(): string
    {
        return $this->departemen;
    }

    public function getTanggalMasuk(): DateTime
    {
        return $this->tanggalMasuk;
    }

    public function getGaji(): float
    {
        return $this->gaji;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getAlamat(): ?string
    {
        return $this->alamat;
    }

    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): DateTime
    {
        return $this->updatedAt;
    }

    public function getDeletedAt(): ?DateTime
    {
        return $this->deletedAt;
    }

    // Setters
    public function setNip(string $nip): self
    {
        $this->nip = $nip;
        return $this;
    }

    public function setNamaLengkap(string $namaLengkap): self
    {
        $this->namaLengkap = $namaLengkap;
        return $this;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function setNoTelepon(?string $noTelepon): self
    {
        $this->noTelepon = $noTelepon;
        return $this;
    }

    public function setJabatan(string $jabatan): self
    {
        $this->jabatan = $jabatan;
        return $this;
    }

    public function setDepartemen(string $departemen): self
    {
        $this->departemen = $departemen;
        return $this;
    }

    public function setTanggalMasuk(DateTime $tanggalMasuk): self
    {
        $this->tanggalMasuk = $tanggalMasuk;
        return $this;
    }

    public function setGaji(float $gaji): self
    {
        $this->gaji = $gaji;
        return $this;
    }

    public function setStatus(string $status): self
    {
        if (!in_array($status, ['aktif', 'non_aktif', 'cuti'])) {
            throw new \InvalidArgumentException('Invalid status value');
        }
        $this->status = $status;
        return $this;
    }

    public function setAlamat(?string $alamat): self
    {
        $this->alamat = $alamat;
        return $this;
    }

    public function setDeletedAt(?DateTime $deletedAt): self
    {
        $this->deletedAt = $deletedAt;
        return $this;
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'nip' => $this->nip,
            'nama_lengkap' => $this->namaLengkap,
            'email' => $this->email,
            'no_telepon' => $this->noTelepon,
            'jabatan' => $this->jabatan,
            'departemen' => $this->departemen,
            'tanggal_masuk' => $this->tanggalMasuk->format('Y-m-d'),
            'gaji' => $this->gaji,
            'status' => $this->status,
            'alamat' => $this->alamat,
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt->format('Y-m-d H:i:s'),
            'deleted_at' => $this->deletedAt ? $this->deletedAt->format('Y-m-d H:i:s') : null,
        ];
    }
}