<?php

namespace App\Livewire;

use App\Models\Antrian;
use App\Models\PengaturanKlinik;
use App\Models\Poli;
use App\Services\ThermalPrinter;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class CetakAntrian extends Component
{
    public string $title = 'Cetak Antrian';

    public string $cari = '';

    public string $filterPoli = '';

    public bool $printerAktif = false;

    public ?int $tiketAktifId = null; // antrian yang terakhir diminta cetak (dipakai untuk markup .print-ticket)

    public ?string $pesanCetak = null;

    public function mount(): void
    {
        $this->printerAktif = config('printer.connection') !== 'none';
    }

    /**
     * Cetak ke printer thermal. Kalau printer tidak aktif/gagal, otomatis jatuh ke cetak browser.
     */
    public function cetak(int $id): void
    {
        $antrian = Antrian::with(['pasien', 'poli'])->findOrFail($id);
        $this->tiketAktifId = $antrian->id;

        if (! $this->printerAktif) {
            $this->pesanCetak = null;
            $this->dispatch('cetak-browser');

            return;
        }

        $namaKlinik = PengaturanKlinik::first()?->nama_klinik ?? 'SIM-KLINIK';

        $berhasil = app(ThermalPrinter::class)->cetakTiketPoli(
            $antrian->kode_antrian,
            $antrian->poli->nama,
            $antrian->pasien->nama,
            $namaKlinik,
            $antrian->created_at ?? now()
        );

        if ($berhasil) {
            $this->pesanCetak = 'Tiket '.$antrian->kode_antrian.' berhasil dicetak ke printer thermal.';
        } else {
            $this->pesanCetak = 'Printer thermal tidak merespons untuk tiket '.$antrian->kode_antrian.' — dialihkan ke cetak browser.';
            $this->dispatch('cetak-browser');
        }
    }

    /**
     * Cetak lewat dialog print browser (dipakai juga sebagai fallback manual).
     */
    public function cetakBrowser(int $id): void
    {
        $this->tiketAktifId = $id;
        $this->pesanCetak = null;
        $this->dispatch('cetak-browser');
    }

    public function render()
    {
        $antrians = Antrian::with(['pasien', 'poli'])
            ->whereDate('tanggal', today())
            ->when($this->filterPoli, fn ($q) => $q->where('poli_id', $this->filterPoli))
            ->when($this->cari, fn ($q) => $q->where('kode_antrian', 'like', "%{$this->cari}%")
                ->orWhereHas('pasien', fn ($q2) => $q2->where('nama', 'like', "%{$this->cari}%")))
            ->orderByDesc('created_at')
            ->limit(50)
            ->get();

        $tiketUntukCetak = $this->tiketAktifId
            ? Antrian::with(['pasien', 'poli'])->find($this->tiketAktifId)
            : null;

        return view('livewire.cetak-antrian', [
            'antrians' => $antrians,
            'polis' => Poli::where('aktif', true)->orderBy('kode')->get(),
            'tiketUntukCetak' => $tiketUntukCetak,
        ]);
    }
}
