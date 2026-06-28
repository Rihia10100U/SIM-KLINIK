<?php

namespace App\Livewire;

use App\Models\PengaturanKlinik;
use App\Services\ThermalPrinter;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Pengaturan')]
class Pengaturan extends Component
{
    public string $title = 'Pengaturan';

    public bool $isAdmin = false;

    // ===== Profil =====
    public string $nama = '';

    public string $email = '';

    // ===== Ubah Password =====
    public string $passwordSaatIni = '';

    public string $passwordBaru = '';

    public string $passwordBaruKonfirmasi = '';

    // ===== Info Klinik (khusus Admin) =====
    public string $namaKlinik = '';

    public string $alamatKlinik = '';

    public string $teleponKlinik = '';

    public string $jamBuka = '';

    public string $jamTutup = '';

    // ===== Printer Thermal (khusus Admin) =====
    public string $koneksiPrinter = 'none';

    public function mount(): void
    {
        $user = Auth::user();

        $this->nama = $user->name;
        $this->email = $user->email;
        $this->isAdmin = $user->isAdmin();

        if (! $this->isAdmin) {
            return; // user non-admin tidak perlu data Info Klinik & Printer
        }

        $klinik = PengaturanKlinik::firstOrCreate(
            ['id' => 1],
            [
                'nama_klinik' => 'SIM-KLINIK',
                'alamat' => '',
                'telepon' => '',
                'jam_buka' => '08:00',
                'jam_tutup' => '20:00',
            ]
        );

        $this->namaKlinik = $klinik->nama_klinik;
        $this->alamatKlinik = (string) $klinik->alamat;
        $this->teleponKlinik = (string) $klinik->telepon;
        $this->jamBuka = substr((string) $klinik->jam_buka, 0, 5);
        $this->jamTutup = substr((string) $klinik->jam_tutup, 0, 5);

        $this->koneksiPrinter = config('printer.connection');
    }

    public function simpanProfil(): void
    {
        $user = Auth::user();

        $data = $this->validate([
            'nama' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($user->id)],
        ]);

        $user->update($data);

        session()->flash('sukses_profil', 'Profil berhasil diperbarui.');
    }

    public function ubahPassword(): void
    {
        $this->validate([
            'passwordSaatIni' => 'required|string',
            'passwordBaru' => 'required|string|min:8',
            'passwordBaruKonfirmasi' => 'required|same:passwordBaru',
        ], [
            'passwordBaruKonfirmasi.same' => 'Konfirmasi password baru tidak cocok.',
        ]);

        $user = Auth::user();

        if (! Hash::check($this->passwordSaatIni, $user->password)) {
            $this->addError('passwordSaatIni', 'Password saat ini salah.');

            return;
        }

        $user->update(['password' => Hash::make($this->passwordBaru)]);

        $this->reset(['passwordSaatIni', 'passwordBaru', 'passwordBaruKonfirmasi']);

        session()->flash('sukses_password', 'Password berhasil diubah.');
    }

    public function simpanKlinik(): void
    {
        abort_unless(Auth::user()->isAdmin(), 403);

        $data = $this->validate([
            'namaKlinik' => 'required|string|max:255',
            'alamatKlinik' => 'nullable|string|max:500',
            'teleponKlinik' => 'nullable|string|max:30',
            'jamBuka' => 'required',
            'jamTutup' => 'required',
        ]);

        PengaturanKlinik::updateOrCreate(['id' => 1], [
            'nama_klinik' => $data['namaKlinik'],
            'alamat' => $data['alamatKlinik'],
            'telepon' => $data['teleponKlinik'],
            'jam_buka' => $data['jamBuka'],
            'jam_tutup' => $data['jamTutup'],
        ]);

        session()->flash('sukses_klinik', 'Info klinik berhasil diperbarui.');
    }

    public function tesCetak(): void
    {
        abort_unless(Auth::user()->isAdmin(), 403);

        if ($this->koneksiPrinter === 'none') {
            session()->flash('gagal_printer', 'Koneksi printer masih "none" — atur dulu PRINTER_CONNECTION di file .env.');

            return;
        }

        $berhasil = app(ThermalPrinter::class)->tesCetak($this->namaKlinik ?: 'SIM-KLINIK');

        if ($berhasil) {
            session()->flash('sukses_printer', 'Tes cetak berhasil dikirim ke printer.');
        } else {
            $koneksi = config('printer.connection');
            $detail = match ($koneksi) {
                'windows' => 'Pastikan printer menyala dan nama printer share di .env (PRINTER_WINDOWS_NAME) sudah benar.',
                'com' => 'Pastikan port COM (PRINTER_COM_PORT) sudah benar. Cek di Device Manager → Ports (COM & LPT).',
                'network' => 'Pastikan alamat IP (PRINTER_HOST) dan port (PRINTER_PORT) sudah benar.',
                default => 'Cek pengaturan koneksi di .env.',
            };
            session()->flash('gagal_printer', "Gagal mencetak — $detail");
        }
    }

    public function render()
    {
        return view('livewire.pengaturan');
    }
}
