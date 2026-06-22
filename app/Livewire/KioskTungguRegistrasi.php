<?php

namespace App\Livewire;

use App\Models\AntrianPendaftaran;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.kiosk')]
class KioskTungguRegistrasi extends Component
{
    public string $title = 'Papan Antrian — Registrasi';

    public function render()
    {
        $dipanggil = AntrianPendaftaran::whereDate('tanggal', today())
            ->where('status', 'dipanggil')
            ->orderByDesc('updated_at')
            ->limit(8)
            ->get();

        $jumlahMenunggu = AntrianPendaftaran::whereDate('tanggal', today())
            ->where('status', 'menunggu')
            ->count();

        $totalHariIni = AntrianPendaftaran::whereDate('tanggal', today())->count();

        return view('livewire.kiosk-tunggu-registrasi', [
            'dipanggil'      => $dipanggil,
            'jumlahMenunggu' => $jumlahMenunggu,
            'totalHariIni'   => $totalHariIni,
        ]);
    }
}
