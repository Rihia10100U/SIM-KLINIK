<?php

namespace App\Livewire;

use App\Models\Antrian;
use App\Models\Poli;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.kiosk')]
class KioskTunggu extends Component
{
    public string $title = 'Layar Antrian';

    public function render()
    {
        $sedangDipanggil = Antrian::with('poli')
            ->whereDate('tanggal', today())
            ->where('status', 'dipanggil')
            ->orderByDesc('updated_at')
            ->limit(6)
            ->get();

        $menunggu = Poli::where('aktif', true)
            ->withCount(['antrians' => fn ($q) => $q
                ->whereDate('tanggal', today())
                ->where('status', 'menunggu')])
            ->orderBy('kode')
            ->get();

        return view('livewire.kiosk-tunggu', [
            'sedangDipanggil' => $sedangDipanggil,
            'menunggu'        => $menunggu,
        ]);
    }
}
