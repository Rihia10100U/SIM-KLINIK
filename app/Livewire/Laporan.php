<?php

namespace App\Livewire;

use App\Models\Antrian;
use App\Models\Pasien;
use App\Models\Transaksi;
use App\Models\TransaksiItem;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Laporan')]
class Laporan extends Component
{
    public string $title = 'Laporan';

    public string $dari = '';

    public string $sampai = '';

    public function mount(): void
    {
        $this->dari = today()->startOfMonth()->toDateString();
        $this->sampai = today()->toDateString();
    }

    public function setRentangCepat(string $jenis): void
    {
        $hariIni = today();

        [$this->dari, $this->sampai] = match ($jenis) {
            'hari-ini' => [$hariIni->toDateString(), $hariIni->toDateString()],
            'minggu-ini' => [$hariIni->copy()->startOfWeek()->toDateString(), $hariIni->toDateString()],
            'bulan-ini' => [$hariIni->copy()->startOfMonth()->toDateString(), $hariIni->toDateString()],
            default => [$this->dari, $this->sampai],
        };
    }

    private function dariCarbon(): Carbon
    {
        return Carbon::parse($this->dari)->startOfDay();
    }

    private function sampaiCarbon(): Carbon
    {
        return Carbon::parse($this->sampai)->endOfDay();
    }

    public function ringkasan(): array
    {
        return [
            'total_kunjungan' => Antrian::whereBetween('tanggal', [$this->dari, $this->sampai])->count(),
            'pasien_baru' => Pasien::whereBetween('created_at', [$this->dariCarbon(), $this->sampaiCarbon()])->count(),
            'total_pendapatan' => (int) Transaksi::whereBetween('tanggal', [$this->dari, $this->sampai])->sum('jumlah'),
            'jumlah_transaksi' => Transaksi::whereBetween('tanggal', [$this->dari, $this->sampai])->count(),
        ];
    }

    /**
     * Data grafik kunjungan per hari dalam rentang yang dipilih.
     */
    public function kunjunganHarian(): array
    {
        $cursor = Carbon::parse($this->dari);
        $akhir = Carbon::parse($this->sampai);
        $hasil = [];

        while ($cursor->lte($akhir)) {
            $hasil[] = [
                'label' => $cursor->copy()->locale('id')->translatedFormat('d M'),
                'jumlah' => Antrian::whereDate('tanggal', $cursor)->count(),
            ];
            $cursor->addDay();
        }

        return $hasil;
    }

    public function pendapatanPerPoli(): Collection
    {
        return Transaksi::whereBetween('tanggal', [$this->dari, $this->sampai])
            ->with('antrian.poli')
            ->get()
            ->groupBy(fn (Transaksi $t) => $t->antrian?->poli?->nama ?? 'Lainnya')
            ->map(fn ($grup, $nama) => [
                'nama' => $nama,
                'jumlah' => $grup->count(),
                'pendapatan' => $grup->sum('jumlah'),
            ])
            ->sortByDesc('pendapatan')
            ->values();
    }

    public function obatTerlaris(): Collection
    {
        return TransaksiItem::query()
            ->whereHas('transaksi', fn ($q) => $q->whereBetween('tanggal', [$this->dari, $this->sampai]))
            ->where('jenis', 'obat')
            ->selectRaw('nama_item, SUM(qty) as total_qty, SUM(subtotal) as total_pendapatan')
            ->groupBy('nama_item')
            ->orderByDesc('total_qty')
            ->limit(5)
            ->get();
    }

    /**
     * Export riwayat transaksi pada rentang terpilih sebagai file CSV.
     * Livewire 3 mendukung file download langsung dari return value action.
     */
    public function exportCsv()
    {
        $transaksis = Transaksi::with('pasien')
            ->whereBetween('tanggal', [$this->dari, $this->sampai])
            ->orderBy('tanggal')
            ->get();

        $namaFile = 'laporan-transaksi-'.$this->dari.'-sd-'.$this->sampai.'.csv';

        return response()->streamDownload(function () use ($transaksis) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Tanggal', 'Pasien', 'Deskripsi', 'Metode', 'Jumlah']);

            foreach ($transaksis as $t) {
                fputcsv($handle, [
                    $t->tanggal->format('Y-m-d'),
                    $t->pasien->nama ?? '-',
                    $t->deskripsi,
                    $t->metode,
                    $t->jumlah,
                ]);
            }

            fclose($handle);
        }, $namaFile, ['Content-Type' => 'text/csv']);
    }

    public function render()
    {
        return view('livewire.laporan', [
            'ringkasan' => $this->ringkasan(),
            'kunjunganHarian' => $this->kunjunganHarian(),
            'pendapatanPerPoli' => $this->pendapatanPerPoli(),
            'obatTerlaris' => $this->obatTerlaris(),
        ]);
    }
}
