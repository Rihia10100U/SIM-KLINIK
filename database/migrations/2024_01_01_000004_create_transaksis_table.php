<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transaksis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pasien_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('antrian_id')->nullable()->constrained()->nullOnDelete();
            $table->string('deskripsi');
            $table->unsignedBigInteger('jumlah');   // disimpan dalam Rupiah (bukan sen)
            $table->string('metode', 30)->default('Tunai');
            $table->date('tanggal');
            $table->timestamps();

            $table->index('tanggal');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transaksis');
    }
};
