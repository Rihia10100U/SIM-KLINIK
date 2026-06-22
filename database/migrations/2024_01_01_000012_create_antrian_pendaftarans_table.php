<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('antrian_pendaftarans', function (Blueprint $table) {
            $table->id();
            $table->string('kode_antrian', 10);  // REG-001, dst
            $table->enum('status', ['menunggu', 'dipanggil', 'selesai'])->default('menunggu');
            $table->foreignId('pasien_id')->nullable()->constrained()->nullOnDelete(); // diisi setelah didaftarkan
            $table->date('tanggal');
            $table->timestamps();

            $table->index(['tanggal', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('antrian_pendaftarans');
    }
};
