<?php

namespace App\Livewire;

use App\Models\Antrian;
use App\Models\Obat;
use App\Models\Pasien;
use App\Models\Poli;
use App\Models\RekamMedis as RekamMedisModel;
use App\Models\Transaksi;
use App\Models\TransaksiItem;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class RekamMedis extends Component
{
    use WithPagination;

    public string $title = 'Rekam Medis';

    public string $cari = '';

    // ===== State modal =====
    public bool $showModal = false;
    public ?int $editId = null;     // mode: edit data lama
    public ?int $antrianId = null;  // mode: catat dari antrian yang sedang diperiksa

    public ?int $pasien_id = null;
    public string $namaPasienTerpilih = '';
    public string $cariPasien = '';
    public ?int $poli_id = null;
    public string $dokter = '';
    public string $keluhan = '';
    public string $diagnosis = '';
    public string $tindakan = '';
    public string $catatan = '';
    public string $tanggal_periksa = '';

    // ===== Resep (hanya dipakai saat catat dari antrian aktif) =====
    public array $resep = [];
    public string $cariObat = '';

    protected function rules(): array
    {
        $rules = [
            'poli_id'         => 'nullable|exists:polis,id',
            'dokter'          => 'nullable|string|max:255',
            'keluhan'         => 'nullable|string|max:1000',
            'diagnosis'       => 'required|string|max:1000',
            'tindakan'        => 'nullable|string|max:1000',
            'catatan'         => 'nullable|string|max:1000',
            'tanggal_periksa' => 'required|date',
        ];

        if (! $this->antrianId && ! $this->editId) {
            $rules['pasien_id'] = 'required|exists:pasiens,id';
        }

        return $rules;
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

    /**
     * Hasil pencarian obat untuk ditambahkan ke resep.
     */
    public function obatOptions(): Collection
    {
        if (strlen($this->cariObat) < 2) {
            return new Collection();
        }

        return Obat::where('nama', 'like', "%{$this->cariObat}%")->limit(8)->get();
    }

    public function tambahObat(int $obatId): void
    {
        $obat = Obat::findOrFail($obatId);

        $this->resep[] = [
            'obat_id'      => $obat->id,
            'nama'         => $obat->nama,
            'qty'          => 1,
            'harga_satuan' => $obat->harga,
        ];

        $this->cariObat = '';
    }

    public function hapusResep(int $index): void
    {
        unset($this->resep[$index]);
        $this->resep = array_values($this->resep);
    }

    public function totalResep(): int
    {
        return collect($this->resep)->sum(
            fn ($item) => (int) ($item['qty'] ?? 0) * (int) ($item['harga_satuan'] ?? 0)
        );
    }

    /**
     * Buka form untuk mencatat hasil pemeriksaan dari antrian yang sedang dipanggil/diperiksa.
     */
    public function bukaFormDariAntrian(int $antrianId): void
    {
        $antrian = Antrian::with(['pasien', 'poli'])->findOrFail($antrianId);

        $this->resetForm();
        $this->antrianId          = $antrian->id;
        $this->pasien_id          = $antrian->pasien_id;
        $this->namaPasienTerpilih = $antrian->pasien->nama;
        $this->poli_id            = $antrian->poli_id;
        $this->tanggal_periksa    = today()->toDateString();

        $this->showModal = true;
    }

    /**
     * Buka form manual (tanpa antrian, tanpa resep) — untuk catatan ad-hoc.
     */
    public function bukaFormManual(): void
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

        $this->resetForm();
        $this->editId             = $rm->id;
        $this->pasien_id          = $rm->pasien_id;
        $this->namaPasienTerpilih = $rm->pasien->nama;
        $this->cariPasien         = $rm->pasien->nama . ' (' . $rm->pasien->no_rm . ')';
        $this->poli_id            = $rm->poli_id;
        $this->dokter             = (string) $rm->dokter;
        $this->keluhan            = (string) $rm->keluhan;
        $this->diagnosis          = $rm->diagnosis;
        $this->tindakan           = (string) $rm->tindakan;
        $this->catatan            = (string) $rm->catatan;
        $this->tanggal_periksa    = $rm->tanggal_periksa->format('Y-m-d');

        $this->showModal = true;
    }

    public function simpan(): void
    {
        $data     = $this->validate();
        $pasienId = $this->pasien_id;

        DB::transaction(function () use ($data, $pasienId) {
            $rmData = [
                'pasien_id'       => $pasienId,
                'poli_id'         => $data['poli_id'] ?? null,
                'antrian_id'      => $this->antrianId,
                'dokter'          => $data['dokter'] ?? null,
                'keluhan'         => $data['keluhan'] ?? null,
                'diagnosis'       => $data['diagnosis'],
                'tindakan'        => $data['tindakan'] ?? null,
                'catatan'         => $data['catatan'] ?? null,
                'tanggal_periksa' => $data['tanggal_periksa'],
            ];

            if ($this->editId) {
                RekamMedisModel::findOrFail($this->editId)->update($rmData);
            } else {
                RekamMedisModel::create($rmData);
            }

            // Kalau dicatat dari antrian yang sedang diperiksa:
            // buat resep (kalau ada item obat) + tandai antrian selesai.
            if ($this->antrianId) {
                $antrian = Antrian::with('poli')->findOrFail($this->antrianId);

                if (! empty($this->resep)) {
                    $total = $this->totalResep();

                    $transaksi = Transaksi::create([
                        'pasien_id'  => $pasienId,
                        'antrian_id' => $antrian->id,
                        'deskripsi'  => 'Resep dari pemeriksaan ' . ($antrian->poli->nama ?? '-'),
                        'jumlah'     => $total,
                        'metode'     => '-',
                        'status'     => 'menunggu_pembayaran',
                        'tanggal'    => today(),
                    ]);

                    foreach ($this->resep as $item) {
                        $qty   = (int) $item['qty'];
                        $harga = (int) $item['harga_satuan'];

                        TransaksiItem::create([
                            'transaksi_id' => $transaksi->id,
                            'obat_id'      => $item['obat_id'],
                            'nama_item'    => $item['nama'],
                            'jenis'        => 'obat',
                            'qty'          => $qty,
                            'harga_satuan' => $harga,
                            'subtotal'     => $qty * $harga,
                        ]);
                    }
                }

                $antrian->update(['status' => 'selesai']);
            }
        });

        session()->flash(
            'sukses',
            $this->antrianId
                ? 'Hasil pemeriksaan berhasil dicatat' . (! empty($this->resep) ? ' dan resep dikirim ke Apoteker.' : '.')
                : ($this->editId ? 'Rekam medis berhasil diperbarui.' : 'Rekam medis baru berhasil disimpan.')
        );

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
            'editId', 'antrianId', 'pasien_id', 'namaPasienTerpilih', 'cariPasien', 'poli_id', 'dokter',
            'keluhan', 'diagnosis', 'tindakan', 'catatan', 'tanggal_periksa', 'resep', 'cariObat',
        ]);
        $this->resetErrorBag();
    }

    public function render()
    {
        $antrianAktif = Antrian::with(['pasien', 'poli'])
            ->whereDate('tanggal', today())
            ->where('status', 'dipanggil')
            ->orderBy('updated_at')
            ->get();

        $rekamMedis = RekamMedisModel::with(['pasien', 'poli'])
            ->when($this->cari, fn ($q) => $q->whereHas('pasien', fn ($q2) => $q2
                ->where('nama', 'like', "%{$this->cari}%")
                ->orWhere('no_rm', 'like', "%{$this->cari}%")))
            ->latest('tanggal_periksa')
            ->paginate(10);

        return view('livewire.rekam-medis', [
            'antrianAktif' => $antrianAktif,
            'rekamMedis'   => $rekamMedis,
            'polis'        => Poli::orderBy('kode')->get(),
        ]);
    }
}
