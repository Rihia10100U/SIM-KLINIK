<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transaksis', function (Blueprint $table) {
            // Transaksi lama (sebelum step ini) otomatis dianggap 'lunas' lewat default.
            $table->enum('status', ['menunggu_pembayaran', 'lunas'])->default('lunas')->after('metode');
        });
    }

    public function down(): void
    {
        Schema::table('transaksis', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
