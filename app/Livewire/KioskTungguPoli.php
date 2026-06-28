<?php

namespace App\Livewire;

use App\Models\Antrian;
use App\Models\MediaInformasi;
use App\Models\Poli;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.kiosk')]
class KioskTungguPoli extends Component
{
    public string $title = 'Papan Antrian — Pemeriksaan';

    // Properti backend untuk mencatat signature (id + updated_at) terakhir yang aktif
    public string $lastSignature = '';

    public function mount()
    {
        $terakhir = Antrian::where('tanggal', today())
            ->where('status', 'dipanggil')
            ->orderByDesc('updated_at')
            ->first();

        $this->lastSignature = $terakhir ? $terakhir->id.'-'.$terakhir->updated_at->timestamp : '';
    }

    public function render()
    {
        $dipanggil = Antrian::with('poli')
            ->where('tanggal', today())
            ->where('status', 'dipanggil')
            ->orderByDesc('updated_at')
            ->limit(6)
            ->get();

        $terbaru = $dipanggil->first();

        // LOGIKA DETEKSI ANTRIAN BARU / DIPANGGIL ULANG:
        $currentSignature = $terbaru ? $terbaru->id.'-'.$terbaru->updated_at->timestamp : '';
        if ($terbaru && $this->lastSignature !== $currentSignature) {

            $kodeEja = str_replace('-', ' ', $terbaru->kode_antrian);
            $kodeEja = implode('  ', str_split($kodeEja));
            $namaPoli = $terbaru->poli->nama ?? 'Poli Pemeriksaan';

            $message = "Nomor antrian, {$kodeEja}, silahkan menuju ke, {$namaPoli}";

            $this->dispatch('queue-called', message: $message, _suaraKey: 'simklinik_suara_poli');

            $this->lastSignature = $currentSignature;
        }

        // Single query ambil semua poli aktif + antrian menunggu pertama per poli
        $polis = Poli::where('aktif', true)->orderBy('kode')->get();
        $poliIds = $polis->pluck('id');

        $antrianPerPoliGroup = Antrian::whereIn('poli_id', $poliIds)
            ->where('tanggal', today())
            ->where('status', 'menunggu')
            ->orderBy('created_at')
            ->get()
            ->groupBy('poli_id');

        $antrianPerPoli = $polis->mapWithKeys(function ($poli) use ($antrianPerPoliGroup) {
            $items = $antrianPerPoliGroup->get($poli->id);
            $first = $items ? $items->first() : null;

            return [$poli->id => [
                'poli' => $poli,
                'antrians' => $first ? collect([$first]) : collect(),
                'jumlahMenunggu' => $first ? 1 : 0,
            ]];
        });

        return view('livewire.kiosk-tunggu-poli', [
            'dipanggil' => $dipanggil,
            'antrianPerPoli' => $antrianPerPoli,
            'media' => MediaInformasi::aktif()->latest()->first(),
        ]);
    }
}
