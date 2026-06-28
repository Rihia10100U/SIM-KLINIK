<?php

namespace App\Livewire;

use App\Models\AntrianPendaftaran;
use App\Models\MediaInformasi;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.kiosk')]
class KioskTungguRegistrasi extends Component
{
    public string $title = 'Papan Antrian — Registrasi';

    public string $lastSignature = '';

    public $kodeTerbaru = null;

    public function mount()
    {
        $terakhir = AntrianPendaftaran::whereDate('tanggal', today())
            ->where('status', 'dipanggil')
            ->orderByDesc('updated_at')
            ->first();

        $this->lastSignature = $terakhir ? $terakhir->id.'-'.$terakhir->updated_at->timestamp : '';
    }

    public function render()
    {
        $dipanggil = AntrianPendaftaran::whereDate('tanggal', today())
            ->where('status', 'dipanggil')
            ->orderByDesc('updated_at')
            ->limit(8)
            ->get();

        $antrianMenunggu = AntrianPendaftaran::whereDate('tanggal', today())
            ->where('status', 'menunggu')
            ->orderBy('created_at')
            ->limit(8)
            ->get();

        $jumlahMenunggu = $antrianMenunggu->count();

        $terbaru = $dipanggil->first();
        $this->kodeTerbaru = $terbaru?->kode_antrian;

        $currentSignature = $terbaru ? $terbaru->id.'-'.$terbaru->updated_at->timestamp : '';
        if ($terbaru && $this->lastSignature !== $currentSignature) {
            $kodeEja = str_replace('-', ' ', $terbaru->kode_antrian);
            $kodeEja = implode('  ', str_split($kodeEja));
            $message = "Nomor antrian, {$kodeEja}, silahkan menuju ke, loket pendaftaran";

            $this->dispatch('queue-called', message: $message, _suaraKey: 'simklinik_suara_registrasi');

            $this->lastSignature = $currentSignature;
        }

        return view('livewire.kiosk-tunggu-registrasi', [
            'dipanggil' => $dipanggil,
            'antrianMenunggu' => $antrianMenunggu,
            'jumlahMenunggu' => $jumlahMenunggu,
            'media' => MediaInformasi::aktif()->latest()->first(),
        ]);
    }
}
