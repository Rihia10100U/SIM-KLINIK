<?php

namespace App\Livewire;

use App\Models\Layanan;
use App\Models\Poli;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class LayananPoli extends Component
{
    public string $title = 'Layanan & Poli';

    // ===== Poli yang sedang dibuka accordion-nya =====
    public ?int $poliAktifId = null;

    // ===== Modal Poli =====
    public bool $showPoliModal = false;

    public ?int $editPoliId = null;

    public string $poliKode = '';

    public string $poliNama = '';

    public bool $poliAktif = true;

    // ===== Modal Layanan =====
    public bool $showLayananModal = false;

    public ?int $editLayananId = null;

    public ?int $layananPoliId = null; // poli terkait

    public string $layananNama = '';

    public string $layananKategori = 'konsultasi';

    public int $layananHarga = 0;

    public bool $layananAktif = true;

    // ===== Validasi Poli =====
    protected function rulesPoli(): array
    {
        return [
            'poliKode' => [
                'required', 'string', 'max:5',
                Rule::unique('polis', 'kode')->ignore($this->editPoliId),
            ],
            'poliNama' => 'required|string|max:100',
        ];
    }

    protected array $messagesPoli = [
        'poliKode.required' => 'Kode poli wajib diisi.',
        'poliKode.unique' => 'Kode poli sudah dipakai poli lain.',
        'poliNama.required' => 'Nama poli wajib diisi.',
    ];

    // ===== Validasi Layanan =====
    protected function rulesLayanan(): array
    {
        return [
            'layananNama' => 'required|string|max:255',
            'layananKategori' => 'required|in:konsultasi,tindakan,lainnya',
            'layananHarga' => 'required|integer|min:0',
        ];
    }

    protected array $messagesLayanan = [
        'layananNama.required' => 'Nama layanan wajib diisi.',
        'layananHarga.required' => 'Harga layanan wajib diisi.',
    ];

    // ===== POLI =====

    public function toggleAccordion(int $id): void
    {
        $this->poliAktifId = ($this->poliAktifId === $id) ? null : $id;
    }

    public function bukaPoliModal(?int $poliId = null): void
    {
        $this->resetPoliForm();

        if ($poliId) {
            $poli = Poli::findOrFail($poliId);
            $this->editPoliId = $poli->id;
            $this->poliKode = $poli->kode;
            $this->poliNama = $poli->nama;
            $this->poliAktif = $poli->aktif;
        }

        $this->showPoliModal = true;
    }

    public function tutupPoliModal(): void
    {
        $this->showPoliModal = false;
        $this->resetPoliForm();
    }

    public function simpanPoli(): void
    {
        $data = $this->validate($this->rulesPoli(), $this->messagesPoli);

        $payload = [
            'kode' => strtoupper($data['poliKode']),
            'nama' => $data['poliNama'],
            'aktif' => $this->poliAktif,
        ];

        if ($this->editPoliId) {
            Poli::findOrFail($this->editPoliId)->update($payload);
            session()->flash('sukses', 'Poli berhasil diperbarui.');
        } else {
            $poli = Poli::create($payload);
            $this->poliAktifId = $poli->id; // buka accordion poli baru otomatis
            session()->flash('sukses', 'Poli baru berhasil ditambahkan.');
        }

        $this->tutupPoliModal();
    }

    public function toggleAktifPoli(int $id): void
    {
        $poli = Poli::findOrFail($id);
        $poli->update(['aktif' => ! $poli->aktif]);
    }

    public function hapusPoli(int $id): void
    {
        $poli = Poli::withCount('antrians')->findOrFail($id);

        if ($poli->antrians_count > 0) {
            session()->flash('gagal', 'Poli '.$poli->nama.' tidak bisa dihapus karena masih punya data antrian.');

            return;
        }

        // Hapus juga layanan yang terikat ke poli ini
        Layanan::where('poli_id', $id)->delete();
        $poli->delete();

        if ($this->poliAktifId === $id) {
            $this->poliAktifId = null;
        }

        session()->flash('sukses', 'Poli dan layanannya berhasil dihapus.');
    }

    private function resetPoliForm(): void
    {
        $this->reset(['editPoliId', 'poliKode', 'poliNama']);
        $this->poliAktif = true;
        $this->resetErrorBag();
    }

    // ===== LAYANAN =====

    public function bukaLayananModal(?int $layananId = null, ?int $poliId = null): void
    {
        $this->resetLayananForm();

        if ($layananId) {
            $l = Layanan::findOrFail($layananId);
            $this->editLayananId = $l->id;
            $this->layananPoliId = $l->poli_id;
            $this->layananNama = $l->nama;
            $this->layananKategori = $l->kategori;
            $this->layananHarga = $l->harga;
            $this->layananAktif = $l->aktif;
        } else {
            // Tambah baru — poli sudah ditentukan dari konteks accordion
            $this->layananPoliId = $poliId;
            $this->layananKategori = 'konsultasi';
        }

        $this->showLayananModal = true;
    }

    public function tutupLayananModal(): void
    {
        $this->showLayananModal = false;
        $this->resetLayananForm();
    }

    public function simpanLayanan(): void
    {
        $data = $this->validate($this->rulesLayanan(), $this->messagesLayanan);

        $payload = [
            'poli_id' => $this->layananPoliId,
            'nama' => $data['layananNama'],
            'kategori' => $data['layananKategori'],
            'harga' => $data['layananHarga'],
            'aktif' => $this->layananAktif,
        ];

        if ($this->editLayananId) {
            Layanan::findOrFail($this->editLayananId)->update($payload);
            session()->flash('sukses', 'Layanan berhasil diperbarui.');
        } else {
            Layanan::create($payload);
            session()->flash('sukses', 'Layanan baru berhasil ditambahkan.');
        }

        $this->tutupLayananModal();
    }

    public function toggleAktifLayanan(int $id): void
    {
        $l = Layanan::findOrFail($id);
        $l->update(['aktif' => ! $l->aktif]);
    }

    public function hapusLayanan(int $id): void
    {
        Layanan::findOrFail($id)->delete();
        session()->flash('sukses', 'Layanan berhasil dihapus.');
    }

    private function resetLayananForm(): void
    {
        $this->reset(['editLayananId', 'layananPoliId', 'layananNama', 'layananHarga']);
        $this->layananKategori = 'konsultasi';
        $this->layananAktif = true;
        $this->resetErrorBag();
    }

    public function render()
    {
        $polis = Poli::withCount([
            'antrians as antrian_hari_ini' => fn ($q) => $q->whereDate('tanggal', today()),
        ])
            ->with(['layanans' => fn ($q) => $q->orderBy('kategori')->orderBy('nama')])
            ->orderBy('kode')
            ->get();

        return view('livewire.layanan-poli', [
            'polis' => $polis,
        ]);
    }
}
