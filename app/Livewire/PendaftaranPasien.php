<?php

namespace App\Livewire;

use App\Models\Pasien;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;

#[Layout('components.layouts.app')]
#[Title('Pendaftaran Pasien')]  
class PendaftaranPasien extends Component
{
    use WithPagination;

    
    public string $title = 'Pendaftaran Pasien';

    // Pencarian
    public string $cari = '';

    // State modal
    public bool $showModal = false;
    public ?int $editId = null;

    // Field form
    public string $nama = '';
    public ?string $nik = null;
    public ?string $tanggal_lahir = null;
    public ?string $jenis_kelamin = null;
    public ?string $no_hp = null;
    public ?string $alamat = null;

    protected function rules(): array
    {
        return [
            'nama'          => 'required|string|max:255',
            'nik'           => 'nullable|string|max:20',
            'tanggal_lahir' => 'nullable|date',
            'jenis_kelamin' => 'nullable|in:L,P',
            'no_hp'         => 'nullable|string|max:20',
            'alamat'        => 'nullable|string|max:500',
        ];
    }

    protected array $messages = [
        'nama.required' => 'Nama pasien wajib diisi.',
    ];

    // Reset halaman pagination tiap kali pencarian berubah
    public function updatingCari(): void
    {
        $this->resetPage();
    }

    public function bukaForm(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function tutupForm(): void
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function edit(int $id): void
    {
        $pasien = Pasien::findOrFail($id);

        $this->editId         = $pasien->id;
        $this->nama            = $pasien->nama;
        $this->nik              = $pasien->nik;
        $this->tanggal_lahir   = $pasien->tanggal_lahir?->format('Y-m-d');
        $this->jenis_kelamin   = $pasien->jenis_kelamin;
        $this->no_hp            = $pasien->no_hp;
        $this->alamat            = $pasien->alamat;

        $this->showModal = true;
    }

    public function simpan(): void
    {
        $data = $this->validate();

        if ($this->editId) {
            Pasien::findOrFail($this->editId)->update($data);
            session()->flash('sukses', 'Data pasien berhasil diperbarui.');
        } else {
            $data['no_rm'] = $this->generateNoRm();
            Pasien::create($data);
            session()->flash('sukses', 'Pasien baru berhasil didaftarkan dengan No. RM ' . $data['no_rm'] . '.');
        }

        $this->tutupForm();
    }

    public function hapus(int $id): void
    {
        Pasien::findOrFail($id)->delete();
        session()->flash('sukses', 'Data pasien berhasil dihapus.');
    }

    /**
     * Membuat No. Rekam Medis otomatis, format: RM-2026-0001
     */
    private function generateNoRm(): string
    {
        $tahun  = now()->year;
        $urutan = Pasien::whereYear('created_at', $tahun)->count() + 1;

        do {
            $noRm = 'RM-' . $tahun . '-' . str_pad((string) $urutan, 4, '0', STR_PAD_LEFT);
            $urutan++;
        } while (Pasien::where('no_rm', $noRm)->exists());

        return $noRm;
    }

    private function resetForm(): void
    {
        $this->reset(['editId', 'nama', 'nik', 'tanggal_lahir', 'jenis_kelamin', 'no_hp', 'alamat']);
        $this->resetErrorBag();
    }

    public function render()
    {
        $pasiens = Pasien::query()
            ->when($this->cari, fn ($q) => $q->where(fn ($q2) => $q2
                ->where('nama', 'like', "%{$this->cari}%")
                ->orWhere('no_rm', 'like', "%{$this->cari}%")
                ->orWhere('no_hp', 'like', "%{$this->cari}%")
            ))
            ->latest()
            ->paginate(10);

        return view('livewire.pendaftaran-pasien', [
            'pasiens' => $pasiens,
        ]);
    }
}
