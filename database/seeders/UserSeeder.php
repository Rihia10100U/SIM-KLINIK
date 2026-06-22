<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            ['name' => 'King Nabil',       'email' => 'admin@simklinik.test',       'role' => Role::Admin],
            ['name' => 'Siti Resepsionis', 'email' => 'resepsionis@simklinik.test', 'role' => Role::Resepsionis],
            ['name' => 'dr. Hendra',       'email' => 'dokter@simklinik.test',      'role' => Role::Dokter],
            ['name' => 'Rudi Rekam Medis', 'email' => 'rekammedis@simklinik.test',  'role' => Role::PetugasRekamMedis],
            ['name' => 'Apt. Rina',        'email' => 'apoteker@simklinik.test',    'role' => Role::Apoteker],
        ];

        foreach ($users as $u) {
            User::updateOrCreate(
                ['email' => $u['email']],
                [
                    'name'     => $u['name'],
                    'role'     => $u['role'],
                    'password' => Hash::make('password'),
                ]
            );
        }
    }
}
