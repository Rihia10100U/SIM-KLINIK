<div class="space-y-6">

    {{-- Notifikasi sukses --}}
    @if (session('sukses'))
        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 5000)" x-show="show" x-transition.duration.500ms
            class="bg-green-50 border border-green-200 text-green-700 text-sm rounded-xl px-4 py-3">
            {{ session('sukses') }}
        </div>
    @endif

    {{-- ===================== HEADER ===================== --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-gray-800">Data Layanan</h2>
            <p class="text-sm text-gray-400 mt-1">Daftar harga konsultasi & tindakan, dipakai otomatis di Kasir & Billing</p>
        </div>

        <div class="flex items-center gap-3">
            <div class="relative">
                <x-icon name="search" class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" />
                <input
    type="text"
    wire:model.live.debounce.400ms="cari"
    placeholder="Cari nama layanan..."
    class="search-input"
>
            </div>

            <button
                wire:click="bukaForm"
                class="btn-success"
            >
                <x-icon name="tag" class="w-4 h-4" /> Tambah Layanan
            </button>
        </div>
    </div>

    {{-- ===================== TABEL LAYANAN ===================== --}}
    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 text-left text-gray-400 text-xs uppercase tracking-wide">
                        <th class="px-5 py-3 font-medium">Nama Layanan</th>
                        <th class="px-5 py-3 font-medium">Kategori</th>
                        <th class="px-5 py-3 font-medium">Poli</th>
                        <th class="px-5 py-3 font-medium">Harga</th>
                        <th class="px-5 py-3 font-medium">Status</th>
                        <th class="px-5 py-3 font-medium text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse ($layanans as $l)
                        <tr class="hover:bg-gray-50/60" wire:key="layanan-{{ $l->id }}">
                            <td class="px-5 py-3 text-gray-700">{{ $l->nama }}</td>
                            <td class="px-5 py-3 text-gray-500 capitalize">{{ $l->kategori }}</td>
                            <td class="px-5 py-3 text-gray-500">{{ $l->poli->nama ?? '-' }}</td>
                            <td class="px-5 py-3 text-gray-600 whitespace-nowrap">Rp {{ number_format($l->harga, 0, ',', '.') }}</td>
                            <td class="px-5 py-3">
                                <button
                                    wire:click="toggleAktif({{ $l->id }})"
                                    class="badge {{ $l->aktif ? 'bg-green-100 text-green-600' : 'bg-gray-100 text-gray-500' }}"
                                >
                                    {{ $l->aktif ? 'Aktif' : 'Nonaktif' }}
                                </button>
                            </td>
                            <td class="px-5 py-3">
                                <div class="flex items-center justify-end gap-3">
                                    <button wire:click="edit({{ $l->id }})" class="text-xs font-medium text-klinik-blue-dark hover:underline">
                                        Edit
                                    </button>
                                    <button
                                        wire:click="hapus({{ $l->id }})"
                                        wire:confirm="Hapus layanan {{ $l->nama }}?"
                                        class="text-xs font-medium text-red-500 hover:underline"
                                    >
                                        Hapus
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-10 text-center text-gray-400 text-sm">
                                @if ($cari)
                                    Tidak ada layanan yang cocok dengan pencarian "{{ $cari }}".
                                @else
                                    Belum ada data layanan. Klik "Tambah Layanan" untuk menambahkan.
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($layanans->hasPages())
            <div class="px-5 py-4 border-t border-gray-50">
                {{ $layanans->links() }}
            </div>
        @endif
    </div>

    {{-- ===================== MODAL TAMBAH / EDIT ===================== --}}
    @if ($showModal)
        <div class="modal-overlay fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4" wire:click.self="tutupForm">
            <div class="modal-content bg-white rounded-2xl shadow-xl w-full max-w-lg p-6">

                <div class="flex items-center justify-between mb-5">
                    <h3 class="font-bold text-gray-800">
                        {{ $editId ? 'Edit Layanan' : 'Tambah Layanan Baru' }}
                    </h3>
                    <button wire:click="tutupForm" class="text-gray-400 hover:text-gray-600">
                        <x-icon name="cross" class="w-5 h-5 rotate-45" />
                    </button>
                </div>

                <form wire:submit="simpan" class="space-y-4">
                    <div>
                        <label class="text-xs font-medium text-gray-500">Nama Layanan</label>
                        <input type="text" wire:model="nama" placeholder="mis. Konsultasi Poli Umum" class="mt-1 w-full bg-gray-100 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-klinik-blue-dark/40">
                        @error('nama') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-xs font-medium text-gray-500">Kategori</label>
                            <select wire:model="kategori" class="mt-1 w-full bg-gray-100 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-klinik-blue-dark/40">
                                <option value="konsultasi">Konsultasi</option>
                                <option value="tindakan">Tindakan</option>
                                <option value="lainnya">Lainnya</option>
                            </select>
                        </div>
                        <div>
                            <label class="text-xs font-medium text-gray-500">Poli Terkait</label>
                            <select wire:model="poli_id" class="mt-1 w-full bg-gray-100 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-klinik-blue-dark/40">
                                <option value="">Tidak terikat poli</option>
                                @foreach ($polis as $poli)
                                    <option value="{{ $poli->id }}">{{ $poli->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="text-xs font-medium text-gray-500">Harga (Rp)</label>
                        <input type="number" min="0" wire:model="harga" class="mt-1 w-full bg-gray-100 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-klinik-blue-dark/40">
                        @error('harga') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <label class="flex items-center gap-2 text-sm text-gray-600">
                        <input type="checkbox" wire:model="aktif" class="rounded border-gray-300 text-klinik-green focus:ring-klinik-green">
                        Layanan aktif (tampil sebagai pilihan di Kasir & Billing)
                    </label>

                    @if ($kategori === 'konsultasi')
                        <p class="text-xs text-gray-400">
                            Tips: kalau "Poli Terkait" diisi dan kategori "Konsultasi", harga ini akan otomatis dipakai sebagai biaya konsultasi default saat kasir membuat tagihan untuk antrian di poli tersebut.
                        </p>
                    @endif

                    <div class="flex items-center justify-end gap-3 pt-2">
                        <button type="button" wire:click="tutupForm" class="btn-cancel">
                            Batal
                        </button>
                        <button
                            type="submit"
                            wire:loading.attr="disabled"
                            wire:target="simpan"
                            class="btn-primary"
                        >
                            <span wire:loading.remove wire:target="simpan">Simpan</span>
                            <span wire:loading wire:target="simpan">Menyimpan...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
