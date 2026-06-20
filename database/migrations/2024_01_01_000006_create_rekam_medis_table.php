<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rekam_medis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pasien_id')->constrained()->cascadeOnDelete();
            $table->foreignId('poli_id')->nullable()->constrained()->nullOnDelete();
            $table->string('dokter')->nullable();
            $table->text('keluhan')->nullable();
            $table->text('diagnosis');
            $table->text('tindakan')->nullable();
            $table->text('catatan')->nullable();
            $table->date('tanggal_periksa');
            $table->timestamps();

            $table->index('tanggal_periksa');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rekam_medis');
    }
};
