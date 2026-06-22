<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Role 'kasir' dihapus dari sistem mulai step ini — Apoteker sekarang
     * merangkap menerima pembayaran. User lama dengan role 'kasir'
     * dipindahkan otomatis ke 'apoteker' supaya tidak ada yang ke-lock out.
     */
    public function up(): void
    {
        DB::table('users')->where('role', 'kasir')->update(['role' => 'apoteker']);
    }

    public function down(): void
    {
        // Sengaja tidak dikembalikan — role 'kasir' sudah tidak ada lagi di enum Role.
    }
};
