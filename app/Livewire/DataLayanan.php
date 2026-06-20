<?php

namespace App\Livewire;

use App\Models\Layanan;
use App\Models\Poli;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
#[Title('Data Layanan')]
class DataLayanan extends Component
{
    use WithPagination;

    public string $title = 'Data Layanan';

    public string $cari = '';

    public bool $showModal = false;
    public ?int $editId = null;
    public string $nama = '';
    public string $kategori = 'konsultasi';
    public ?int $poli_id = null;
    public int $harga = 0;
    public bool $aktif = true;

    protected function rules(): array
    {
        return [
            'nama'     => 'required|string|max:255',
            'kategori' => 'required|in:konsultasi,tindakan,lainnya',
            'poli_id'  => 'nullable|exists:polis,id',
            'harga'    => 'required|integer|min:0',
        ];
    }

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
        $layanan = Layanan::findOrFail($id);

        $this->editId   = $layanan->id;
        $this->nama      = $layanan->nama;
        $this->kategori   = $layanan->kategori;
        $this->poli_id     = $layanan->poli_id;
        $this->harga         = $layanan->harga;
        $this->aktif           = $layanan->aktif;

        $this->showModal = true;
    }

    public function simpan(): void
    {
        $data = $this->validate();
        $data['aktif'] = $this->aktif;

        if ($this->editId) {
            Layanan::findOrFail($this->editId)->update($data);
            session()->flash('sukses', 'Layanan berhasil diperbarui.');
        } else {
            Layanan::create($data);
            session()->flash('sukses', 'Layanan baru berhasil ditambahkan.');
        }

        $this->tutupForm();
    }

    public function hapus(int $id): void
    {
        Layanan::findOrFail($id)->delete();
        session()->flash('sukses', 'Layanan berhasil dihapus.');
    }

    public function toggleAktif(int $id): void
    {
        $layanan = Layanan::findOrFail($id);
        $layanan->update(['aktif' => ! $layanan->aktif]);
    }

    private function resetForm(): void
    {
        $this->reset(['editId', 'nama', 'poli_id', 'harga']);
        $this->kategori = 'konsultasi';
        $this->aktif    = true;
        $this->resetErrorBag();
    }

    public function render()
    {
        $layanans = Layanan::with('poli')
            ->when($this->cari, fn ($q) => $q->where('nama', 'like', "%{$this->cari}%"))
            ->orderBy('kategori')
            ->orderBy('nama')
            ->paginate(10);

        return view('livewire.data-layanan', [
            'layanans' => $layanans,
            'polis'    => Poli::orderBy('kode')->get(),
        ]);
    }
}
