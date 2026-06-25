<?php

namespace Database\Seeders;

use App\Models\Obat;
use Illuminate\Database\Seeder;

class ObatSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            ['nama' => 'Amoxicillin 500mg',      'kategori' => 'Antibiotik',        'satuan' => 'Tablet', 'stok' => 15,  'stok_minimum' => 20, 'harga' => 1500],
            ['nama' => 'Paracetamol 500mg',      'kategori' => 'Analgesik',         'satuan' => 'Tablet', 'stok' => 250, 'stok_minimum' => 50, 'harga' => 500],
            ['nama' => 'Ibuprofen 400mg',        'kategori' => 'Analgesik',         'satuan' => 'Tablet', 'stok' => 120, 'stok_minimum' => 30, 'harga' => 800],
            ['nama' => 'Antasida Tablet',        'kategori' => 'Gastrointestinal',  'satuan' => 'Tablet', 'stok' => 80,  'stok_minimum' => 20, 'harga' => 600],
            ['nama' => 'Cetirizine 10mg',        'kategori' => 'Antihistamin',      'satuan' => 'Tablet', 'stok' => 60,  'stok_minimum' => 20, 'harga' => 700],
            ['nama' => 'Vitamin C 500mg',        'kategori' => 'Vitamin',           'satuan' => 'Tablet', 'stok' => 200, 'stok_minimum' => 30, 'harga' => 400],
            ['nama' => 'Obat Batuk Hitam (OBH)', 'kategori' => 'Obat Batuk',        'satuan' => 'Botol',  'stok' => 8,   'stok_minimum' => 10, 'harga' => 12000],
            ['nama' => 'Salep Antibiotik',       'kategori' => 'Topikal',           'satuan' => 'Tube',   'stok' => 25,  'stok_minimum' => 10, 'harga' => 18000],
        ];

        foreach ($data as $i => $row) {
            Obat::updateOrCreate(
                ['kode_obat' => 'OBT-'.str_pad((string) ($i + 1), 4, '0', STR_PAD_LEFT)],
                $row
            );
        }
    }
}
