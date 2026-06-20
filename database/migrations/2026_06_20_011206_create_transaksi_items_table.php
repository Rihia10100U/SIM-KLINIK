<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transaksi_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaksi_id')->constrained()->cascadeOnDelete();
            $table->foreignId('obat_id')->nullable()->constrained()->nullOnDelete();
            $table->string('nama_item');
            $table->enum('jenis', ['konsultasi', 'obat', 'tindakan', 'lainnya'])->default('lainnya');
            $table->integer('qty')->default(1);
            $table->unsignedBigInteger('harga_satuan')->default(0);
            $table->unsignedBigInteger('subtotal')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transaksi_items');
    }
};