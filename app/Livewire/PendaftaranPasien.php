<?php

namespace App\Livewire;

use App\Models\Antrian;
use App\Models\AntrianPendaftaran;
use App\Models\Notification;
use App\Models\Pasien;
use App\Models\PengaturanKlinik;
use App\Models\Poli;
use App\Models\User;
use App\Services\ThermalPrinter;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
#[Title('Registrasi Pasien')]
class PendaftaranPasien extends Component
{
    use WithPagination;

    public string $title = 'Pendaftaran Pasien';

    // ========================================================================
    // BAGIAN 1 — Patient CRUD
    // ========================================================================

    public string $cari = '';

    public bool $showModalPasien = false;

    public ?int $editId = null;

    public string $nama = '';

    public ?string $nik = null;

    public ?string $tanggal_lahir = null;

    public ?string $jenis_kelamin = null;

    public ?string $no_hp = null;

    public ?string $alamat = null;

    public ?int $poli_id = null;

    // ========================================================================
    // BAGIAN 2 — Antrian Pendaftaran
    // ========================================================================

    public bool $showModalDaftar = false;

    public ?int $antrianPendaftaranId = null;

    public string $kodeAntrianPendaftaran = '';

    public bool $pasienBaru = false;

    public ?int $pasien_id = null;

    public string $cariPasien = '';

    // Field pasien baru di modal daftar
    public string $daftar_nama = '';

    public string $daftar_nik = '';

    public string $daftar_tanggal_lahir = '';

    public string $daftar_jenis_kelamin = '';

    public string $daftar_no_hp = '';

    public string $daftar_alamat = '';

    public ?int $daftar_poli_id = null;

    public bool $bpjs = false;

    public bool $daftar_bpjs = false;

    // ========================================================================
    // VALIDASI
    // ========================================================================

    protected function rules(): array
    {
        $rules = [
            'nama' => 'required|string|max:255',
            'nik' => 'nullable|string|max:20',
            'tanggal_lahir' => 'nullable|date',
            'jenis_kelamin' => 'nullable|in:L,P',
            'no_hp' => 'nullable|string|max:20',
            'alamat' => 'nullable|string|max:500',
        ];

        if (! $this->editId) {
            $rules['poli_id'] = 'required|exists:polis,id';
        }

        return $rules;
    }

    protected function rulesDaftar(): array
    {
        $rules = [
            'daftar_poli_id' => 'required|exists:polis,id',
        ];

        if ($this->pasienBaru) {
            $rules['daftar_nama'] = 'required|string|max:255';
            $rules['daftar_nik'] = 'nullable|string|max:20';
            $rules['daftar_tanggal_lahir'] = 'nullable|date';
            $rules['daftar_jenis_kelamin'] = 'nullable|in:L,P';
            $rules['daftar_no_hp'] = 'nullable|string|max:20';
            $rules['daftar_alamat'] = 'nullable|string|max:500';
        } else {
            $rules['pasien_id'] = 'required|exists:pasiens,id';
        }

        return $rules;
    }

    protected array $messages = [
        'nama.required' => 'Nama pasien wajib diisi.',
        'poli_id.required' => 'Pilih poli tujuan untuk mengantrikan pasien.',
        'pasien_id.required' => 'Pilih pasien dari hasil pencarian, atau centang "Pasien Baru".',
        'daftar_nama.required' => 'Nama pasien wajib diisi.',
        'daftar_poli_id.required' => 'Pilih poli tujuan.',
    ];

    // ========================================================================
    // PATIENT CRUD
    // ========================================================================

    public function updatingCari(): void
    {
        $this->resetPage();
    }

    public function bukaForm(): void
    {
        $this->resetForm();
        $this->showModalPasien = true;
    }

    public function tutupForm(): void
    {
        $this->showModalPasien = false;
        $this->resetForm();
    }

    public function edit(int $id): void
    {
        $pasien = Pasien::findOrFail($id);

        $this->editId = $pasien->id;
        $this->nama = $pasien->nama;
        $this->nik = $pasien->nik;
        $this->tanggal_lahir = $pasien->tanggal_lahir?->format('Y-m-d');
        $this->jenis_kelamin = $pasien->jenis_kelamin;
        $this->no_hp = $pasien->no_hp;
        $this->alamat = $pasien->alamat;

        $this->showModalPasien = true;
    }

