<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('antrians', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pasien_id')->constrained()->cascadeOnDelete();
            $table->foreignId('poli_id')->constrained()->cascadeOnDelete();
            $table->string('kode_antrian', 10);     // mis. A-002
            $table->enum('status', ['menunggu', 'dipanggil', 'selesai', 'batal'])->default('menunggu');
            $table->date('tanggal');                // tanggal antrian (dipakai untuk grafik & stat harian)
            $table->timestamps();

            $table->index(['tanggal', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('antrians');
    }
};
