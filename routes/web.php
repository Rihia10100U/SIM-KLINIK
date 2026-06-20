<?php

use App\Livewire\Auth\Login;
use App\Livewire\Dashboard;
use App\Livewire\DataLayanan;
use App\Livewire\Farmasi;
use App\Livewire\KasirBilling;
use App\Livewire\KioskAntrian;
use App\Livewire\KioskTunggu;
use App\Livewire\Laporan;
use App\Livewire\ManajemenAntrian;
use App\Livewire\ManajemenUser;
use App\Livewire\PendaftaranPasien;
use App\Livewire\Pengaturan;
use App\Livewire\RekamMedis;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// ===== Halaman kiosk publik (tanpa login, untuk layar/perangkat di klinik) =====
Route::get('/kiosk/tunggu', KioskTunggu::class)->name('kiosk.tunggu');
Route::get('/kiosk/antrian', KioskAntrian::class)->name('kiosk.antrian');

// ===== Halaman tamu (belum login) =====
Route::middleware('guest')->group(function () {
    Route::get('/login', Login::class)->name('login');
});

// ===== Logout =====
Route::post('/logout', function () {
    Auth::logout();

    request()->session()->invalidate();
    request()->session()->regenerateToken();

    return redirect()->route('login');
})->middleware('auth')->name('logout');

// ===== Halaman yang wajib login =====
Route::middleware('auth')->group(function () {
    Route::get('/', function () {
        return redirect()->route('dashboard');
    });

    // Semua role yang sudah login boleh akses
    Route::get('/dashboard', Dashboard::class)->name('dashboard');
    Route::get('/pengaturan', Pengaturan::class)->name('pengaturan');

    // Admin + Resepsionis
    Route::middleware('role:admin,resepsionis')->group(function () {
        Route::get('/pendaftaran-pasien', PendaftaranPasien::class)->name('pendaftaran-pasien');
    });

    // Admin + Resepsionis + Dokter
    Route::middleware('role:admin,resepsionis,dokter')->group(function () {
        Route::get('/manajemen-antrian', ManajemenAntrian::class)->name('manajemen-antrian');
    });

    // Admin + Dokter
    Route::middleware('role:admin,dokter')->group(function () {
        Route::get('/rekam-medis', RekamMedis::class)->name('rekam-medis');
    });

    // Admin + Apoteker
    Route::middleware('role:admin,apoteker')->group(function () {
        Route::get('/farmasi', Farmasi::class)->name('farmasi');
    });

    // Admin + Kasir
    Route::middleware('role:admin,kasir')->group(function () {
        Route::get('/kasir-billing', KasirBilling::class)->name('kasir-billing');
    });

    // Khusus Admin
    Route::middleware('role:admin')->group(function () {
        Route::get('/data-layanan', DataLayanan::class)->name('data-layanan');

        Route::get('/laporan', Laporan::class)->name('laporan');

        Route::get('/manajemen-user', ManajemenUser::class)->name('manajemen-user');
    });
});
