<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('layanans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('poli_id')->nullable()->constrained()->nullOnDelete(); // null = tidak terikat poli tertentu
            $table->string('nama');
            $table->enum('kategori', ['konsultasi', 'tindakan', 'lainnya'])->default('lainnya');
            $table->unsignedBigInteger('harga')->default(0);
            $table->boolean('aktif')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('layanans');
    }
};
