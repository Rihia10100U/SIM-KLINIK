<?php

namespace App\Livewire;

use App\Models\Obat;
use App\Models\Transaksi;
use App\Models\TransaksiItem;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class KasirBilling extends Component
{
    use WithPagination;

    public string $title = 'Farmasi & Pembayaran';

    // ===== Modal konfirmasi pembayaran resep =====
    public bool $showModal = false;
    public ?int $transaksiId = null;
    public string $namaPasien = '';
    public array $items = [];
    public string $metode = 'Tunai';
    public string $cariObat = '';

    // ===== Modal rincian riwayat =====
    public bool $showDetailModal = false;
    public ?int $detailId = null;

    protected function rules(): array
    {
        return [
            'metode'               => 'required|string',
            'items'                => 'required|array|min:1',
            'items.*.nama_item'    => 'required|string|max:255',
            'items.*.qty'          => 'required|integer|min:1',
            'items.*.harga_satuan' => 'required|integer|min:0',
        ];
    }

    /**
     * Buka modal konfirmasi pembayaran untuk 1 resep (transaksi berstatus menunggu_pembayaran).
     */
    public function bukaForm(int $transaksiId): void
    {
        $transaksi = Transaksi::with(['pasien', 'items'])->findOrFail($transaksiId);

        $this->transaksiId = $transaksi->id;
        $this->namaPasien   = $transaksi->pasien->nama ?? '-';
        $this->metode        = 'Tunai';
        $this->cariObat       = '';

        $this->items = $transaksi->items->map(fn (TransaksiItem $i) => [
            'obat_id'      => $i->obat_id,
            'nama_item'    => $i->nama_item,
            'jenis'        => $i->jenis,
            'qty'          => $i->qty,
            'harga_satuan' => $i->harga_satuan,
        ])->toArray();

        $this->resetErrorBag();
        $this->showModal = true;
    }

    public function tutupForm(): void
    {
        $this->showModal = false;
        $this->reset(['transaksiId', 'namaPasien', 'items', 'cariObat']);
    }

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

        $this->items[] = [
            'obat_id'      => $obat->id,
            'nama_item'    => $obat->nama,
            'jenis'        => 'obat',
            'qty'          => 1,
            'harga_satuan' => $obat->harga,
        ];

        $this->cariObat = '';
    }

    public function hapusItem(int $index): void
    {
        if (count($this->items) <= 1) {
            return;
        }

        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }

    public function total(): int
    {
        return collect($this->items)->sum(
            fn ($item) => (int) ($item['qty'] ?? 0) * (int) ($item['harga_satuan'] ?? 0)
        );
    }

    /**
     * Konfirmasi pembayaran: simpan item final, set status lunas, DAN kurangi stok obat di sini
     * (bukan saat resep dibuat) — supaya stok baru berkurang saat obat benar-benar diserahkan/dibayar.
     */
    public function konfirmasiPembayaran(): void
    {
        $data  = $this->validate();
        $total = $this->total();

        DB::transaction(function () use ($data, $total) {
            $transaksi = Transaksi::findOrFail($this->transaksiId);

            $transaksi->update([
                'jumlah' => $total,
                'metode' => $data['metode'],
                'status' => 'lunas',
            ]);

            // Ganti seluruh item lama dengan item final hasil konfirmasi apoteker
            $transaksi->items()->delete();

            foreach ($this->items as $item) {
                $qty   = (int) $item['qty'];
                $harga = (int) $item['harga_satuan'];

                TransaksiItem::create([
                    'transaksi_id' => $transaksi->id,
                    'obat_id'      => $item['obat_id'] ?? null,
                    'nama_item'    => $item['nama_item'],
                    'jenis'        => $item['jenis'] ?? 'obat',
                    'qty'          => $qty,
                    'harga_satuan' => $harga,
                    'subtotal'     => $qty * $harga,
                ]);

                if (! empty($item['obat_id'])) {
                    $obat = Obat::find($item['obat_id']);
                    if ($obat) {
                        $obat->stok = max(0, $obat->stok - $qty);
                        $obat->save();
                    }
                }
            }
        });

        session()->flash('sukses', 'Pembayaran berhasil dikonfirmasi, stok obat sudah diperbarui.');
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
        $resepMenunggu = Transaksi::with('pasien')
            ->where('status', 'menunggu_pembayaran')
            ->orderBy('created_at')
            ->get();

        $riwayat = Transaksi::with('pasien')
            ->where('status', 'lunas')
            ->latest('created_at')
            ->paginate(10);

        $detailTransaksi = $this->detailId
            ? Transaksi::with(['items', 'pasien'])->find($this->detailId)
            : null;

        return view('livewire.kasir-billing', [
            'resepMenunggu'   => $resepMenunggu,
            'riwayat'         => $riwayat,
            'detailTransaksi' => $detailTransaksi,
        ]);
    }
}
