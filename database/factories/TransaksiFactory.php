<?php

namespace Database\Factories;

use App\Models\Pasien;
use App\Models\Transaksi;
use Illuminate\Database\Eloquent\Factories\Factory;

class TransaksiFactory extends Factory
{
    protected $model = Transaksi::class;

    public function definition(): array
    {
        return [
            'pasien_id' => Pasien::inRandomOrder()->value('id'),
            'deskripsi' => 'Pembayaran layanan klinik',
            'jumlah' => $this->faker->randomElement([50000, 75000, 100000, 150000, 200000, 255000, 500000]),
            'metode' => $this->faker->randomElement(['Tunai', 'Transfer', 'QRIS']),
            'tanggal' => now()->toDateString(),
        ];
    }
}
