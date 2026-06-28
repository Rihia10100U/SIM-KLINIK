<?php

namespace App\Livewire;

use App\Models\Antrian;
use App\Models\Layanan;
use App\Models\Obat;
use App\Models\Pasien;
use App\Models\Poli;
use App\Models\RekamMedis as RekamMedisModel;
use App\Models\Transaksi;
use App\Models\TransaksiItem;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class RekamMedis extends Component
{
    use WithPagination;

    #[Title('Rekam Medis')]
    public string $title = 'Rekam Medis';

    public string $cari = '';

    // ===== State modal =====
    public bool $showModal = false;

    public ?int $editId = null;

    public ?int $antrianId = null;

    // ===== Field rekam medis =====
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

    // ===== BPJS =====
    public bool $bpjs = false;

    // ===== LAYANAN: satu pilihan (radio button) =====
    public ?int $selectedLayananId = null; // ID layanan yang dipilih

    // ===== RESEP OBAT =====
    public array $resep = [];

    public string $cariObat = '';

    protected function rules(): array
    {
        $rules = [
            'poli_id' => 'nullable|exists:polis,id',
            'dokter' => 'nullable|string|max:255',
            'keluhan' => 'nullable|string|max:1000',
            'diagnosis' => 'required|string|max:1000',
            'tindakan' => 'nullable|string|max:1000',
            'catatan' => 'nullable|string|max:1000',
            'tanggal_periksa' => 'required|date',
        ];

        if (! $this->antrianId && ! $this->editId) {
            $rules['pasien_id'] = 'required|exists:pasiens,id';
        }

        return $rules;
    }

    protected array $messages = [
        'pasien_id.required' => 'Pilih pasien dari hasil pencarian.',
        'diagnosis.required' => 'Diagnosis wajib diisi.',
    ];

    public function updatingCari(): void
    {
        $this->resetPage();
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

    // ===== PASIEN AUTOCOMPLETE =====

    public function pasienOptions(): Collection
    {
        if ($this->pasien_id !== null || strlen($this->cariPasien) < 2) {
            return new Collection;
        }

        return Pasien::query()
            ->where('nama', 'like', "%{$this->cariPasien}%")
            ->orWhere('no_rm', 'like', "%{$this->cariPasien}%")
            ->limit(8)->get();
    }

    public function pilihPasien(int $id): void
    {
        $pasien = Pasien::findOrFail($id);
        $this->pasien_id = $pasien->id;
        $this->cariPasien = $pasien->nama.' ('.$pasien->no_rm.')';

        if (! $this->antrianId) {
            $lastAntrian = Antrian::where('pasien_id', $id)
                ->whereNotNull('poli_id')
                ->latest()
                ->first();
            if ($lastAntrian) {
                $this->poli_id = $lastAntrian->poli_id;
            }
        }
    }

    // ===== OBAT (resep) =====

    public function obatOptions(): Collection
    {
        if (strlen($this->cariObat) < 2) {
            return new Collection;
        }

        return Obat::where('nama', 'like', "%{$this->cariObat}%")->limit(8)->get();
    }

    public function tambahObat(int $id): void
    {
        $obat = Obat::findOrFail($id);

        // Cegah duplikat obat yang sama
        $sudahAda = collect($this->resep)->contains(fn ($i) => ($i['obat_id'] ?? null) === $obat->id);
        if ($sudahAda) {
            $this->cariObat = '';

            return;
        }

        $this->resep[] = [
            'obat_id' => $obat->id,
            'nama' => $obat->nama,
            'qty' => 1,
            'harga_satuan' => $obat->harga,
            'catatan' => '',
        ];

        $this->cariObat = '';
    }

    public function hapusResep(int $index): void
    {
        unset($this->resep[$index]);
        $this->resep = array_values($this->resep);
    }

    // ===== KALKULASI TOTAL =====

    public function totalLayanan(): int
    {
        if (! $this->selectedLayananId) {
            return 0;
        }

        $l = Layanan::find($this->selectedLayananId);

        return $l ? $l->harga : 0;
    }

    public function totalResep(): int
    {
        return collect($this->resep)->sum(
            fn ($i) => (int) ($i['qty'] ?? 0) * (int) ($i['harga_satuan'] ?? 0)
        );
    }

    public function totalTagihan(): int
    {
        return $this->totalLayanan() + $this->totalResep();
    }

    // ===== BUKA FORM =====

    public function bukaFormDariAntrian(int $antrianId): void
    {
        $antrian = Antrian::with(['pasien', 'poli'])->findOrFail($antrianId);

        $this->resetForm();
        $this->antrianId = $antrian->id;
        $this->pasien_id = $antrian->pasien_id;
        $this->namaPasienTerpilih = $antrian->pasien->nama;
        $this->poli_id = $antrian->poli_id;
        $this->bpjs = $antrian->bpjs;
        $this->tanggal_periksa = today()->toDateString();

        // Pilih otomatis layanan konsultasi aktif untuk poli ini
        $konsultasi = Layanan::where('poli_id', $antrian->poli_id)
            ->where('kategori', 'konsultasi')
            ->where('aktif', true)
            ->first();

        if ($konsultasi) {
            $this->selectedLayananId = $konsultasi->id;
        }

        $this->showModal = true;
    }

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
        $this->editId = $rm->id;
        $this->pasien_id = $rm->pasien_id;
        $this->namaPasienTerpilih = $rm->pasien->nama;
        $this->cariPasien = $rm->pasien->nama.' ('.$rm->pasien->no_rm.')';
        $this->poli_id = $rm->poli_id;
        $this->dokter = (string) $rm->dokter;
        $this->keluhan = (string) $rm->keluhan;
        $this->diagnosis = $rm->diagnosis;
        $this->tindakan = (string) $rm->tindakan;
        $this->catatan = (string) $rm->catatan;
        $this->tanggal_periksa = $rm->tanggal_periksa->format('Y-m-d');

        $this->showModal = true;
    }

    // ===== SIMPAN =====

    public function simpan(): void
    {
        $data = $this->validate();
        $pasienId = $this->pasien_id;

        DB::transaction(function () use ($data, $pasienId) {

            // 1. Simpan rekam medis
            $rmData = [
                'pasien_id' => $pasienId,
                'poli_id' => $data['poli_id'] ?? null,
                'antrian_id' => $this->antrianId,
                'dokter' => $data['dokter'] ?? null,
                'keluhan' => $data['keluhan'] ?? null,
                'diagnosis' => $data['diagnosis'],
                'tindakan' => $data['tindakan'] ?? null,
                'catatan' => $data['catatan'] ?? null,
                'tanggal_periksa' => $data['tanggal_periksa'],
            ];

            if ($this->editId) {
                RekamMedisModel::findOrFail($this->editId)->update($rmData);
            } else {
                RekamMedisModel::create($rmData);
            }

            // 2. Buat tagihan gabungan kalau ada layanan/resep (antrian aktif atau catat manual)
            $semuaItem = [];

            if ($this->selectedLayananId) {
                $l = Layanan::find($this->selectedLayananId);
                if ($l) {
                    $semuaItem[] = [
                        'obat_id' => null,
                        'nama_item' => $l->nama,
                        'jenis' => $l->kategori,
                        'qty' => 1,
                        'harga_satuan' => $l->harga,
                        'subtotal' => $l->harga,
                    ];
                }
            }

            foreach ($this->resep as $i) {
                $qty = (int) ($i['qty'] ?? 1);
                $harga = (int) ($i['harga_satuan'] ?? 0);
                $catatan = trim($i['catatan'] ?? '');
                $namaItem = $i['nama'];
                if ($catatan) {
                    $namaItem .= ' ('.$catatan.')';
                }

                $semuaItem[] = [
                    'obat_id' => $i['obat_id'],
                    'nama_item' => $namaItem,
                    'jenis' => 'obat',
                    'qty' => $qty,
                    'harga_satuan' => $harga,
                    'subtotal' => $qty * $harga,
                ];
            }

            if (! empty($semuaItem)) {
                $total = collect($semuaItem)->sum('subtotal');

                $deskripsi = 'Tagihan pemeriksaan';
                if ($this->antrianId) {
                    $antrian = Antrian::with('poli')->findOrFail($this->antrianId);
                    $deskripsi .= ' '.($antrian->poli->nama ?? '-');
                }

                $transaksi = Transaksi::create([
                    'pasien_id' => $pasienId,
                    'antrian_id' => $this->antrianId,
                    'deskripsi' => $deskripsi,
                    'jumlah' => $total,
                    'metode' => '-',
                    'status' => 'menunggu_pembayaran',
                    'bpjs' => $this->bpjs,
                    'tanggal' => today(),
                ]);

                foreach ($semuaItem as $item) {
                    TransaksiItem::create([
                        'transaksi_id' => $transaksi->id,
                        'obat_id' => $item['obat_id'],
                        'nama_item' => $item['nama_item'],
                        'jenis' => $item['jenis'],
                        'qty' => $item['qty'],
                        'harga_satuan' => $item['harga_satuan'],
                        'subtotal' => $item['subtotal'],
                    ]);
                }

                if ($this->antrianId) {
                    $antrian->update(['status' => 'selesai']);
                }
            }
        });

        $pesan = $this->editId
            ? 'Rekam medis berhasil diperbarui.'
            : (($this->antrianId || $this->totalTagihan() > 0)
                ? 'Pemeriksaan selesai. Tagihan Rp '.number_format($this->totalTagihan(), 0, ',', '.').' dikirim ke Farmasi & Pembayaran.'
                : 'Rekam medis berhasil disimpan.');

        session()->flash('sukses', $pesan);
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
            'editId', 'antrianId', 'pasien_id', 'namaPasienTerpilih', 'cariPasien',
            'poli_id', 'dokter', 'keluhan', 'diagnosis', 'tindakan', 'catatan',
            'tanggal_periksa', 'bpjs', 'selectedLayananId', 'resep', 'cariObat',
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

        // Layanan aktif sesuai poli yang sedang dipilih (untuk radio button)
        $layanansAktif = $this->poli_id
            ? Layanan::where('aktif', true)
                ->where(fn ($q) => $q
                    ->where('poli_id', $this->poli_id)
                    ->orWhereNull('poli_id'))
                ->orderByRaw("FIELD(kategori, 'konsultasi', 'tindakan', 'lainnya')")
                ->orderBy('nama')
                ->get()
            : collect();

        return view('livewire.rekam-medis', [
            'antrianAktif' => $antrianAktif,
            'rekamMedis' => $rekamMedis,
            'polis' => Poli::orderBy('kode')->get(),
            'layanansAktif' => $layanansAktif,
        ]);
    }
}
