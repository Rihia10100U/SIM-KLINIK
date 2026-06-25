<?php

namespace App\Livewire;

use App\Models\Antrian;
use App\Models\Pasien;
use App\Models\Poli;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Manajemen Antrian')]
class ManajemenAntrian extends Component
{
    public string $title = 'Manajemen Antrian';

    public string $filterPoli = '';

    public bool $showModal = false;

    public ?int $pasien_id = null;

    public ?int $poli_id = null;

    public string $cariPasien = '';

    protected function rules(): array
    {
        return [
            'pasien_id' => 'required|exists:pasiens,id',
            'poli_id' => 'required|exists:polis,id',
        ];
    }

    protected array $messages = [
        'pasien_id.required' => 'Pilih pasien terlebih dahulu dari hasil pencarian.',
        'poli_id.required' => 'Pilih poli tujuan.',
    ];

    public function bukaForm(): void
    {
        $this->reset(['pasien_id', 'poli_id', 'cariPasien']);
        $this->resetErrorBag();
        $this->showModal = true;
    }

    public function tutupForm(): void
    {
        $this->showModal = false;
    }

    public function updatedCariPasien(): void
    {
        $this->pasien_id = null;
    }

    public function pilihPasien(int $id): void
    {
        $pasien = Pasien::findOrFail($id);
        $this->pasien_id = $pasien->id;
        $this->cariPasien = $pasien->nama.' ('.$pasien->no_rm.')';
    }

    public function pasienOptions(): Collection
    {
        if ($this->pasien_id !== null || strlen($this->cariPasien) < 2) {
            return new Collection;
        }

        return Pasien::query()
            ->where('nama', 'like', "%{$this->cariPasien}%")
            ->orWhere('no_rm', 'like', "%{$this->cariPasien}%")
            ->limit(8)
            ->get();
    }

    public function daftarkan(): void
    {
        $data = $this->validate();
        $poli = Poli::findOrFail($data['poli_id']);

        $urutan = Antrian::where('poli_id', $poli->id)
            ->whereDate('tanggal', today())
            ->count() + 1;

        Antrian::create([
            'pasien_id' => $data['pasien_id'],
            'poli_id' => $poli->id,
            'kode_antrian' => $poli->kode.'-'.str_pad((string) $urutan, 3, '0', STR_PAD_LEFT),
            'status' => 'menunggu',
            'tanggal' => today(),
        ]);

        session()->flash('sukses', 'Pasien berhasil didaftarkan ke antrian '.$poli->nama.'.');
        $this->tutupForm();
    }

    // ===================== PERBAIKAN METHOD PANGGIL =====================
    public function panggil(int $id): void
    {
        $antrian = Antrian::whereDate('tanggal', today())->with('poli', 'pasien')->findOrFail($id);
        $antrian->update(['status' => 'dipanggil']);

        // Rangkai teks suara agar dieja per huruf/angka oleh JavaScript
        $kodeEja = str_replace('-', ' ', $antrian->kode_antrian);
        $kodeEja = implode('  ', str_split($kodeEja));
        $namaPoli = $antrian->poli->nama ?? 'Poli Pemeriksaan';
        $message = "Nomor antrian, {$kodeEja}, silahkan menuju ke, {$namaPoli}";

        // Tembakkan event ke call-queue.js (Jika di-test langsung di halaman admin)
        $this->dispatch('queue-called', message: $message);
    }

    // ===================== PERBAIKAN METHOD PANGGIL ULANG =====================
    public function panggilUlang(int $id): void
    {
        $antrian = Antrian::whereDate('tanggal', today())->with('poli', 'pasien')->findOrFail($id);

        // Paksa perbarui field 'updated_at' di DB agar halaman Kiosk TV mendeteksi adanya aktivitas baru
        $antrian->touch();

        // Rangkai teks suara
        $kodeEja = str_replace('-', ' ', $antrian->kode_antrian);
        $kodeEja = implode('  ', str_split($kodeEja));
        $namaPoli = $antrian->poli->nama ?? 'Poli Pemeriksaan';
        $message = "Nomor antrian, {$kodeEja}, silahkan menuju ke, {$namaPoli}";

        // Jalankan suara lokal di komputer admin
        $this->dispatch('queue-called', message: $message);

        session()->flash('sukses', 'Antrian '.$antrian->kode_antrian.' ('.$antrian->pasien->nama.') berhasil dipanggil ulang.');
    }

    public function selesaikan(int $id): void
    {
        Antrian::whereDate('tanggal', today())->findOrFail($id)->update(['status' => 'selesai']);
    }

    public function batalkan(int $id): void
    {
        Antrian::whereDate('tanggal', today())->findOrFail($id)->update(['status' => 'batal']);
    }

    public function render()
    {
        $query = Antrian::with(['pasien', 'poli'])
            ->whereDate('tanggal', today())
            ->when($this->filterPoli, fn ($q) => $q->where('poli_id', $this->filterPoli))
            ->orderBy('created_at');

        return view('livewire.manajemen-antrian', [
            'antrianMenunggu' => (clone $query)->where('status', 'menunggu')->get(),
            'antrianDipanggil' => (clone $query)->where('status', 'dipanggil')->get(),
            'antrianSelesai' => (clone $query)->where('status', 'selesai')->get(),
            'polis' => Poli::orderBy('kode')->get(),
        ]);
    }
}
