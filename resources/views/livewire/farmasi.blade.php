<div class="space-y-6">

    {{-- Notifikasi sukses --}}
    @if (session('sukses'))
        <div class="bg-green-50 border border-green-200 text-green-700 text-sm rounded-xl px-4 py-3">
            {{ session('sukses') }}
        </div>
    @endif

    {{-- ===================== HEADER ===================== --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-gray-800">Farmasi</h2>
            <p class="text-sm text-gray-400 mt-1">Data obat & stok di klinik</p>
        </div>

        <div class="flex items-center gap-3">
            <div class="relative">
                <x-icon name="search" class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" />
                <input
                    type="text"
                    wire:model.live.debounce.400ms="cari"
                    placeholder="Cari nama atau kode obat..."
                    class="w-64 pl-9 pr-4 py-2 text-sm bg-gray-50 border border-gray-300 rounded-full transition-all duration-300 outline-none hover:border-gray-400 focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/20"
                >
            </div>

            <button
                wire:click="bukaForm"
                class="flex items-center gap-2 bg-klinik-blue text-white text-sm font-medium px-4 py-2 rounded-full hover:bg-klinik-blue-dark transition-colors whitespace-nowrap"
            >
                <x-icon name="pill" class="w-4 h-4" /> Tambah Obat
            </button>
        </div>
    </div>

    {{-- ===================== TABEL OBAT ===================== --}}
    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 text-left text-gray-400 text-xs uppercase tracking-wide">
                        <th class="px-5 py-3 font-medium">Kode</th>
                        <th class="px-5 py-3 font-medium">Nama Obat</th>
                        <th class="px-5 py-3 font-medium">Kategori</th>
                        <th class="px-5 py-3 font-medium">Stok</th>
                        <th class="px-5 py-3 font-medium">Status</th>
                        <th class="px-5 py-3 font-medium">Harga</th>
                        <th class="px-5 py-3 font-medium text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse ($obats as $o)
                        @php
                            $status = $o->statusStok();
                            $badgeClass = match ($status) {
                                'aman'    => 'bg-green-100 text-green-600',
                                'menipis' => 'bg-amber-100 text-amber-600',
                                'habis'   => 'bg-red-100 text-red-500',
                            };
                            $badgeLabel = match ($status) {
                                'aman'    => 'Stok Aman',
                                'menipis' => 'Stok Menipis',
                                'habis'   => 'Stok Habis',
                            };
                        @endphp
                        <tr class="hover:bg-gray-50/60" wire:key="obat-{{ $o->id }}">
                            <td class="px-5 py-3 font-medium text-sky-500 whitespace-nowrap">{{ $o->kode_obat }}</td>
                            <td class="px-5 py-3 text-gray-700">{{ $o->nama }}</td>
                            <td class="px-5 py-3 text-gray-500">{{ $o->kategori ?: '-' }}</td>
                            <td class="px-5 py-3 text-gray-600 whitespace-nowrap">{{ $o->stok }} {{ $o->satuan }}</td>
                            <td class="px-5 py-3">
                                <span class="badge {{ $badgeClass }}">{{ $badgeLabel }}</span>
                            </td>
                            <td class="px-5 py-3 text-gray-500 whitespace-nowrap">
                                Rp {{ number_format($o->harga, 0, ',', '.') }}
                            </td>
                            <td class="px-5 py-3">
                                <div class="flex items-center justify-end gap-3">
                                    <button wire:click="bukaStokForm({{ $o->id }})" class="text-xs font-medium text-klinik-green hover:underline">
                                        Atur Stok
                                    </button>
                                    <button wire:click="edit({{ $o->id }})" class="text-xs font-medium text-sky-500 hover:underline">
                                        Edit
                                    </button>
                                    <button
                                        wire:click="hapus({{ $o->id }})"
                                        wire:confirm="Hapus obat {{ $o->nama }}?"
                                        class="text-xs font-medium text-red-500 hover:underline"
                                    >
                                        Hapus
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-10 text-center text-gray-400 text-sm">
                                @if ($cari)
                                    Tidak ada obat yang cocok dengan pencarian "{{ $cari }}".
                                @else
                                    Belum ada data obat. Klik "Tambah Obat" untuk menambahkan.
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($obats->hasPages())
            <div class="px-5 py-4 border-t border-gray-50">
                {{ $obats->links() }}
            </div>
        @endif
    </div>

    {{-- ===================== MODAL TAMBAH / EDIT OBAT ===================== --}}
    @if ($showModal)
        <div class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4" wire:click.self="tutupForm">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg p-6">

                <div class="flex items-center justify-between mb-5">
                    <h3 class="font-bold text-gray-800">
                        {{ $editId ? 'Edit Data Obat' : 'Tambah Obat Baru' }}
                    </h3>
                    <button wire:click="tutupForm" class="text-gray-400 hover:text-gray-600">
                        <x-icon name="cross" class="w-5 h-5 rotate-45" />
                    </button>
                </div>

                <form wire:submit="simpan" class="space-y-4">
                    <div>
                        <label class="text-xs font-medium text-gray-500">Nama Obat</label>
                        <input type="text" wire:model="nama" class="mt-1 w-full bg-gray-100 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-klinik-blue/40">
                        @error('nama') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-xs font-medium text-gray-500">Kategori</label>
                            <input type="text" wire:model="kategori" placeholder="mis. Antibiotik" class="mt-1 w-full bg-gray-100 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-klinik-blue/40">
                        </div>
                        <div>
                            <label class="text-xs font-medium text-gray-500">Satuan</label>
                            <select wire:model="satuan" class="mt-1 w-full bg-gray-100 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-klinik-blue/40">
                                <option value="Tablet">Tablet</option>
                                <option value="Kapsul">Kapsul</option>
                                <option value="Botol">Botol</option>
                                <option value="Strip">Strip</option>
                                <option value="Tube">Tube</option>
                                <option value="Ampul">Ampul</option>
                                <option value="Sachet">Sachet</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-4">
                        <div>
                            <label class="text-xs font-medium text-gray-500">Stok Awal</label>
                            <input type="number" min="0" wire:model="stok" class="mt-1 w-full bg-gray-100 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-klinik-blue/40">
                            @error('stok') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="text-xs font-medium text-gray-500">Stok Minimum</label>
                            <input type="number" min="0" wire:model="stok_minimum" class="mt-1 w-full bg-gray-100 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-klinik-blue/40">
                        </div>
                        <div>
                            <label class="text-xs font-medium text-gray-500">Harga (Rp)</label>
                            <input type="number" min="0" wire:model="harga" class="mt-1 w-full bg-gray-100 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-klinik-blue/40">
                            @error('harga') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    @if ($editId)
                        <p class="text-xs text-gray-400">
                            Tips: untuk menambah/mengurangi stok sehari-hari, gunakan tombol "Atur Stok" di tabel — supaya lebih cepat dan tidak perlu buka form edit ini.
                        </p>
                    @endif

                    <div class="flex items-center justify-end gap-3 pt-2">
                        <button type="button" wire:click="tutupForm" class="text-sm font-medium text-gray-500 px-4 py-2 rounded-full hover:bg-gray-100">
                            Batal
                        </button>
                        <button
                            type="submit"
                            wire:loading.attr="disabled"
                            wire:target="simpan"
                            class="text-sm font-medium text-white bg-klinik-blue px-5 py-2 rounded-full hover:bg-klinik-blue-dark disabled:opacity-60"
                        >
                            <span wire:loading.remove wire:target="simpan">Simpan</span>
                            <span wire:loading wire:target="simpan">Menyimpan...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- ===================== MODAL ATUR STOK ===================== --}}
    @if ($showStokModal)
        <div class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4" wire:click.self="tutupStokForm">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm p-6">

                <div class="flex items-center justify-between mb-1">
                    <h3 class="font-bold text-gray-800">Atur Stok</h3>
                    <button wire:click="tutupStokForm" class="text-gray-400 hover:text-gray-600">
                        <x-icon name="cross" class="w-5 h-5 rotate-45" />
                    </button>
                </div>
                <p class="text-sm text-gray-500 mb-5">{{ $stokObatNama }} — stok saat ini: <strong>{{ $stokSaatIni }}</strong></p>

                <form wire:submit="simpanStok" class="space-y-4">
                    <div class="grid grid-cols-2 gap-2 bg-gray-100 rounded-full p-1">
                        <button
                            type="button"
                            wire:click="$set('jenisPerubahan', 'tambah')"
                            class="text-sm font-medium py-1.5 rounded-full transition-colors {{ $jenisPerubahan === 'tambah' ? 'bg-klinik-green text-white' : 'text-gray-500' }}"
                        >
                            + Tambah
                        </button>
                        <button
                            type="button"
                            wire:click="$set('jenisPerubahan', 'kurangi')"
                            class="text-sm font-medium py-1.5 rounded-full transition-colors {{ $jenisPerubahan === 'kurangi' ? 'bg-red-500 text-white' : 'text-gray-500' }}"
                        >
                            − Kurangi
                        </button>
                    </div>

                    <div>
                        <label class="text-xs font-medium text-gray-500">Jumlah</label>
                        <input
                            type="number"
                            min="1"
                            wire:model="jumlahPerubahan"
                            class="mt-1 w-full bg-gray-100 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-klinik-blue/40"
                        >
                        @error('jumlahPerubahan') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-2">
                        <button type="button" wire:click="tutupStokForm" class="text-sm font-medium text-gray-500 px-4 py-2 rounded-full hover:bg-gray-100">
                            Batal
                        </button>
                        <button
                            type="submit"
                            wire:loading.attr="disabled"
                            wire:target="simpanStok"
                            class="text-sm font-medium text-white bg-klinik-blue px-5 py-2 rounded-full hover:opacity-90 disabled:opacity-60"
                        >
                            <span wire:loading.remove wire:target="simpanStok">Simpan</span>
                            <span wire:loading wire:target="simpanStok">Menyimpan...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
