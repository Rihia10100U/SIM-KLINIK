<?php

use App\Livewire\Auth\Login;
use App\Livewire\CetakAntrian;
use App\Livewire\Dashboard;
use App\Livewire\Farmasi;
use App\Livewire\KasirBilling;
use App\Livewire\KioskAntrian;
use App\Livewire\KioskTungguPoli;
use App\Livewire\KioskTungguRegistrasi;
use App\Livewire\Laporan;
use App\Livewire\LayananPoli;
use App\Livewire\ManajemenAntrian;
use App\Livewire\ManajemenUser;
use App\Livewire\MediaInformasi;
use App\Livewire\Notifications;
use App\Livewire\PendaftaranPasien;
use App\Livewire\Pengaturan;
use App\Livewire\RekamMedis;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// ===== Kiosk publik (tanpa login) =====
Route::get('/kiosk/antrian', KioskAntrian::class)->name('kiosk.antrian');
Route::get('/kiosk/tunggu-registrasi', KioskTungguRegistrasi::class)->name('kiosk.tunggu.registrasi');
Route::get('/kiosk/tunggu-poli', KioskTungguPoli::class)->name('kiosk.tunggu.poli');
Route::redirect('/kiosk/tunggu', '/kiosk/tunggu-poli')->name('kiosk.tunggu');

// ===== Tamu =====
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

// ===== Wajib login =====
Route::middleware('auth')->group(function () {
    Route::get('/', fn () => redirect()->route('dashboard'));

    Route::get('/dashboard', Dashboard::class)->name('dashboard');
    Route::get('/notifications', Notifications::class)->name('notifications');
    Route::get('/pengaturan', Pengaturan::class)->name('pengaturan');

    Route::middleware('role:admin,resepsionis')->group(function () {
        Route::get('/pendaftaran-pasien', PendaftaranPasien::class)->name('pendaftaran-pasien');
        Route::redirect('/panggilan-pendaftaran', '/pendaftaran-pasien')->name('panggilan-pendaftaran');
        Route::get('/cetak-antrian', CetakAntrian::class)->name('cetak-antrian');
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

    // Khusus Admin
    Route::middleware('role:admin')->group(function () {
        Route::get('/laporan', Laporan::class)->name('laporan');
        Route::get('/manajemen-user', ManajemenUser::class)->name('manajemen-user');

        // Halaman gabungan Layanan & Poli (menggantikan /data-layanan & /manajemen-poli)
        Route::get('/layanan-poli', LayananPoli::class)->name('layanan-poli');

        Route::get('/media-informasi', MediaInformasi::class)->name('media-informasi');

        // Backward-compatible redirect dari route lama
        Route::redirect('/data-layanan', '/layanan-poli')->name('data-layanan');
        Route::redirect('/manajemen-poli', '/layanan-poli')->name('manajemen-poli');
    });
});
