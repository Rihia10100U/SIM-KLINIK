<?php

namespace App\Livewire;

use App\Models\AntrianPendaftaran;
use App\Models\PengaturanKlinik;
use App\Services\ThermalPrinter;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.kiosk')]
class KioskAntrian extends Component
{
    public string $title = 'Ambil Nomor Antrian';

    public ?AntrianPendaftaran $tiket = null;

    public bool $printerAktif = false;

    public ?string $pesanPrinter = null;

    public function mount(): void
    {
        $this->printerAktif = config('printer.connection') !== 'none';
    }

    public function ambilNomor(): void
    {
        $urutan = AntrianPendaftaran::whereDate('tanggal', today())->count() + 1;

        $this->tiket = AntrianPendaftaran::create([
            'kode_antrian' => 'REG-' . str_pad((string) $urutan, 3, '0', STR_PAD_LEFT),
            'status'       => 'menunggu',
            'tanggal'      => today(),
        ]);

        $this->cetakKeThermal();

        $this->dispatch('tiket-dibuat');
    }

    public function cetakUlang(): void
    {
        $this->cetakKeThermal();
        $this->dispatch('tiket-dibuat');
    }

    public function ulangi(): void
    {
        $this->tiket       = null;
        $this->pesanPrinter = null;
    }

    private function cetakKeThermal(): void
    {
        if (! $this->printerAktif || ! $this->tiket) {
            $this->pesanPrinter = null;

            return;
        }

        $namaKlinik = PengaturanKlinik::first()?->nama_klinik ?? 'SIM-KLINIK';

        $berhasil = app(ThermalPrinter::class)->cetakTiketAntrian(
            $this->tiket->kode_antrian,
            $namaKlinik,
            now()
        );

        $this->pesanPrinter = $berhasil
            ? 'Tiket berhasil dicetak ke printer thermal.'
            : 'Printer thermal tidak merespons — gunakan tombol "Cetak via Browser" di bawah.';
    }

    public function render()
    {
        return view('livewire.kiosk-antrian');
    }
}