    public function simpan(): void
    {
        $data = $this->validate();

        if ($this->editId) {
            Pasien::findOrFail($this->editId)->update([
                'nama' => $data['nama'],
                'nik' => $data['nik'] ?? null,
                'tanggal_lahir' => $data['tanggal_lahir'] ?? null,
                'jenis_kelamin' => $data['jenis_kelamin'] ?? null,
                'no_hp' => $data['no_hp'] ?? null,
                'alamat' => $data['alamat'] ?? null,
            ]);

            session()->flash('sukses', 'Data pasien berhasil diperbarui.');
            $this->tutupForm();

            return;
        }

        DB::transaction(function () use ($data) {
            $pasien = Pasien::create([
                'no_rm' => $this->generateNoRm(),
                'nama' => $data['nama'],
                'nik' => $data['nik'] ?? null,
                'tanggal_lahir' => $data['tanggal_lahir'] ?? null,
                'jenis_kelamin' => $data['jenis_kelamin'] ?? null,
                'no_hp' => $data['no_hp'] ?? null,
                'alamat' => $data['alamat'] ?? null,
            ]);

            $poli = Poli::findOrFail($data['poli_id']);

            $urutan = Antrian::where('poli_id', $poli->id)
                ->whereDate('tanggal', today())
                ->count() + 1;

            $antrian = Antrian::create([
                'pasien_id' => $pasien->id,
                'poli_id' => $poli->id,
                'kode_antrian' => $poli->kode.'-'.str_pad((string) $urutan, 3, '0', STR_PAD_LEFT),
                'status' => 'menunggu',
                'bpjs' => $this->bpjs,
                'tanggal' => today(),
            ]);

            $this->cetakTiketPoli($antrian, $poli, $pasien);

            session()->flash(
                'sukses',
                'Pasien '.$pasien->nama.' berhasil didaftarkan dengan No. RM '.$pasien->no_rm
                    .', diantrikan ke '.$poli->nama.' dengan nomor '.$antrian->kode_antrian
                    .'. Tiket sedang dicetak — kalau tidak keluar, cetak ulang lewat menu "Cetak Antrian".'
            );
        });

        $this->tutupForm();
    }

    public function hapus(int $id): void
    {
        Pasien::findOrFail($id)->delete();
        session()->flash('sukses', 'Data pasien berhasil dihapus.');
    }

    // ========================================================================
    // ANTRIAN PENDAFTARAN — Panggil & Daftarkan
    // ========================================================================

    public function panggil(int $id): void
    {
        $antrian = AntrianPendaftaran::whereDate('tanggal', today())->findOrFail($id);
        $antrian->update(['status' => 'dipanggil']);

        // Pemicu suara panggil otomatis untuk panggilan pertama
        $kodeEja = str_replace('-', ' ', $antrian->kode_antrian);
        $kodeEja = implode('  ', str_split($kodeEja));
        $message = "Nomor antrian, {$kodeEja}, silahkan menuju ke, loket pendaftaran";

        $this->dispatch('queue-called', message: $message, _suaraKey: 'simklinik_suara_registrasi');

        session()->flash('sukses', "Nomor {$antrian->kode_antrian} berhasil dipanggil.");
    }

    public function panggilUlang(int $id): void
    {
        $antrian = AntrianPendaftaran::whereDate('tanggal', today())->findOrFail($id);
        $antrian->touch();

        $kodeEja = str_replace('-', ' ', $antrian->kode_antrian);
        $kodeEja = implode('  ', str_split($kodeEja));
        $message = "Nomor antrian, {$kodeEja}, silahkan menuju ke, loket pendaftaran";

        $this->dispatch('queue-called', message: $message, _suaraKey: 'simklinik_suara_registrasi');

        session()->flash('sukses', "Nomor {$antrian->kode_antrian} berhasil dipanggil ulang.");
    }

    public function bukaFormDaftar(int $id): void
    {
        $antrian = AntrianPendaftaran::whereDate('tanggal', today())->findOrFail($id);

        $this->antrianPendaftaranId = $antrian->id;
        $this->kodeAntrianPendaftaran = $antrian->kode_antrian;
        $this->resetFormDaftar();
        $this->showModalDaftar = true;
    }

    public function tutupFormDaftar(): void
    {
        $this->showModalDaftar = false;
        $this->reset(['antrianPendaftaranId', 'kodeAntrianPendaftaran']);
        $this->resetFormDaftar();
    }

