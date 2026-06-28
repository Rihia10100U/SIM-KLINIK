<?php

namespace App\Livewire;

use App\Models\Notification;
use App\Models\Obat;
use App\Models\Transaksi;
use App\Models\TransaksiItem;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class KasirBilling extends Component
{
    use WithPagination;

    #[Title('Farmasi & Pembayaran')]
    public string $title = 'Farmasi & Pembayaran';

    // ===== Modal konfirmasi pembayaran resep =====
    public bool $showModal = false;

    public ?int $transaksiId = null;

    public string $namaPasien = '';

    public array $items = [];

    public string $metode = 'Tunai';

    public bool $bpjs = false;

    public string $catatan = '';

    public string $cariObat = '';

    // ===== Modal rincian riwayat =====
    public bool $showDetailModal = false;

    public ?int $detailId = null;

    protected function rules(): array
    {
        return [
            'metode' => 'required|string',
            'items' => 'required|array|min:1',
            'items.*.nama_item' => 'required|string|max:255',
            'items.*.qty' => 'required|integer|min:1',
            'items.*.harga_satuan' => 'required|integer|min:0',
            'catatan' => 'nullable|string|max:2000',
        ];
    }

    /**
     * Buka modal konfirmasi pembayaran untuk 1 resep (transaksi berstatus menunggu_pembayaran).
     */
    public function bukaForm(int $transaksiId): void
    {
        $transaksi = Transaksi::with(['pasien', 'items'])->findOrFail($transaksiId);

        $this->transaksiId = $transaksi->id;
        $this->namaPasien = $transaksi->pasien->nama ?? '-';
        $this->bpjs = $transaksi->bpjs;
        $this->metode = $transaksi->bpjs ? 'BPJS' : 'Tunai';
        $this->catatan = (string) $transaksi->catatan;
        $this->cariObat = '';

        $this->items = $transaksi->items->map(fn (TransaksiItem $i) => [
            'obat_id' => $i->obat_id,
            'nama_item' => $i->nama_item,
            'jenis' => $i->jenis,
            'qty' => $i->qty,
            'harga_satuan' => $transaksi->bpjs ? 0 : $i->harga_satuan,
            'catatan' => '',
        ])->toArray();

        $this->resetErrorBag();
        $this->showModal = true;
    }

    public function tutupForm(): void
    {
        $this->showModal = false;
        $this->reset(['transaksiId', 'namaPasien', 'items', 'cariObat', 'catatan', 'metode', 'bpjs']);
    }

    public function obatOptions(): Collection
    {
        if (strlen($this->cariObat) < 2) {
            return new Collection;
        }

        return Obat::where('nama', 'like', "%{$this->cariObat}%")->limit(8)->get();
    }

    public function tambahObat(int $obatId): void
    {
        $obat = Obat::findOrFail($obatId);

        $this->items[] = [
            'obat_id' => $obat->id,
            'nama_item' => $obat->nama,
            'jenis' => 'obat',
            'qty' => 1,
            'harga_satuan' => $this->bpjs ? 0 : $obat->harga,
            'catatan' => '',
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
        $data = $this->validate();
        $total = $this->total();

        $transaksi = null;

        DB::transaction(function () use ($data, $total, &$transaksi) {
            $transaksi = Transaksi::findOrFail($this->transaksiId);

            $metode = $this->bpjs ? 'BPJS' : $data['metode'];

            $transaksi->update([
                'jumlah' => $total,
                'metode' => $metode,
                'status' => 'lunas',
                'catatan' => $data['catatan'] ?? null,
            ]);

            // Ganti seluruh item lama dengan item final hasil konfirmasi apoteker
            $transaksi->items()->delete();

            foreach ($this->items as $item) {
                $qty = (int) $item['qty'];
                $harga = (int) $item['harga_satuan'];
                $catatan = trim($item['catatan'] ?? '');
                $namaItem = $item['nama_item'];
                if ($catatan) {
                    $namaItem .= ' ('.$catatan.')';
                }

                TransaksiItem::create([
                    'transaksi_id' => $transaksi->id,
                    'obat_id' => $item['obat_id'] ?? null,
                    'nama_item' => $namaItem,
                    'jenis' => $item['jenis'] ?? 'obat',
                    'qty' => $qty,
                    'harga_satuan' => $harga,
                    'subtotal' => $qty * $harga,
                ]);

                if (! empty($item['obat_id'])) {
                    $obat = Obat::find($item['obat_id']);
                    if ($obat) {
                        $obat->stok = max(0, $obat->stok - $qty);
                        $obat->save();
                        $obat->kirimNotifikasiStok();
                    }
                }
            }
        });

        $transaksi->load('pasien');

        foreach (User::where('role', 'admin')->cursor() as $user) {
            Notification::create([
                'user_id' => $user->id,
                'title' => 'Pembayaran diterima',
                'message' => 'Pembayaran Rp '.number_format($total, 0, ',', '.').' dari '.($transaksi->pasien->nama ?? 'Pasien').' sudah dikonfirmasi.',
                'type' => 'success',
                'link' => route('kasir-billing'),
            ]);
        }

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
            'resepMenunggu' => $resepMenunggu,
            'riwayat' => $riwayat,
            'detailTransaksi' => $detailTransaksi,
        ]);
    }
}
