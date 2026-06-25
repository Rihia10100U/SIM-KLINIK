<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('antrians', function (Blueprint $table) {
            $table->index(['status', 'poli_id', 'tanggal', 'created_at'], 'antrians_status_poli_tanggal_idx');
            $table->index(['tanggal', 'status', 'updated_at'], 'antrians_tanggal_status_updated_idx');
        });
    }

    public function down(): void
    {
        Schema::table('antrians', function (Blueprint $table) {
            $table->dropIndex('antrians_status_poli_tanggal_idx');
            $table->dropIndex('antrians_tanggal_status_updated_idx');
        });
    }
};