    public function toggleModePasien(bool $baru): void
    {
        $this->pasienBaru = $baru;
        $this->pasien_id = null;
        $this->cariPasien = '';
        $this->resetErrorBag();
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
            ->where(function ($query) {
                $query->where('nama', 'like', "%{$this->cariPasien}%")
                    ->orWhere('no_rm', 'like', "%{$this->cariPasien}%");
            })
            ->limit(8)
            ->get();
    }

    public function pilihPasien(int $id): void
    {
        $pasien = Pasien::findOrFail($id);

        $this->pasien_id = $pasien->id;
        $this->cariPasien = $pasien->nama.' ('.$pasien->no_rm.')';
    }

    public function daftarkan(): void
    {
        $data = $this->validate($this->rulesDaftar());

        DB::transaction(function () use ($data) {
            if ($this->pasienBaru) {
                $pasien = Pasien::create([
                    'no_rm' => $this->generateNoRm(),
                    'nama' => $data['daftar_nama'],
                    'nik' => $data['daftar_nik'] ?? null,
                    'tanggal_lahir' => $data['daftar_tanggal_lahir'] ?? null,
                    'jenis_kelamin' => $data['daftar_jenis_kelamin'] ?? null,
                    'no_hp' => $data['daftar_no_hp'] ?? null,
                    'alamat' => $data['daftar_alamat'] ?? null,
                ]);
            } else {
                $pasien = Pasien::findOrFail($data['pasien_id']);
            }

            $poli = Poli::findOrFail($data['daftar_poli_id']);

            $urutan = Antrian::where('poli_id', $poli->id)
                ->whereDate('tanggal', today())
                ->count() + 1;

            $antrian = Antrian::create([
                'pasien_id' => $pasien->id,
                'poli_id' => $poli->id,
                'kode_antrian' => $poli->kode.'-'.str_pad((string) $urutan, 3, '0', STR_PAD_LEFT),
                'status' => 'menunggu',
                'bpjs' => $this->daftar_bpjs,
                'tanggal' => today(),
            ]);

            AntrianPendaftaran::findOrFail($this->antrianPendaftaranId)->update([
                'status' => 'selesai',
                'pasien_id' => $pasien->id,
            ]);

            $this->cetakTiketPoli($antrian, $poli, $pasien);

            // Pindahkan loop notifikasi ke dalam transaksi agar variabel ter-baca dengan benar
            foreach (User::where('role', 'dokter')->cursor() as $user) {
                Notification::create([
                    'user_id' => $user->id,
                    'title' => 'Pasien baru di '.$poli->nama,
                    'message' => $pasien->nama.' ('.$antrian->kode_antrian.') sudah mendaftar dan menunggu pemeriksaan.',
                    'type' => 'info',
                    'link' => route('manajemen-antrian'),
                ]);
            }

            session()->flash('sukses', $pasien->nama.' berhasil didaftarkan dan diantrikan ke '.$poli->nama
                .'. Nomor '.$antrian->kode_antrian.' sedang dicetak — kalau tidak keluar, cetak ulang lewat menu "Cetak Antrian".');
        });

        $this->tutupFormDaftar();
    }

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
        $tahun = now()->year;
        $urutan = Pasien::whereYear('created_at', $tahun)->count() + 1;

        do {
            $noRm = 'RM-'.$tahun.'-'.str_pad((string) $urutan, 4, '0', STR_PAD_LEFT);
            $urutan++;
        } while (Pasien::where('no_rm', $noRm)->exists());

        return $noRm;
    }

    private function resetForm(): void
    {
        $this->reset(['editId', 'nama', 'nik', 'tanggal_lahir', 'jenis_kelamin', 'no_hp', 'alamat', 'poli_id', 'bpjs']);
        $this->resetErrorBag();
    }

    private function resetFormDaftar(): void
    {
        $this->reset([
            'pasienBaru', 'pasien_id', 'cariPasien',
            'daftar_nama', 'daftar_nik', 'daftar_tanggal_lahir', 'daftar_jenis_kelamin', 'daftar_no_hp', 'daftar_alamat',
            'daftar_poli_id', 'daftar_bpjs',
        ]);
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
            'polis' => Poli::where('aktif', true)->orderBy('kode')->get(),
            'antrianMenunggu' => AntrianPendaftaran::whereDate('tanggal', today())
                ->where('status', 'menunggu')
                ->orderBy('created_at')
                ->get(),
            'antrianDipanggil' => AntrianPendaftaran::whereDate('tanggal', today())
                ->where('status', 'dipanggil')
                ->orderByDesc('updated_at')
                ->get(),
        ]);
    }
}
