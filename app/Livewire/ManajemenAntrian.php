<?php

namespace App\Livewire;

use App\Models\Antrian;
use App\Models\Notification;
use App\Models\Pasien;
use App\Models\Poli;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Manajemen Antrian Poli ')]
class ManajemenAntrian extends Component
{
    public string $title = 'Manajemen Antrian Poli';

    public string $filterPoli = '';

    public bool $showModal = false;

    public ?int $pasien_id = null;

    public ?int $poli_id = null;

    public string $cariPasien = '';

    public bool $bpjs = false;

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
        $this->reset(['pasien_id', 'poli_id', 'cariPasien', 'bpjs']);
        $this->resetErrorBag();
        $this->showModal = true;
    }

    public function tutupForm(): void
    {
        $this->showModal = false;
    }

    public function pilihPasien(int $id): void
    {
        $pasien = Pasien::findOrFail($id);
        $this->pasien_id = $pasien->id;
        $this->cariPasien = $pasien->nama.' ('.$pasien->no_rm.')';
    }

    public function updatedCariPasien(): void
    {
        if ($this->pasien_id !== null) {
            $pasien = Pasien::find($this->pasien_id);
            if ($pasien && $this->cariPasien === $pasien->nama.' ('.$pasien->no_rm.')') {
                return;
            }
        }
        $this->pasien_id = null;
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
            ->where('tanggal', today())
            ->count() + 1;

        Antrian::create([
            'pasien_id' => $data['pasien_id'],
            'poli_id' => $poli->id,
            'kode_antrian' => $poli->kode.'-'.str_pad((string) $urutan, 3, '0', STR_PAD_LEFT),
            'status' => 'menunggu',
            'bpjs' => $this->bpjs,
            'tanggal' => today(),
        ]);

        session()->flash('sukses', 'Pasien berhasil didaftarkan ke antrian '.$poli->nama.'.');
        $this->tutupForm();
    }

    // ===================== PERBAIKAN METHOD PANGGIL =====================
    public function panggil(int $id): void
    {
        $antrian = Antrian::where('tanggal', today())->with('poli', 'pasien')->findOrFail($id);
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
        $antrian = Antrian::where('tanggal', today())->with('poli', 'pasien')->findOrFail($id);

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
        $antrian = Antrian::where('tanggal', today())->with('pasien', 'poli')->findOrFail($id);
        $antrian->update(['status' => 'selesai']);

        foreach (User::whereIn('role', ['resepsionis', 'petugas_rekam_medis'])->cursor() as $user) {
            Notification::create([
                'user_id' => $user->id,
                'title' => 'Pemeriksaan selesai — '.$antrian->pasien->nama,
                'message' => 'Antrian '.$antrian->kode_antrian.' di '.$antrian->poli->nama.' sudah selesai diperiksa.',
                'type' => 'success',
                'link' => route('rekam-medis'),
            ]);
        }
    }

    public function batalkan(int $id): void
    {
        Antrian::where('tanggal', today())->findOrFail($id)->update(['status' => 'batal']);
    }

    public function render()
    {
        $semuaAntrian = Antrian::with(['pasien', 'poli'])
            ->where('tanggal', today())
            ->when($this->filterPoli, fn ($q) => $q->where('poli_id', $this->filterPoli))
            ->orderBy('created_at')
            ->get();

        return view('livewire.manajemen-antrian', [
            'antrianMenunggu' => $semuaAntrian->where('status', 'menunggu'),
            'antrianDipanggil' => $semuaAntrian->where('status', 'dipanggil'),
            'antrianSelesai' => $semuaAntrian->where('status', 'selesai'),
            'polis' => Poli::orderBy('kode')->get(),
        ]);
    }
}
