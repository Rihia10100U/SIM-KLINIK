<?php

namespace Database\Seeders;

use App\Models\Antrian;
use App\Models\Pasien;
use App\Models\Poli;
use Illuminate\Database\Seeder;

class AntrianSeeder extends Seeder
{
    public function run(): void
    {
        $pasiens = Pasien::all();
        $polis   = Poli::all();

        // Pola jumlah kunjungan Senin - Minggu, mengikuti angka di contoh desain dashboard
        $polaMingguan = [
            'jumlah' => [60, 65, 50, 58, 64, 45, 43], // Sen, Sel, Rab, Kam, Jum, Sab, Min
        ];

        $awalMinggu = today()->startOfWeek(); // Senin minggu berjalan

        foreach ($polaMingguan['jumlah'] as $index => $jumlah) {
            $tanggal   = $awalMinggu->copy()->addDays($index);
            $isHariIni = $tanggal->isToday();

            for ($i = 1; $i <= $jumlah; $i++) {
                $poli = $polis->random();

                // 12 antrian pertama hari ini sengaja dibuat 'menunggu',
                // supaya cocok dengan panel "Antrian Aktif" & stat "Pasien dalam antrian"
                $status = ($isHariIni && $i <= 12)
                    ? 'menunggu'
                    : fake()->randomElement(['selesai', 'selesai', 'selesai', 'batal']);

                Antrian::create([
                    'pasien_id'    => $pasiens->random()->id,
                    'poli_id'      => $poli->id,
                    'kode_antrian' => $poli->kode . '-' . str_pad($i, 3, '0', STR_PAD_LEFT),
                    'status'       => $status,
                    'tanggal'      => $tanggal->toDateString(),
                ]);
            }
        }
    }
}
