<?php

namespace Database\Seeders;

use App\Models\Layanan;
use App\Models\Poli;
use Illuminate\Database\Seeder;

class LayananSeeder extends Seeder
{
    public function run(): void
    {
        $poliUmum = Poli::where('kode', 'A')->first();
        $poliGigi = Poli::where('kode', 'B')->first();
        $poliAnak = Poli::where('kode', 'C')->first();
        $poliKandungan = Poli::where('kode', 'D')->first();

        $data = [
            ['poli_id' => $poliUmum?->id,      'nama' => 'Konsultasi Poli Umum',      'kategori' => 'konsultasi', 'harga' => 50000, 'aktif' => true],
            ['poli_id' => $poliGigi?->id,      'nama' => 'Konsultasi Poli Gigi',      'kategori' => 'konsultasi', 'harga' => 75000, 'aktif' => true],
            ['poli_id' => $poliAnak?->id,      'nama' => 'Konsultasi Poli Anak',      'kategori' => 'konsultasi', 'harga' => 60000, 'aktif' => true],
            ['poli_id' => $poliKandungan?->id, 'nama' => 'Konsultasi Poli Kandungan', 'kategori' => 'konsultasi', 'harga' => 80000, 'aktif' => true],
            ['poli_id' => null, 'nama' => 'Pemeriksaan Lab Sederhana', 'kategori' => 'tindakan', 'harga' => 35000, 'aktif' => true],
            ['poli_id' => null, 'nama' => 'Suntik / Injeksi',          'kategori' => 'tindakan', 'harga' => 25000, 'aktif' => true],
            ['poli_id' => null, 'nama' => 'Rawat Luka',                'kategori' => 'tindakan', 'harga' => 30000, 'aktif' => true],
        ];

        foreach ($data as $row) {
            Layanan::updateOrCreate(['nama' => $row['nama']], $row);
        }
    }
}
