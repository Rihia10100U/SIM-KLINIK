<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class PasienFactory extends Factory
{
    protected $model = \App\Models\Pasien::class;

    public function definition(): array
    {
        return [
            'no_rm'          => 'RM-2024-' . $this->faker->unique()->numberBetween(1000, 9999),
            'nama'           => $this->faker->name(),
            'nik'            => $this->faker->numerify('################'),
            'tanggal_lahir'  => $this->faker->dateTimeBetween('-70 years', '-1 years'),
            'jenis_kelamin'  => $this->faker->randomElement(['L', 'P']),
            'no_hp'          => $this->faker->numerify('08##########'),
            'alamat'         => $this->faker->address(),
        ];
    }
}
