<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('obats', function (Blueprint $table) {
            $table->id();
            $table->string('kode_obat')->unique();    // OBT-0001
            $table->string('nama');
            $table->string('kategori')->nullable();    // Antibiotik, Analgesik, dst
            $table->string('satuan', 30)->default('Tablet');
            $table->integer('stok')->default(0);
            $table->integer('stok_minimum')->default(10); // batas bawah sebelum dianggap "menipis"
            $table->unsignedBigInteger('harga')->default(0); // harga jual per satuan
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('obats');
    }
};
