<?php

namespace Database\Seeders;

use App\Models\Pasien;
use Illuminate\Database\Seeder;

class PasienSeeder extends Seeder
{
    public function run(): void
    {
        // Pasien dengan nama spesifik (biar cocok dengan contoh tampilan dashboard)
        $namaPasien = ['Nabi Laksamana', 'Lenong Isti', 'Eksi Roy', 'Rafael Hex', 'Budi Santoso', 'Rina Kartika'];

        foreach ($namaPasien as $i => $nama) {
            Pasien::updateOrCreate(
                ['no_rm' => 'RM-2024-' . str_pad($i + 300, 4, '0', STR_PAD_LEFT)],
                [
                    'nama'          => $nama,
                    'jenis_kelamin' => fake()->randomElement(['L', 'P']),
                    'no_hp'         => fake()->numerify('08##########'),
                ]
            );
        }

        // Tambahan pasien acak, supaya grafik kunjungan & data antrian lebih realistis
        Pasien::factory(40)->create();
    }
}
