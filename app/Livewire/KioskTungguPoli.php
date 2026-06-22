<?php

namespace App\Livewire;

use App\Models\Antrian;
use App\Models\Poli;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.kiosk')]
class KioskTungguPoli extends Component
{
    public string $title = 'Papan Antrian — Pemeriksaan';

    public function render()
    {
        // Nomor yang sedang aktif dipanggil/diperiksa per poli — tampil besar
        $dipanggil = Antrian::with('poli')
            ->whereDate('tanggal', today())
            ->where('status', 'dipanggil')
            ->orderByDesc('updated_at')
            ->limit(6)
            ->get();

        // Jumlah menunggu per poli untuk statistik bawah
        $polis = Poli::where('aktif', true)
            ->withCount(['antrians as menunggu_count' => fn ($q) => $q
                ->whereDate('tanggal', today())
                ->where('status', 'menunggu')])
            ->withCount(['antrians as selesai_count' => fn ($q) => $q
                ->whereDate('tanggal', today())
                ->where('status', 'selesai')])
            ->orderBy('kode')
            ->get();

        return view('livewire.kiosk-tunggu-poli', [
            'dipanggil' => $dipanggil,
            'polis'     => $polis,
        ]);
    }
}
