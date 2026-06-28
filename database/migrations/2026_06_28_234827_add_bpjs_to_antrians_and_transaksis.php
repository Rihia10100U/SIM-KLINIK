<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('antrians', function (Blueprint $table) {
            $table->boolean('bpjs')->default(false)->after('status');
        });

        Schema::table('transaksis', function (Blueprint $table) {
            $table->boolean('bpjs')->default(false)->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('antrians', function (Blueprint $table) {
            $table->dropColumn('bpjs');
        });

        Schema::table('transaksis', function (Blueprint $table) {
            $table->dropColumn('bpjs');
        });
    }
};
