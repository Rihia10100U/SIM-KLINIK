<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            PoliSeeder::class,      // harus duluan, dipakai oleh AntrianSeeder
            PasienSeeder::class,    // harus duluan, dipakai oleh AntrianSeeder & TransaksiSeeder
            AntrianSeeder::class,
            TransaksiSeeder::class,
            AktivitasSeeder::class,
        ]);
    }
}
