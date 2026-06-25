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

    // Properti backend untuk mencatat ID terakhir yang aktif
    public $lastCalledId = null;

    public function mount()
    {
        // Ambil ID terakhir di database saat halaman di-load pertama kali.
        // Mengamankan browser dari auto-play block akibat refresh halaman.
        $this->lastCalledId = Antrian::whereDate('tanggal', today())
            ->where('status', 'dipanggil')
            ->orderByDesc('updated_at')
            ->value('id') ?? 0;
    }

    public function render()
    {
        $dipanggil = Antrian::with('poli')
            ->whereDate('tanggal', today())
            ->where('status', 'dipanggil')
            ->orderByDesc('updated_at')
            ->limit(6)
            ->get();

        $polis = Poli::where('aktif', true)
            ->withCount(['antrians as menunggu_count' => fn ($q) => $q
                ->whereDate('tanggal', today())
                ->where('status', 'menunggu')])
            ->withCount(['antrians as selesai_count' => fn ($q) => $q
                ->whereDate('tanggal', today())
                ->where('status', 'selesai')])
            ->orderBy('kode')
            ->get();

        $terbaru = $dipanggil->first();

        // LOGIKA DETEKSI ANTRIAN BARU:
        if ($terbaru && $this->lastCalledId !== $terbaru->id) {

            // Format pengejaan kode agar terbaca per huruf/angka (Contoh: "A-003" menjadi "A  0  0  3")
            $kodeEja = str_replace('-', ' ', $terbaru->kode_antrian);
            $kodeEja = implode('  ', str_split($kodeEja));
            $namaPoli = $terbaru->poli->nama ?? 'Poli Pemeriksaan';

            // Rakit kalimat instruksi panggilan
            $message = "Nomor antrian, {$kodeEja}, silahkan menuju ke, {$namaPoli}";

            // Tembakkan langsung ke event 'queue-called' yang didengar oleh call-queue.js
            $this->dispatch('queue-called', message: $message);

            // Update memori backend
            $this->lastCalledId = $terbaru->id;
        }

        return view('livewire.kiosk-tunggu-poli', [
            'dipanggil' => $dipanggil,
            'polis' => $polis,
        ]);
    }
}
