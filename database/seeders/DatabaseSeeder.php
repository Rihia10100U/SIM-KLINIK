<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            PoliSeeder::class,      // harus duluan, dipakai oleh AntrianSeeder, RekamMedisSeeder & LayananSeeder
            LayananSeeder::class,
            PasienSeeder::class,    // harus duluan, dipakai oleh AntrianSeeder, TransaksiSeeder & RekamMedisSeeder
            AntrianSeeder::class,
            TransaksiSeeder::class,
            AktivitasSeeder::class,
            RekamMedisSeeder::class,
            ObatSeeder::class,
        ]);
    }
}
