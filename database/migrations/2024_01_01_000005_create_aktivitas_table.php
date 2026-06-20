<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aktivitas', function (Blueprint $table) {
            $table->id();
            $table->enum('tipe', ['success', 'info', 'warning', 'danger'])->default('info');
            $table->string('pesan');               // boleh mengandung <strong> untuk penekanan teks
            $table->timestamp('waktu');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aktivitas');
    }
};
