<?php

namespace App\Enums;

enum Role: string
{
    case Admin = 'admin';
    case Resepsionis = 'resepsionis';
    case Dokter = 'dokter';
    case PetugasRekamMedis = 'petugas_rekam_medis';
    case Apoteker = 'apoteker';

    /**
     * Label yang ditampilkan ke user (mis. di halaman Pengaturan / daftar user).
     */
    public function label(): string
    {
        return match ($this) {
            self::Admin             => 'Admin',
            self::Resepsionis       => 'Resepsionis',
            self::Dokter            => 'Dokter',
            self::PetugasRekamMedis => 'Petugas Rekam Medis',
            self::Apoteker          => 'Apoteker',
        };
    }

    /**
     * Dipakai untuk mengisi dropdown <select> role.
     */
    public static function options(): array
    {
        return array_map(
            fn (self $role) => ['value' => $role->value, 'label' => $role->label()],
            self::cases()
        );
    }
}
