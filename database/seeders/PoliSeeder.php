<?php

namespace Database\Seeders;

use App\Models\Poli;
use Illuminate\Database\Seeder;

class PoliSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            ['kode' => 'A', 'nama' => 'Poli Umum',      'aktif' => true],
            ['kode' => 'B', 'nama' => 'Poli Gigi',      'aktif' => true],
            ['kode' => 'C', 'nama' => 'Poli Anak',      'aktif' => true],
            ['kode' => 'D', 'nama' => 'Poli Kandungan', 'aktif' => false],
        ];

        foreach ($data as $row) {
            Poli::updateOrCreate(['kode' => $row['kode']], $row);
        }
    }
}
