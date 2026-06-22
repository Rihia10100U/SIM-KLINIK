<?php

namespace App\Livewire;

use App\Models\Antrian;
use App\Models\AntrianPendaftaran;
use App\Models\Poli;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.kiosk')]
class KioskTunggu extends Component
{
    public string $title = 'Layar Antrian';

    public function render()
    {
        $pendaftaranDipanggil = AntrianPendaftaran::whereDate('tanggal', today())
            ->where('status', 'dipanggil')
            ->orderByDesc('updated_at')
            ->limit(4)
            ->get();

        $poliDipanggil = Antrian::with('poli')
            ->whereDate('tanggal', today())
            ->where('status', 'dipanggil')
            ->orderByDesc('updated_at')
            ->limit(6)
            ->get();

        $menungguPerPoli = Poli::where('aktif', true)
            ->withCount(['antrians' => fn ($q) => $q
                ->whereDate('tanggal', today())
                ->where('status', 'menunggu')])
            ->orderBy('kode')
            ->get();

        $pendaftaranMenunggu = AntrianPendaftaran::whereDate('tanggal', today())
            ->where('status', 'menunggu')
            ->count();

        return view('livewire.kiosk-tunggu', [
            'pendaftaranDipanggil' => $pendaftaranDipanggil,
            'poliDipanggil'        => $poliDipanggil,
            'menungguPerPoli'      => $menungguPerPoli,
            'pendaftaranMenunggu'  => $pendaftaranMenunggu,
        ]);
    }
}
