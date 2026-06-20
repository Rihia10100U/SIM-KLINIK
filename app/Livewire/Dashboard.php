<?php

namespace App\Livewire;

use App\Models\Aktivitas;
use App\Models\Antrian;
use App\Models\Poli;
use App\Models\Transaksi;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Dashboard extends Component
{
    public string $title = 'Dashboard';

    public string $namaUser = '';

    public function mount(): void
    {
        $this->namaUser = auth()->user()->name;
    }

    public function render()
    {
        return view('livewire.dashboard', [
            'statistik'         => $this->getStatistik(),
            'kunjunganMingguan' => $this->getKunjunganMingguan(),
            'antrianAktif'      => $this->getAntrianAktif(),
            'aktivitasTerbaru'  => $this->getAktivitasTerbaru(),
            'pelayananAktif'    => $this->getPelayananAktif(),
        ]);
    }

    /**
     * 3 angka di stat card paling atas.
     */
    private function getStatistik(): array
    {
        return [
            'total_antrian'        => Antrian::whereDate('tanggal', today())->count(),
            'pasien_dalam_antrian' => Antrian::whereDate('tanggal', today())->where('status', 'menunggu')->count(),
            'pendapatan_hari_ini'  => (int) Transaksi::whereDate('tanggal', today())->sum('jumlah'),
        ];
    }

    /**
     * Data untuk grafik "Kunjungan Pasien - Minggu Ini" (Senin s.d. Minggu).
     */
    private function getKunjunganMingguan(): array
    {
        $awalMinggu = today()->startOfWeek();
        $hasil = [];

        foreach (range(0, 6) as $i) {
            $tanggal = $awalMinggu->copy()->addDays($i);

            $hasil[] = [
                'hari'   => $tanggal->locale('id')->translatedFormat('D'), // Sen, Sel, Rab, ...
                'jumlah' => Antrian::whereDate('tanggal', $tanggal)->count(),
            ];
        }

        return $hasil;
    }

    /**
     * 4 antrian dengan status "menunggu" hari ini, untuk panel Antrian Aktif.
     */
    private function getAntrianAktif(): Collection
    {
        return Antrian::with(['pasien', 'poli'])
            ->whereDate('tanggal', today())
            ->where('status', 'menunggu')
            ->orderBy('created_at')
            ->limit(4)
            ->get()
            ->map(fn (Antrian $a) => [
                'kode'   => $a->kode_antrian,
                'nama'   => $a->pasien->nama,
                'poli'   => $a->poli->nama,
                'status' => ucfirst($a->status),
            ]);
    }

    /**
     * 5 aktivitas terbaru untuk panel Aktivitas Terbaru.
     */
    private function getAktivitasTerbaru(): Collection
    {
        return Aktivitas::orderByDesc('waktu')
            ->limit(5)
            ->get()
            ->map(fn (Aktivitas $a) => [
                'warna' => $a->warnaBadge(),
                'pesan' => $a->pesan,
                'waktu' => $a->waktu->format('H:i'),
            ]);
    }

    /**
     * Daftar poli untuk panel Pelayanan yang Aktif.
     */
    private function getPelayananAktif(): Collection
    {
        return Poli::orderBy('kode')
            ->get()
            ->map(fn (Poli $p) => [
                'kode'  => $p->kode,
                'nama'  => $p->nama,
                'aktif' => $p->aktif,
            ]);
    }
}
