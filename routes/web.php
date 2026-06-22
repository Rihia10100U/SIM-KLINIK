<?php

use App\Livewire\Auth\Login;
use App\Livewire\CetakAntrian;
use App\Livewire\Dashboard;
use App\Livewire\DataLayanan;
use App\Livewire\Farmasi;
use App\Livewire\KasirBilling;
use App\Livewire\KioskAntrian;
use App\Livewire\KioskTungguPoli;
use App\Livewire\KioskTungguRegistrasi;
use App\Livewire\Laporan;
use App\Livewire\ManajemenAntrian;
use App\Livewire\ManajemenUser;
use App\Livewire\PanggilanPendaftaran;
use App\Livewire\PendaftaranPasien;
use App\Livewire\Pengaturan;
use App\Livewire\RekamMedis;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// ===== Halaman kiosk publik (tanpa login, untuk layar/perangkat di klinik) =====
Route::get('/kiosk/antrian', KioskAntrian::class)->name('kiosk.antrian');

// Papan antrian terpisah: registrasi & poli
Route::get('/kiosk/tunggu-registrasi', KioskTungguRegistrasi::class)->name('kiosk.tunggu.registrasi');
Route::get('/kiosk/tunggu-poli', KioskTungguPoli::class)->name('kiosk.tunggu.poli');

// Route lama /kiosk/tunggu → redirect ke papan poli (backward-compatible)
Route::redirect('/kiosk/tunggu', '/kiosk/tunggu-poli')->name('kiosk.tunggu');

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

    Route::get('/dashboard', Dashboard::class)->name('dashboard');
    Route::get('/pengaturan', Pengaturan::class)->name('pengaturan');

    Route::middleware('role:admin,resepsionis')->group(function () {
        Route::get('/panggilan-pendaftaran', PanggilanPendaftaran::class)->name('panggilan-pendaftaran');
        Route::get('/cetak-antrian', CetakAntrian::class)->name('cetak-antrian');
        Route::get('/pendaftaran-pasien', PendaftaranPasien::class)->name('pendaftaran-pasien');
    });

    Route::middleware('role:admin,resepsionis,dokter,petugas_rekam_medis')->group(function () {
        Route::get('/manajemen-antrian', ManajemenAntrian::class)->name('manajemen-antrian');
    });

    Route::middleware('role:admin,dokter,petugas_rekam_medis')->group(function () {
        Route::get('/rekam-medis', RekamMedis::class)->name('rekam-medis');
    });

    Route::middleware('role:admin,apoteker')->group(function () {
        Route::get('/farmasi', Farmasi::class)->name('farmasi');
        Route::get('/kasir-billing', KasirBilling::class)->name('kasir-billing');
    });

    Route::middleware('role:admin')->group(function () {
        Route::get('/data-layanan', DataLayanan::class)->name('data-layanan');
        Route::get('/laporan', Laporan::class)->name('laporan');
        Route::get('/manajemen-user', ManajemenUser::class)->name('manajemen-user');
    });
});
