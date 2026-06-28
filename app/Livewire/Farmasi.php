<?php

namespace App\Livewire;

use App\Models\Obat;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
#[Title('Farmasi')]
class Farmasi extends Component
{
    use WithPagination;

    public string $title = 'Farmasi';

    public string $cari = '';

    // ===== Modal tambah/edit obat =====
    public bool $showModal = false;

    public ?int $editId = null;

    public string $nama = '';

    public string $kategori = '';

    public string $satuan = 'Tablet';

    public int $stok = 0;

    public int $stok_minimum = 10;

    public int $harga = 0;

    public string $catatan = '';

    // ===== Modal atur stok =====
    public bool $showStokModal = false;

    public ?int $stokObatId = null;

    public string $stokObatNama = '';

    public int $stokSaatIni = 0;

    public string $jenisPerubahan = 'tambah'; // tambah | kurangi

    public ?int $jumlahPerubahan = null;

    protected function rules(): array
    {
        return [
            'nama' => 'required|string|max:255',
            'kategori' => 'nullable|string|max:100',
            'satuan' => 'required|string|max:50',
            'stok' => 'required|integer|min:0',
            'stok_minimum' => 'required|integer|min:0',
            'harga' => 'required|integer|min:0',
            'catatan' => 'nullable|string|max:2000',
        ];
    }

    public function updatingCari(): void
    {
        $this->resetPage();
    }

    // ----- Tambah / Edit Obat -----

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
        $obat = Obat::findOrFail($id);

        $this->editId = $obat->id;
        $this->nama = $obat->nama;
        $this->kategori = (string) $obat->kategori;
        $this->satuan = $obat->satuan;
        $this->stok = $obat->stok;
        $this->stok_minimum = $obat->stok_minimum;
        $this->harga = $obat->harga;
        $this->catatan = (string) $obat->catatan;

        $this->showModal = true;
    }

    public function simpan(): void
    {
        $data = $this->validate();

        if ($this->editId) {
            $obat = Obat::findOrFail($this->editId);
            $obat->update($data);
            $obat->kirimNotifikasiStok();
            session()->flash('sukses', 'Data obat berhasil diperbarui.');
        } else {
            $data['kode_obat'] = $this->generateKodeObat();
            Obat::create($data);
            session()->flash('sukses', 'Obat baru berhasil ditambahkan.');
        }

        $this->tutupForm();
    }

    public function hapus(int $id): void
    {
        Obat::findOrFail($id)->delete();
        session()->flash('sukses', 'Data obat berhasil dihapus.');
    }

    private function generateKodeObat(): string
    {
        $urutan = Obat::count() + 1;

        do {
            $kode = 'OBT-'.str_pad((string) $urutan, 4, '0', STR_PAD_LEFT);
            $urutan++;
        } while (Obat::where('kode_obat', $kode)->exists());

        return $kode;
    }

    private function resetForm(): void
    {
        $this->reset(['editId', 'nama', 'kategori', 'stok', 'harga', 'catatan']);
        $this->satuan = 'Tablet';
        $this->stok_minimum = 10;
        $this->resetErrorBag();
    }

    // ----- Atur Stok -----

    public function bukaStokForm(int $id): void
    {
        $obat = Obat::findOrFail($id);

        $this->stokObatId = $obat->id;
        $this->stokObatNama = $obat->nama;
        $this->stokSaatIni = $obat->stok;
        $this->jenisPerubahan = 'tambah';
        $this->jumlahPerubahan = null;
        $this->resetErrorBag();

        $this->showStokModal = true;
    }

    public function tutupStokForm(): void
    {
        $this->showStokModal = false;
        $this->reset(['stokObatId', 'stokObatNama', 'stokSaatIni', 'jumlahPerubahan']);
    }

    public function simpanStok(): void
    {
        $this->validate([
            'jumlahPerubahan' => 'required|integer|min:1',
        ], [
            'jumlahPerubahan.required' => 'Jumlah perubahan stok wajib diisi.',
            'jumlahPerubahan.min' => 'Jumlah minimal 1.',
        ]);

        $obat = Obat::findOrFail($this->stokObatId);

        if ($this->jenisPerubahan === 'tambah') {
            $obat->increment('stok', $this->jumlahPerubahan);
            $obat->refresh();
        } else {
            $obat->stok = max(0, $obat->stok - $this->jumlahPerubahan);
            $obat->save();
        }

        $obat->kirimNotifikasiStok();

        session()->flash('sukses', 'Stok '.$obat->nama.' berhasil diperbarui.');
        $this->tutupStokForm();
    }

    public function render()
    {
        $obats = Obat::query()
            ->when($this->cari, fn ($q) => $q
                ->where('nama', 'like', "%{$this->cari}%")
                ->orWhere('kode_obat', 'like', "%{$this->cari}%"))
            ->orderBy('nama')
            ->paginate(10);

        return view('livewire.farmasi', [
            'obats' => $obats,
        ]);
    }
}
