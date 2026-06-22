<?php

namespace App\Livewire;

use App\Models\Antrian;
use App\Models\Pasien;
use App\Models\PengaturanKlinik;
use App\Models\Poli;
use App\Services\ThermalPrinter;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
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

    // Hanya dipakai saat menambah pasien baru — langsung antrikan ke poli
    public ?int $poli_id = null;

    protected function rules(): array
    {
        $rules = [
            'nama'          => 'required|string|max:255',
            'nik'           => 'nullable|string|max:20',
            'tanggal_lahir' => 'nullable|date',
            'jenis_kelamin' => 'nullable|in:L,P',
            'no_hp'         => 'nullable|string|max:20',
            'alamat'        => 'nullable|string|max:500',
        ];

        // Poli cuma wajib diisi saat menambah pasien BARU, bukan saat edit data pasien lama
        if (! $this->editId) {
            $rules['poli_id'] = 'required|exists:polis,id';
        }

        return $rules;
    }

    protected array $messages = [
        'nama.required'    => 'Nama pasien wajib diisi.',
        'poli_id.required' => 'Pilih poli tujuan untuk mengantrikan pasien.',
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

        // ===== Mode edit: cuma update data pasien, tidak bikin antrian baru =====
        if ($this->editId) {
            Pasien::findOrFail($this->editId)->update([
                'nama'          => $data['nama'],
                'nik'           => $data['nik'] ?? null,
                'tanggal_lahir' => $data['tanggal_lahir'] ?? null,
                'jenis_kelamin' => $data['jenis_kelamin'] ?? null,
                'no_hp'         => $data['no_hp'] ?? null,
                'alamat'        => $data['alamat'] ?? null,
            ]);

            session()->flash('sukses', 'Data pasien berhasil diperbarui.');
            $this->tutupForm();

            return;
        }

        // ===== Mode tambah baru: simpan pasien + antrikan langsung ke poli =====
        DB::transaction(function () use ($data) {
            $pasien = Pasien::create([
                'no_rm'         => $this->generateNoRm(),
                'nama'          => $data['nama'],
                'nik'           => $data['nik'] ?? null,
                'tanggal_lahir' => $data['tanggal_lahir'] ?? null,
                'jenis_kelamin' => $data['jenis_kelamin'] ?? null,
                'no_hp'         => $data['no_hp'] ?? null,
                'alamat'        => $data['alamat'] ?? null,
            ]);

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

            $this->cetakTiketPoli($antrian, $poli, $pasien);

            session()->flash(
                'sukses',
                'Pasien ' . $pasien->nama . ' berhasil didaftarkan dengan No. RM ' . $pasien->no_rm
                    . ', diantrikan ke ' . $poli->nama . ' dengan nomor ' . $antrian->kode_antrian
                    . '. Tiket sedang dicetak — kalau tidak keluar, cetak ulang lewat menu "Cetak Antrian".'
            );
        });

        $this->tutupForm();
    }

    /**
     * Coba cetak otomatis tiket antrian poli ke printer thermal.
     * Tidak menggagalkan proses pendaftaran kalau printer bermasalah —
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
        $this->reset(['editId', 'nama', 'nik', 'tanggal_lahir', 'jenis_kelamin', 'no_hp', 'alamat', 'poli_id']);
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
            'polis'   => Poli::where('aktif', true)->orderBy('kode')->get(),
        ]);
    }
}
