<div class="space-y-6">

    {{-- Notifikasi sukses --}}
    @if (session('sukses'))
        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 5000)" x-show="show" x-transition.duration.500ms
            class="bg-green-50 border border-green-200 text-green-700 text-sm rounded-xl px-4 py-3">
            {{ session('sukses') }}
        </div>
    @endif

    <div>
        <h2 class="text-xl font-bold text-gray-800">Farmasi & Pembayaran</h2>
        <p class="text-sm text-gray-400 mt-1">Konfirmasi pembayaran resep dari Rekam Medis</p>
    </div>

    {{-- ===================== RESEP MENUNGGU PEMBAYARAN ===================== --}}
    <div class="card p-5">
        <h3 class="font-semibold text-gray-800 mb-4">Resep Menunggu Pembayaran</h3>

        <div class="space-y-3">
            @forelse ($resepMenunggu as $t)
                <div class="flex items-center justify-between bg-gray-50 rounded-xl p-3" wire:key="resep-{{ $t->id }}">
                    <div class="flex items-center gap-2">
                        <div>
                            <p class="text-sm font-medium text-gray-700">{{ $t->pasien->nama ?? '-' }}</p>
                            <p class="text-xs text-gray-400">{{ $t->deskripsi }}</p>
                        </div>
                        @if ($t->bpjs)
                            <span class="text-[10px] font-semibold bg-blue-600 text-white px-2 py-0.5 rounded-full">BPJS</span>
                        @endif
                    </div>
                    <button
                        wire:click="bukaForm({{ $t->id }})"
                        class="text-xs font-medium text-white bg-klinik-green px-4 py-1.5 rounded-full hover:bg-klinik-green-dark"
                    >
                        Proses Pembayaran
                    </button>
                </div>
            @empty
                <p class="text-sm text-gray-400 text-center py-6">Tidak ada resep yang menunggu pembayaran saat ini.</p>
            @endforelse
        </div>
    </div>

    {{-- ===================== RIWAYAT TRANSAKSI ===================== --}}
    <div class="card overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-50">
            <h3 class="font-semibold text-gray-800">Riwayat Pembayaran</h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 text-left text-gray-400 text-xs uppercase tracking-wide">
                        <th class="px-5 py-3 font-medium">Tanggal</th>
                        <th class="px-5 py-3 font-medium">Pasien</th>
                        <th class="px-5 py-3 font-medium">Deskripsi</th>
                        <th class="px-5 py-3 font-medium">Metode</th>
                        <th class="px-5 py-3 font-medium">Total</th>
                        <th class="px-5 py-3 font-medium text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse ($riwayat as $t)
                        <tr class="hover:bg-gray-50/60" wire:key="riwayat-{{ $t->id }}">
                            <td class="px-5 py-3 text-gray-500 whitespace-nowrap">{{ $t->tanggal->translatedFormat('d M Y') }}</td>
                            <td class="px-5 py-3 text-gray-700">{{ $t->pasien->nama ?? '-' }}</td>
                            <td class="px-5 py-3 text-gray-500">{{ $t->deskripsi }}</td>
                            <td class="px-5 py-3 text-gray-500 whitespace-nowrap">
                                @if ($t->bpjs)
                                    <span class="text-[10px] font-semibold bg-blue-600 text-white px-2 py-0.5 rounded-full">BPJS</span>
                                @else
                                    {{ $t->metode }}
                                @endif
                                @if ($t->catatan)
                                    <span class="ml-1 text-xs text-gray-300" title="Ada catatan">📋</span>
                                @endif
                            </td>
                            <td class="px-5 py-3 text-gray-700 font-medium whitespace-nowrap">
                                Rp {{ number_format($t->jumlah, 0, ',', '.') }}
                            </td>
                            <td class="px-5 py-3 text-right">
                                <button wire:click="lihatDetail({{ $t->id }})" class="text-xs font-medium text-klinik-blue-dark hover:underline">
                                    Lihat Rincian
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-10 text-center text-gray-400 text-sm">Belum ada transaksi yang lunas.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($riwayat->hasPages())
            <div class="px-5 py-4 border-t border-gray-50">
                {{ $riwayat->links() }}
            </div>
        @endif
    </div>

    {{-- ===================== MODAL KONFIRMASI PEMBAYARAN ===================== --}}
    @if ($showModal)
        <div class="modal-overlay fixed inset-0 bg-black/40 flex items-start justify-center z-50 p-4 overflow-y-auto" wire:click.self="tutupForm">
            <div class="modal-content bg-white rounded-2xl shadow-xl w-full max-w-2xl p-6 mt-8 mb-8 max-h-[calc(100vh-4rem)] overflow-y-auto">

                <div class="flex items-center justify-between mb-1">
                    <h3 class="font-bold text-gray-800">Konfirmasi Pembayaran</h3>
                    <button wire:click="tutupForm" class="text-gray-400 hover:text-gray-600">
                        <x-icon name="cross" class="w-5 h-5 rotate-45" />
                    </button>
                </div>
                <div class="flex items-center gap-2 mb-5">
                    <p class="text-sm text-gray-500">{{ $namaPasien }}</p>
                    @if ($bpjs)
                        <span class="text-[10px] font-semibold bg-blue-600 text-white px-2 py-0.5 rounded-full">BPJS</span>
                    @endif
                </div>

                <form wire:submit="konfirmasiPembayaran" class="space-y-5">

                    {{-- Daftar item resep --}}
                    <div class="space-y-2">
                        <div class="grid grid-cols-12 gap-2 text-xs font-medium text-gray-400 px-1">
                            <span class="col-span-5">Item</span>
                            <span class="col-span-2 text-center">Qty</span>
                            <span class="col-span-2 text-right">Harga</span>
                            <span class="col-span-2 text-right">Subtotal</span>
                            <span class="col-span-1"></span>
                        </div>

                        @foreach ($items as $index => $item)
                            <div class="bg-gray-50 rounded-lg px-3 py-2" wire:key="item-{{ $index }}">
                                <div class="grid grid-cols-12 gap-2 items-center">
                                    @php $isObat = ($item['jenis'] ?? 'obat') === 'obat'; @endphp
                                    <div class="{{ $isObat ? 'col-span-5' : 'col-span-7' }}">
                                        <input
                                            type="text"
                                            wire:model="items.{{ $index }}.nama_item"
                                            @if (! empty($item['obat_id'])) disabled @endif
                                            class="w-full bg-gray-100 rounded-lg px-3 py-2 text-sm disabled:opacity-70"
                                        >
                                    </div>
                                    @if ($isObat)
                                    <div class="col-span-2">
                                        <input
                                            type="number"
                                            min="1"
                                            wire:model.live="items.{{ $index }}.qty"
                                            class="w-full bg-gray-100 rounded-lg px-2 py-2 text-sm text-center"
                                        >
                                    </div>
                                    @endif
                                    <div class="col-span-2">
                                        <input
                                            type="number"
                                            min="0"
                                            wire:model.live="items.{{ $index }}.harga_satuan"
                                            @if (! empty($item['obat_id']) || $bpjs) disabled @endif
                                            class="w-full bg-gray-100 rounded-lg px-2 py-2 text-sm text-right disabled:opacity-70"
                                        >
                                    </div>
                                    <div class="col-span-2 text-right text-sm text-gray-600 font-medium">
                                        {{ number_format(((int) ($item['qty'] ?? 0)) * ((int) ($item['harga_satuan'] ?? 0)), 0, ',', '.') }}
                                    </div>
                                    <div class="col-span-1 text-right">
                                        @if (count($items) > 1)
                                            <button type="button" wire:click="hapusItem({{ $index }})" class="text-red-400 hover:text-red-600">
                                                <x-icon name="cross" class="w-4 h-4 rotate-45" />
                                            </button>
                                        @endif
                                    </div>
                                </div>
                                @if ($isObat)
                                    <input
                                        type="text"
                                        wire:model="items.{{ $index }}.catatan"
                                        placeholder="Aturan pakai (mis. 3x sehari)..."
                                        class="mt-1.5 w-full bg-white border border-gray-200 rounded px-2 py-1 text-xs text-gray-600 placeholder-gray-400 focus:outline-none focus:ring-1 focus:ring-klinik-green/40"
                                    >
                                @endif
                            </div>
                        @endforeach

                        @error('items') <p class="text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>

                    {{-- Tambah obat tambahan --}}
                    <div class="relative">
                        <label class="text-xs font-medium text-gray-500 block mb-1">+ Tambah Obat Lain (kalau diperlukan)</label>
                        <input
                            type="text"
                            wire:model.live.debounce.300ms="cariObat"
                            placeholder="Cari nama obat..."
                            autocomplete="off"
                            class="w-full bg-gray-100 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-klinik-blue-dark/40"
                        >
                        @if (strlen($cariObat) >= 2)
                            <div class="absolute z-10 mt-1 w-full bg-white border border-gray-100 rounded-lg shadow-lg max-h-48 overflow-y-auto">
                                @forelse ($this->obatOptions() as $opt)
                                    <button
                                        type="button"
                                        wire:click="tambahObat({{ $opt->id }})"
                                        class="w-full text-left px-3 py-2 text-sm hover:bg-gray-50 flex items-center justify-between"
                                    >
                                        <span class="text-gray-700">{{ $opt->nama }}</span>
                                        <span class="text-xs text-gray-400">Rp {{ number_format($opt->harga, 0, ',', '.') }}</span>
                                    </button>
                                @empty
                                    <p class="px-3 py-2 text-sm text-gray-400">Obat tidak ditemukan.</p>
                                @endforelse
                            </div>
                        @endif
                    </div>


                    {{-- Total + metode --}}
                    <div class="flex items-center justify-between border-t border-gray-100 pt-4">
                        <div>
                            <label class="text-xs font-medium text-gray-500 block mb-1">Metode Pembayaran</label>
                            @if ($bpjs)
                                <div class="flex items-center gap-2 bg-blue-50 text-blue-700 text-sm font-medium px-3 py-2 rounded-lg">
                                    <x-icon name="ticket" class="w-4 h-4" /> BPJS Kesehatan
                                </div>
                                <p class="text-[11px] text-blue-500 mt-1">Pasien BPJS — seluruh biaya ditanggung BPJS</p>
                            @else
                                <select wire:model="metode" class="bg-gray-100 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-klinik-blue-dark/40">
                                    <option value="Tunai">Tunai</option>
                                    <option value="Transfer">Transfer</option>
                                    <option value="QRIS">QRIS</option>
                                </select>
                            @endif
                        </div>
                        <div class="text-right">
                            <p class="text-xs text-gray-400">Total Bayar</p>
                            <p class="text-xl font-bold text-gray-800">Rp {{ number_format($this->total(), 0, ',', '.') }}</p>
                            @if ($bpjs)
                                <p class="text-[11px] text-blue-500">Gratis (BPJS)</p>
                            @endif
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-2">
                        <button type="button" wire:click="tutupForm" class="text-sm font-medium text-gray-500 px-4 py-2 rounded-full hover:bg-gray-100">
                            Batal
                        </button>
                        <button
                            type="submit"
                            wire:loading.attr="disabled"
                            wire:target="konfirmasiPembayaran"
                            class="text-sm font-medium text-white bg-klinik-green px-5 py-2 rounded-full hover:bg-klinik-green-dark disabled:opacity-60"
                        >
                            <span wire:loading.remove wire:target="konfirmasiPembayaran">Konfirmasi & Bayar</span>
                            <span wire:loading wire:target="konfirmasiPembayaran">Memproses...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- ===================== MODAL RINCIAN RIWAYAT ===================== --}}
    @if ($showDetailModal && $detailTransaksi)
        <div class="modal-overlay fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4" wire:click.self="tutupDetail">
            <div class="modal-content bg-white rounded-2xl shadow-xl w-full max-w-lg p-6">

                <div class="flex items-center justify-between mb-1">
                    <h3 class="font-bold text-gray-800">Rincian Transaksi</h3>
                    <button wire:click="tutupDetail" class="text-gray-400 hover:text-gray-600">
                        <x-icon name="cross" class="w-5 h-5 rotate-45" />
                    </button>
                </div>
                <p class="text-sm text-gray-500 mb-5">
                    {{ $detailTransaksi->pasien->nama ?? '-' }} — {{ $detailTransaksi->tanggal->translatedFormat('d M Y') }}
                </p>

                <div class="space-y-2 mb-4">
                    @forelse ($detailTransaksi->items as $item)
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-600">{{ $item->nama_item }} <span class="text-gray-400">x{{ $item->qty }}</span></span>
                            <span class="text-gray-700 font-medium">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</span>
                        </div>
                    @empty
                        <p class="text-sm text-gray-400">Transaksi ini belum punya rincian item.</p>
                    @endforelse
                </div>

                @if ($detailTransaksi->catatan)
                    <div class="border-t border-gray-100 pt-3 mt-3">
                        <p class="text-xs font-medium text-gray-500 mb-1">Catatan</p>
                        <p class="text-sm text-gray-600">{{ $detailTransaksi->catatan }}</p>
                    </div>
                @endif

                <div class="flex items-center justify-between border-t border-gray-100 pt-3">
                    <span class="text-sm font-medium text-gray-500">
                        Total
                        @if ($detailTransaksi->bpjs)
                            <span class="ml-1 text-[10px] font-semibold bg-blue-600 text-white px-2 py-0.5 rounded-full">BPJS</span>
                        @else
                            ({{ $detailTransaksi->metode }})
                        @endif
                    </span>
                    <span class="text-lg font-bold text-gray-800">Rp {{ number_format($detailTransaksi->jumlah, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>
    @endif
</div>
