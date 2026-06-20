<?php

namespace App\Livewire;

use App\Models\Antrian;
use App\Models\Pasien;
use App\Models\Poli;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.kiosk')]
class KioskAntrian extends Component
{
    public string $title = 'Ambil Nomor Antrian';

    public string $step = 'cari'; // cari -> pilih-poli -> tiket

    public string $noRm = '';

    public ?Pasien $pasien = null;

    public ?string $errorPasien = null;

    public ?Antrian $tiket = null;

    public function cariPasien(): void
    {
        $this->errorPasien = null;

        $pasien = Pasien::where('no_rm', trim($this->noRm))->first();

        if (! $pasien) {
            $this->errorPasien = 'No. RM tidak ditemukan. Pastikan kamu sudah terdaftar di loket Pendaftaran Pasien.';

            return;
        }

        $this->pasien = $pasien;
        $this->step   = 'pilih-poli';
    }

    public function pilihPoli(int $poliId): void
    {
        $poli = Poli::findOrFail($poliId);

        $urutan = Antrian::where('poli_id', $poli->id)
            ->whereDate('tanggal', today())
            ->count() + 1;

        $tiket = Antrian::create([
            'pasien_id'    => $this->pasien->id,
            'poli_id'      => $poli->id,
            'kode_antrian' => $poli->kode . '-' . str_pad((string) $urutan, 3, '0', STR_PAD_LEFT),
            'status'       => 'menunggu',
            'tanggal'      => today(),
        ]);

        $this->tiket = $tiket->load('poli');
        $this->step  = 'tiket';
    }

    public function ulangi(): void
    {
        $this->reset(['step', 'noRm', 'pasien', 'errorPasien', 'tiket']);
        $this->step = 'cari';
    }

    public function render()
    {
        return view('livewire.kiosk-antrian', [
            'polis' => Poli::where('aktif', true)->orderBy('kode')->get(),
        ]);
    }
}
