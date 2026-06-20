<?php

namespace Database\Seeders;

use App\Models\Pasien;
use App\Models\Transaksi;
use Illuminate\Database\Seeder;

class TransaksiSeeder extends Seeder
{
    public function run(): void
    {
        $pasiens = Pasien::all();

        // Transaksi hari ini — totalnya sengaja dibuat mendekati contoh desain (~Rp 8,2 Jt)
        $transaksiHariIni = [50000, 75000, 255000, 120000, 600000, 1500000, 2000000, 1200000, 800000, 600000, 500000, 500000];

        foreach ($transaksiHariIni as $jumlah) {
            Transaksi::create([
                'pasien_id' => $pasiens->random()->id,
                'deskripsi' => 'Pembayaran layanan klinik',
                'jumlah'    => $jumlah,
                'metode'    => fake()->randomElement(['Tunai', 'Transfer', 'QRIS']),
                'tanggal'   => now()->toDateString(),
            ]);
        }

        // Beberapa transaksi di hari-hari sebelumnya, biar data tidak kosong total
        foreach (range(1, 20) as $i) {
            Transaksi::factory()->create([
                'tanggal' => now()->subDays(rand(1, 6))->toDateString(),
            ]);
        }
    }
}
