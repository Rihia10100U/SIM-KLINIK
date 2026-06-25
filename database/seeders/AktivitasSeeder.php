<?php

namespace Database\Seeders;

use App\Models\Aktivitas;
use Illuminate\Database\Seeder;

class AktivitasSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            ['tipe' => 'success', 'pesan' => '<strong>Budi Santoso</strong> — selesai pemeriksaan di Poli Umum, dr. Hendra', 'menit_lalu' => 5],
            ['tipe' => 'info',    'pesan' => 'Resep <strong>#RX-0891</strong> diterima di farmasi dari dr. Hendra', 'menit_lalu' => 9],
            ['tipe' => 'warning', 'pesan' => 'Stok <strong>Amoxicillin 500mg</strong> tersisa 15 tablet — segera restock', 'menit_lalu' => 17],
            ['tipe' => 'success', 'pesan' => 'Pembayaran <strong>Rp 255.000</strong> diterima dari Budi Santoso (Tunai)', 'menit_lalu' => 22],
            ['tipe' => 'info',    'pesan' => 'Pasien baru <strong>Rina Kartika</strong> terdaftar dengan No. RM-2024-0319', 'menit_lalu' => 32],
        ];

        foreach ($data as $row) {
            Aktivitas::create([
                'tipe' => $row['tipe'],
                'pesan' => $row['pesan'],
                'waktu' => now()->subMinutes($row['menit_lalu']),
            ]);
        }
    }
}
