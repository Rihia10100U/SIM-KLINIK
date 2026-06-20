<?php

namespace App\Livewire;

use App\Models\Antrian;
use App\Models\Layanan;
use App\Models\Obat;
use App\Models\Transaksi;
use App\Models\TransaksiItem;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;

#[Layout('components.layouts.app')]
class KasirBilling extends Component
{
    use WithPagination;

    #[Title('Kasir & Billing')] 
    public string $title = 'Kasir & Billing';

    // ===== Modal "Buat Tagihan" =====
    public bool $showModal = false;
    public ?int $antrianId = null;
    public string $namaPasien = '';
    public string $namaPoli = '';
    public string $metode = 'Tunai';
    public array $items = [];
    public string $cariObat = '';
    public string $cariLayanan = '';

    // ===== Modal "Rincian Transaksi" =====
    public bool $showDetailModal = false;
    public ?int $detailId = null;

    protected function rules(): array
    {
        return [
            'metode'                => 'required|string',
            'items'                 => 'required|array|min:1',
            'items.*.nama_item'     => 'required|string|max:255',
            'items.*.qty'           => 'required|integer|min:1',
            'items.*.harga_satuan'  => 'required|integer|min:0',
        ];
    }

    public function bukaForm(int $antrianId): void
    {
        $antrian = Antrian::with(['pasien', 'poli'])->findOrFail($antrianId);

        $this->antrianId   = $antrian->id;
        $this->namaPasien  = $antrian->pasien->nama;
        $this->namaPoli    = $antrian->poli->nama;
        $this->metode      = 'Tunai';
        $this->cariObat    = '';
        $this->cariLayanan = '';

        // Cari harga konsultasi default dari Data Layanan untuk poli ini.
        // Kalau belum diatur di Data Layanan, jatuh ke nilai default Rp50.000.
        $layananKonsultasi = Layanan::where('poli_id', $antrian->poli_id)
            ->where('kategori', 'konsultasi')
            ->where('aktif', true)
            ->first();

        $this->items = [
            [
                'nama_item'    => $layananKonsultasi->nama ?? ('Biaya Konsultasi ' . $antrian->poli->nama),
                'jenis'        => 'konsultasi',
                'obat_id'      => null,
                'qty'          => 1,
                'harga_satuan' => $layananKonsultasi->harga ?? 50000,
            ],
        ];

        $this->resetErrorBag();
        $this->showModal = true;
    }

    public function tutupForm(): void
    {
        $this->showModal = false;
        $this->reset(['antrianId', 'namaPasien', 'namaPoli', 'items', 'cariObat', 'cariLayanan']);
    }

    /**
     * Hasil pencarian obat untuk ditambahkan sebagai item tagihan.
     */
    public function obatOptions(): Collection
    {
        if (strlen($this->cariObat) < 2) {
            return new Collection();
        }

        return Obat::where('nama', 'like', "%{$this->cariObat}%")->limit(8)->get();
    }

    /**
     * Hasil pencarian layanan (tindakan/lainnya) untuk ditambahkan sebagai item tagihan.
     */
    public function layananOptions(): Collection
    {
        if (strlen($this->cariLayanan) < 2) {
            return new Collection();
        }

        return Layanan::where('aktif', true)
            ->where('nama', 'like', "%{$this->cariLayanan}%")
            ->limit(8)
            ->get();
    }

    public function tambahObat(int $obatId): void
    {
        $obat = Obat::findOrFail($obatId);

        $this->items[] = [
            'nama_item'    => $obat->nama,
            'jenis'        => 'obat',
            'obat_id'      => $obat->id,
            'qty'          => 1,
            'harga_satuan' => $obat->harga,
        ];

        $this->cariObat = '';
    }

    public function tambahLayanan(int $layananId): void
    {
        $layanan = Layanan::findOrFail($layananId);

        $this->items[] = [
            'nama_item'    => $layanan->nama,
            'jenis'        => $layanan->kategori === 'konsultasi' ? 'konsultasi' : 'tindakan',
            'obat_id'      => null,
            'qty'          => 1,
            'harga_satuan' => $layanan->harga,
        ];

        $this->cariLayanan = '';
    }

    public function tambahItemLain(): void
    {
        $this->items[] = [
            'nama_item'    => '',
            'jenis'        => 'lainnya',
            'obat_id'      => null,
            'qty'          => 1,
            'harga_satuan' => 0,
        ];
    }

    public function hapusItem(int $index): void
    {
        if (count($this->items) <= 1) {
            return; // minimal harus ada 1 item
        }

        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }

    /**
     * Total tagihan saat ini (dipanggil langsung dari Blade: $this->total()).
     */
    public function total(): int
    {
        return collect($this->items)->sum(
            fn ($item) => (int) ($item['qty'] ?? 0) * (int) ($item['harga_satuan'] ?? 0)
        );
    }

    public function simpan(): void
    {
        $data  = $this->validate();
        $total = $this->total();

        DB::transaction(function () use ($data, $total) {
            $antrian = Antrian::findOrFail($this->antrianId);

            $transaksi = Transaksi::create([
                'pasien_id'  => $antrian->pasien_id,
                'antrian_id' => $antrian->id,
                'deskripsi'  => 'Pembayaran kunjungan ' . $antrian->poli->nama,
                'jumlah'     => $total,
                'metode'     => $data['metode'],
                'tanggal'    => today(),
            ]);

            foreach ($this->items as $item) {
                $qty   = (int) $item['qty'];
                $harga = (int) $item['harga_satuan'];

                TransaksiItem::create([
                    'transaksi_id' => $transaksi->id,
                    'obat_id'      => $item['obat_id'] ?? null,
                    'nama_item'    => $item['nama_item'],
                    'jenis'        => $item['jenis'] ?? 'lainnya',
                    'qty'          => $qty,
                    'harga_satuan' => $harga,
                    'subtotal'     => $qty * $harga,
                ]);

                // Kurangi stok otomatis kalau item ini adalah obat
                if (! empty($item['obat_id'])) {
                    $obat = Obat::find($item['obat_id']);
                    if ($obat) {
                        $obat->stok = max(0, $obat->stok - $qty);
                        $obat->save();
                    }
                }
            }
        });

        session()->flash('sukses', 'Pembayaran berhasil dicatat.');
        $this->tutupForm();
    }

    public function lihatDetail(int $id): void
    {
        $this->detailId = $id;
        $this->showDetailModal = true;
    }

    public function tutupDetail(): void
    {
        $this->showDetailModal = false;
        $this->detailId = null;
    }

    public function render()
    {
        $antrianBelumDibayar = Antrian::with(['pasien', 'poli'])
            ->whereDate('tanggal', today())
            ->where('status', 'selesai')
            ->whereDoesntHave('transaksi')
            ->orderBy('updated_at')
            ->get();

        $riwayat = Transaksi::with('pasien')
            ->latest('created_at')
            ->paginate(10);

        $detailTransaksi = $this->detailId
            ? Transaksi::with(['items', 'pasien'])->find($this->detailId)
            : null;

        return view('livewire.kasir-billing', [
            'antrianBelumDibayar' => $antrianBelumDibayar,
            'riwayat'             => $riwayat,
            'detailTransaksi'     => $detailTransaksi,
        ]);
    }
}
