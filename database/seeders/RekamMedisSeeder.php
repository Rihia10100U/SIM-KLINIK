<?php

namespace Database\Seeders;

use App\Models\Pasien;
use App\Models\Poli;
use App\Models\RekamMedis;
use Illuminate\Database\Seeder;

class RekamMedisSeeder extends Seeder
{
    public function run(): void
    {
        $budi  = Pasien::where('nama', 'Budi Santoso')->first();
        $rina  = Pasien::where('nama', 'Rina Kartika')->first();
        $umum  = Poli::where('kode', 'A')->first();
        $gigi  = Poli::where('kode', 'B')->first();

        if (! $budi || ! $rina || ! $umum) {
            return; // jaga-jaga kalau PasienSeeder/PoliSeeder belum dijalankan
        }

        $data = [
            [
                'pasien_id'       => $budi->id,
                'poli_id'         => $umum->id,
                'dokter'          => 'dr. Hendra',
                'keluhan'         => 'Demam dan batuk sejak 3 hari',
                'diagnosis'       => 'ISPA (Infeksi Saluran Pernapasan Akut)',
                'tindakan'        => 'Pemberian obat penurun panas dan antibiotik',
                'catatan'         => 'Kontrol ulang jika demam tidak turun dalam 3 hari',
                'tanggal_periksa' => today(),
            ],
            [
                'pasien_id'       => $rina->id,
                'poli_id'         => $gigi?->id,
                'dokter'          => 'drg. Sari',
                'keluhan'         => 'Sakit gigi geraham kanan bawah',
                'diagnosis'       => 'Karies dentis',
                'tindakan'        => 'Penambalan gigi sementara',
                'catatan'         => 'Disarankan kontrol untuk penambalan permanen',
                'tanggal_periksa' => today()->subDay(),
            ],
        ];

        foreach ($data as $row) {
            RekamMedis::create($row);
        }
    }
}
