<?php

namespace App\Livewire;

use App\Models\Pasien;
use App\Models\Poli;
use App\Models\RekamMedis as RekamMedisModel;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;

#[Layout('components.layouts.app')]
#[Title('Rekam Medis')] 
class RekamMedis extends Component
{
    use WithPagination;

    public string $title = 'Rekam Medis';

    // Pencarian daftar rekam medis
    public string $cari = '';

    // State modal
    public bool $showModal = false;
    public ?int $editId = null;

    // Field form
    public ?int $pasien_id = null;
    public string $cariPasien = '';
    public ?int $poli_id = null;
    public string $dokter = '';
    public string $keluhan = '';
    public string $diagnosis = '';
    public string $tindakan = '';
    public string $catatan = '';
    public string $tanggal_periksa = '';

    protected function rules(): array
    {
        return [
            'pasien_id'       => 'required|exists:pasiens,id',
            'poli_id'         => 'nullable|exists:polis,id',
            'dokter'          => 'nullable|string|max:255',
            'keluhan'         => 'nullable|string|max:1000',
            'diagnosis'       => 'required|string|max:1000',
            'tindakan'        => 'nullable|string|max:1000',
            'catatan'         => 'nullable|string|max:1000',
            'tanggal_periksa' => 'required|date',
        ];
    }

    protected array $messages = [
        'pasien_id.required' => 'Pilih pasien terlebih dahulu dari hasil pencarian.',
        'diagnosis.required' => 'Diagnosis wajib diisi.',
    ];

    public function updatingCari(): void
    {
        $this->resetPage();
    }

    public function updatedCariPasien(): void
    {
        $this->pasien_id = null;
    }

    /**
     * Hasil pencarian pasien untuk dropdown autocomplete di form.
     */
    public function pasienOptions(): Collection
    {
        if ($this->pasien_id !== null || strlen($this->cariPasien) < 2) {
            return new Collection();
        }

        return Pasien::query()
            ->where('nama', 'like', "%{$this->cariPasien}%")
            ->orWhere('no_rm', 'like', "%{$this->cariPasien}%")
            ->limit(8)
            ->get();
    }

    public function pilihPasien(int $id): void
    {
        $pasien = Pasien::findOrFail($id);

        $this->pasien_id  = $pasien->id;
        $this->cariPasien = $pasien->nama . ' (' . $pasien->no_rm . ')';
    }

    public function bukaForm(): void
    {
        $this->resetForm();
        $this->tanggal_periksa = today()->toDateString();
        $this->showModal = true;
    }

    public function tutupForm(): void
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function edit(int $id): void
    {
        $rm = RekamMedisModel::with('pasien')->findOrFail($id);

        $this->editId          = $rm->id;
        $this->pasien_id       = $rm->pasien_id;
        $this->cariPasien      = $rm->pasien->nama . ' (' . $rm->pasien->no_rm . ')';
        $this->poli_id         = $rm->poli_id;
        $this->dokter          = (string) $rm->dokter;
        $this->keluhan         = (string) $rm->keluhan;
        $this->diagnosis       = $rm->diagnosis;
        $this->tindakan        = (string) $rm->tindakan;
        $this->catatan         = (string) $rm->catatan;
        $this->tanggal_periksa = $rm->tanggal_periksa->format('Y-m-d');

        $this->showModal = true;
    }

    public function simpan(): void
    {
        $data = $this->validate();

        if ($this->editId) {
            RekamMedisModel::findOrFail($this->editId)->update($data);
            session()->flash('sukses', 'Rekam medis berhasil diperbarui.');
        } else {
            RekamMedisModel::create($data);
            session()->flash('sukses', 'Rekam medis baru berhasil disimpan.');
        }

        $this->tutupForm();
    }

    public function hapus(int $id): void
    {
        RekamMedisModel::findOrFail($id)->delete();
        session()->flash('sukses', 'Rekam medis berhasil dihapus.');
    }

    private function resetForm(): void
    {
        $this->reset([
            'editId', 'pasien_id', 'cariPasien', 'poli_id', 'dokter',
            'keluhan', 'diagnosis', 'tindakan', 'catatan', 'tanggal_periksa',
        ]);
        $this->resetErrorBag();
    }

    public function render()
    {
        $rekamMedis = RekamMedisModel::with(['pasien', 'poli'])
            ->when($this->cari, fn ($q) => $q->whereHas('pasien', fn ($q2) => $q2
                ->where('nama', 'like', "%{$this->cari}%")
                ->orWhere('no_rm', 'like', "%{$this->cari}%")
            ))
            ->latest('tanggal_periksa')
            ->paginate(10);

        return view('livewire.rekam-medis', [
            'rekamMedis' => $rekamMedis,
            'polis'      => Poli::orderBy('kode')->get(),
        ]);
    }
}
