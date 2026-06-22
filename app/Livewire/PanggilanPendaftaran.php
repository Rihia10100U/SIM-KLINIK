<?php

namespace App\Livewire;

use App\Models\Antrian;
use App\Models\AntrianPendaftaran;
use App\Models\Pasien;
use App\Models\PengaturanKlinik;
use App\Models\Poli;
use App\Services\ThermalPrinter;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class PanggilanPendaftaran extends Component
{
    public string $title = 'Panggilan Pendaftaran';

    // ===== Modal daftarkan =====
    public bool $showModal = false;
    public ?int $antrianPendaftaranId = null;
    public string $kodeAntrianPendaftaran = '';

    public bool $pasienBaru = false;
    public ?int $pasien_id = null;
    public string $cariPasien = '';

    // Field pasien baru
    public string $nama = '';
    public string $nik = '';
    public string $tanggal_lahir = '';
    public string $jenis_kelamin = '';
    public string $no_hp = '';
    public string $alamat = '';

    public ?int $poli_id = null;

    protected function rules(): array
    {
        $rules = [
            'poli_id' => 'required|exists:polis,id',
        ];

        if ($this->pasienBaru) {
            $rules['nama']          = 'required|string|max:255';
            $rules['nik']           = 'nullable|string|max:20';
            $rules['tanggal_lahir'] = 'nullable|date';
            $rules['jenis_kelamin'] = 'nullable|in:L,P';
            $rules['no_hp']         = 'nullable|string|max:20';
            $rules['alamat']        = 'nullable|string|max:500';
        } else {
            $rules['pasien_id'] = 'required|exists:pasiens,id';
        }

        return $rules;
    }

    protected array $messages = [
        'pasien_id.required' => 'Pilih pasien dari hasil pencarian, atau centang "Pasien Baru".',
        'nama.required'      => 'Nama pasien wajib diisi.',
        'poli_id.required'   => 'Pilih poli tujuan.',
    ];

    public function panggil(int $id): void
    {
        AntrianPendaftaran::whereDate('tanggal', today())->findOrFail($id)->update(['status' => 'dipanggil']);
    }

    public function bukaForm(int $id): void
    {
        $antrian = AntrianPendaftaran::whereDate('tanggal', today())->findOrFail($id);

        $this->antrianPendaftaranId  = $antrian->id;
        $this->kodeAntrianPendaftaran = $antrian->kode_antrian;
        $this->resetFormPasien();
        $this->showModal = true;
    }

    public function tutupForm(): void
    {
        $this->showModal = false;
        $this->reset(['antrianPendaftaranId', 'kodeAntrianPendaftaran']);
        $this->resetFormPasien();
    }

    public function toggleModePasien(bool $baru): void
    {
        $this->pasienBaru  = $baru;
        $this->pasien_id   = null;
        $this->cariPasien  = '';
        $this->resetErrorBag();
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

    public function daftarkan(): void
    {
        $data = $this->validate();

        DB::transaction(function () use ($data) {
            if ($this->pasienBaru) {
                $pasien = Pasien::create([
                    'no_rm'         => $this->generateNoRm(),
                    'nama'          => $data['nama'],
                    'nik'           => $data['nik'] ?? null,
                    'tanggal_lahir' => $data['tanggal_lahir'] ?? null,
                    'jenis_kelamin' => $data['jenis_kelamin'] ?? null,
                    'no_hp'         => $data['no_hp'] ?? null,
                    'alamat'        => $data['alamat'] ?? null,
                ]);
            } else {
                $pasien = Pasien::findOrFail($data['pasien_id']);
            }

            $poli = Poli::findOrFail($data['poli_id']);

            $urutan = Antrian::where('poli_id', $poli->id)
                ->whereDate('tanggal', today())
                ->count() + 1;

            $antrian = Antrian::create([
                'pasien_id'    => $pasien->id,
                'poli_id'      => $poli->id,
                'kode_antrian' => $poli->kode . '-' . str_pad((string) $urutan, 3, '0', STR_PAD_LEFT),
                'status'       => 'menunggu',
                'tanggal'      => today(),
            ]);

            AntrianPendaftaran::findOrFail($this->antrianPendaftaranId)->update([
                'status'    => 'selesai',
                'pasien_id' => $pasien->id,
            ]);

            $this->cetakTiketPoli($antrian, $poli, $pasien);

            session()->flash('sukses', $pasien->nama . ' berhasil didaftarkan dan diantrikan ke ' . $poli->nama
                . '. Nomor ' . $antrian->kode_antrian . ' sedang dicetak — kalau tidak keluar, cetak ulang lewat menu "Cetak Antrian".');
        });

        $this->tutupForm();
    }

    /**
     * Coba cetak otomatis tiket antrian poli ke printer thermal.
     * Sengaja tidak menggagalkan proses pendaftaran kalau printer bermasalah —
     * staf masih bisa cetak ulang manual lewat menu "Cetak Antrian".
     */
    private function cetakTiketPoli(Antrian $antrian, Poli $poli, Pasien $pasien): void
    {
        if (config('printer.connection') === 'none') {
            return;
        }

        $namaKlinik = PengaturanKlinik::first()?->nama_klinik ?? 'SIM-KLINIK';

        app(ThermalPrinter::class)->cetakTiketPoli(
            $antrian->kode_antrian,
            $poli->nama,
            $pasien->nama,
            $namaKlinik,
            now()
        );
    }

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

    private function resetFormPasien(): void
    {
        $this->reset([
            'pasienBaru', 'pasien_id', 'cariPasien',
            'nama', 'nik', 'tanggal_lahir', 'jenis_kelamin', 'no_hp', 'alamat', 'poli_id',
        ]);
        $this->resetErrorBag();
    }

    public function render()
    {
        return view('livewire.panggilan-pendaftaran', [
            'antrianMenunggu'  => AntrianPendaftaran::whereDate('tanggal', today())
                ->where('status', 'menunggu')
                ->orderBy('created_at')
                ->get(),
            'antrianDipanggil' => AntrianPendaftaran::whereDate('tanggal', today())
                ->where('status', 'dipanggil')
                ->orderByDesc('updated_at')
                ->get(),
            'polis' => Poli::where('aktif', true)->orderBy('kode')->get(),
        ]);
    }
}
