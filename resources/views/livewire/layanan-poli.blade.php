<div class="space-y-6">

    {{-- Notifikasi --}}
    @if (session('sukses'))
        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 5000)" x-show="show" x-transition.duration.500ms
            class="bg-blue-50 border border-blue-200 text-blue-700 text-sm rounded-xl px-4 py-3">
            {{ session('sukses') }}
        </div>
    @endif
    @if (session('gagal'))
        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 5000)" x-show="show" x-transition.duration.500ms
            class="bg-red-50 border border-red-200 text-red-600 text-sm rounded-xl px-4 py-3">
            {{ session('gagal') }}
        </div>
    @endif

    {{-- ===================== HEADER ===================== --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-gray-800">Layanan & Poli</h2>
            <p class="text-sm text-gray-400 mt-1">Kelola daftar poli dan harga layanan per poli dalam satu halaman</p>
        </div>
        <button
            wire:click="bukaPoliModal()"
            class="flex items-center gap-2 bg-klinik-green text-white text-sm font-medium px-4 py-2 rounded-full hover:bg-klinik-green-dark transition-colors whitespace-nowrap self-start"
        >
            <x-icon name="cross" class="w-4 h-4" /> Tambah Poli
        </button>
    </div>

    {{-- ===================== ACCORDION POLI ===================== --}}
    <div class="space-y-3">
        @forelse ($polis as $p)
            <div class="card overflow-hidden" wire:key="poli-{{ $p->id }}">

                {{-- Header accordion --}}
                <div
                    wire:click="toggleAccordion({{ $p->id }})"
                    class="flex items-center justify-between px-5 py-4 cursor-pointer hover:bg-gray-50/60 transition-colors select-none"
                >
                    <div class="flex items-center gap-4">
                        {{-- Badge kode --}}
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center text-sm font-bold shrink-0
                            {{ $p->aktif ? 'bg-sky-500 text-white' : 'bg-gray-200 text-gray-400' }}">
                            {{ $p->kode }}
                        </div>

                        <div>
                            <p class="font-semibold {{ $p->aktif ? 'text-gray-800' : 'text-gray-400' }}">
                                {{ $p->nama }}
                            </p>
                            <div class="flex items-center gap-3 mt-0.5">
                                <span class="text-xs text-gray-400">
                                    {{ $p->layanans->count() }} layanan
                                </span>
                                @if ($p->antrian_hari_ini > 0)
                                    <span class="text-xs text-sky-500 font-medium">
                                        {{ $p->antrian_hari_ini }} antrian hari ini
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        {{-- Toggle aktif --}}
                        <button
                            wire:click.stop="toggleAktifPoli({{ $p->id }})"
                            class="badge {{ $p->aktif ? 'bg-green-100 text-green-600' : 'bg-gray-100 text-gray-500' }}"
                        >
                            {{ $p->aktif ? 'Aktif' : 'Nonaktif' }}
                        </button>

                        {{-- Edit poli --}}
                        <button
                            wire:click.stop="bukaPoliModal({{ $p->id }})"
                            class="text-xs font-medium text-sky-500 hover:underline"
                        >
                            Edit
                        </button>

                        {{-- Hapus poli --}}
                        <button
                            wire:click.stop="hapusPoli({{ $p->id }})"
                            wire:confirm="Hapus poli {{ $p->nama }} beserta semua layanannya?"
                            class="text-xs font-medium text-red-400 hover:underline"
                        >
                            Hapus
                        </button>

                        {{-- Panah accordion --}}
                        <x-icon
                            name="chevron-up"
                            class="w-4 h-4 text-gray-400 transition-transform duration-200 {{ $poliAktifId === $p->id ? 'rotate-0' : 'rotate-180' }}"
                        />
                    </div>
                </div>

                {{-- Konten accordion: daftar layanan poli ini --}}
                @if ($poliAktifId === $p->id)
                    <div class="border-t border-gray-100 bg-gray-50/40">

                        {{-- Tabel layanan --}}
                        @if ($p->layanans->isNotEmpty())
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="text-left text-gray-400 text-xs uppercase tracking-wide border-b border-gray-100">
                                        <th class="px-5 py-2.5 font-medium">Nama Layanan</th>
                                        <th class="px-5 py-2.5 font-medium">Kategori</th>
                                        <th class="px-5 py-2.5 font-medium">Harga</th>
                                        <th class="px-5 py-2.5 font-medium">Status</th>
                                        <th class="px-5 py-2.5 font-medium text-right">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @foreach ($p->layanans as $l)
                                        <tr class="hover:bg-white transition-colors" wire:key="layanan-{{ $l->id }}">
                                            <td class="px-5 py-2.5 text-gray-700">{{ $l->nama }}</td>
                                            <td class="px-5 py-2.5">
                                                <span class="text-xs px-2 py-0.5 rounded-full
                                                    {{ $l->kategori === 'konsultasi' ? 'bg-sky-100 text-sky-600' : ($l->kategori === 'tindakan' ? 'bg-purple-100 text-purple-600' : 'bg-gray-100 text-gray-500') }}">
                                                    {{ ucfirst($l->kategori) }}
                                                </span>
                                            </td>
                                            <td class="px-5 py-2.5 text-gray-600 whitespace-nowrap">
                                                Rp {{ number_format($l->harga, 0, ',', '.') }}
                                            </td>
                                            <td class="px-5 py-2.5">
                                                <button
                                                    wire:click="toggleAktifLayanan({{ $l->id }})"
                                                    class="badge {{ $l->aktif ? 'bg-green-100 text-green-600' : 'bg-gray-100 text-gray-400' }}"
                                                >
                                                    {{ $l->aktif ? 'Aktif' : 'Nonaktif' }}
                                                </button>
                                            </td>
                                            <td class="px-5 py-2.5 text-right">
                                                <div class="flex items-center justify-end gap-3">
                                                    <button
                                                        wire:click="bukaLayananModal({{ $l->id }})"
                                                        class="text-xs font-medium text-sky-500 hover:underline"
                                                    >
                                                        Edit
                                                    </button>
                                                    <button
                                                        wire:click="hapusLayanan({{ $l->id }})"
                                                        wire:confirm="Hapus layanan {{ $l->nama }}?"
                                                        class="text-xs font-medium text-red-400 hover:underline"
                                                    >
                                                        Hapus
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @else
                            <p class="px-5 py-4 text-sm text-gray-400">
                                Belum ada layanan untuk poli ini.
                            </p>
                        @endif

                        {{-- Tombol tambah layanan --}}
                        <div class="px-5 py-3 border-t border-gray-100">
                            <button
                                wire:click="bukaLayananModal(null, {{ $p->id }})"
                                class="text-xs font-medium text-klinik-green hover:underline flex items-center gap-1"
                            >
                                <x-icon name="cross" class="w-3.5 h-3.5" /> Tambah Layanan untuk {{ $p->nama }}
                            </button>
                        </div>
                    </div>
                @endif
            </div>
        @empty
            <div class="card p-10 text-center text-gray-400 text-sm">
                Belum ada poli. Klik "Tambah Poli" untuk memulai.
            </div>
        @endforelse
    </div>

   
    {{-- ===================== MODAL POLI ===================== --}}
    @if ($showPoliModal)
        <div class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4" wire:click.self="tutupPoliModal">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm p-6">

                <div class="flex items-center justify-between mb-5">
                    <h3 class="font-bold text-gray-800">{{ $editPoliId ? 'Edit Poli' : 'Tambah Poli Baru' }}</h3>
                    <button wire:click="tutupPoliModal" class="text-gray-400 hover:text-gray-600">
                        <x-icon name="cross" class="w-5 h-5 rotate-45" />
                    </button>
                </div>

                <form wire:submit="simpanPoli" class="space-y-4">
                    <div>
                        <label class="text-xs font-medium text-gray-500">Kode Poli</label>
                        <input
                            type="text"
                            wire:model="poliKode"
                            maxlength="5"
                            placeholder="mis. E, F, G ..."
                            class="mt-1 w-full bg-gray-100 rounded-lg px-3 py-2 text-sm uppercase focus:outline-none focus:ring-2 focus:ring-klinik-blue/40"
                        >
                        <p class="text-[11px] text-gray-400 mt-1">Awalan nomor antrian (mis. kode "E" → E-001)</p>
                        @error('poliKode') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="text-xs font-medium text-gray-500">Nama Poli</label>
                        <input
                            type="text"
                            wire:model="poliNama"
                            placeholder="mis. Poli Mata"
                            class="mt-1 w-full bg-gray-100 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-klinik-blue/40"
                        >
                        @error('poliNama') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <label class="flex items-center gap-2 text-sm text-gray-600">
                        <input type="checkbox" wire:model="poliAktif" class="rounded border-gray-300 text-klinik-green focus:ring-klinik-green">
                        Poli aktif
                    </label>

                    <div class="flex items-center justify-end gap-3 pt-2">
                        <button type="button" wire:click="tutupPoliModal" class="text-sm font-medium text-gray-500 px-4 py-2 rounded-full hover:bg-gray-100">
                            Batal
                        </button>
                        <button
                            type="submit"
                            wire:loading.attr="disabled"
                            wire:target="simpanPoli"
                            class="text-sm font-medium text-white bg-klinik-green px-5 py-2 rounded-full hover:bg-klinik-green-dark disabled:opacity-60"
                        >
                            <span wire:loading.remove wire:target="simpanPoli">Simpan</span>
                            <span wire:loading wire:target="simpanPoli">Menyimpan...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- ===================== MODAL LAYANAN ===================== --}}
    @if ($showLayananModal)
        <div class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4" wire:click.self="tutupLayananModal">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6">

                <div class="flex items-center justify-between mb-5">
                    <div>
                        <h3 class="font-bold text-gray-800">{{ $editLayananId ? 'Edit Layanan' : 'Tambah Layanan' }}</h3>
                        @if ($layananPoliId)
                            @php $namaPoliModal = $polis->find($layananPoliId)?->nama ?? ''; @endphp
                            <p class="text-xs text-gray-400 mt-0.5">{{ $namaPoliModal }}</p>
                        @else
                            <p class="text-xs text-gray-400 mt-0.5">Layanan Umum (tidak terikat poli)</p>
                        @endif
                    </div>
                    <button wire:click="tutupLayananModal" class="text-gray-400 hover:text-gray-600">
                        <x-icon name="cross" class="w-5 h-5 rotate-45" />
                    </button>
                </div>

                <form wire:submit="simpanLayanan" class="space-y-4">
                    <div>
                        <label class="text-xs font-medium text-gray-500">Nama Layanan</label>
                        <input
                            type="text"
                            wire:model="layananNama"
                            placeholder="mis. Konsultasi Poli Umum"
                            class="mt-1 w-full bg-gray-100 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-klinik-blue/40"
                        >
                        @error('layananNama') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-xs font-medium text-gray-500">Kategori</label>
                            <select
                                wire:model="layananKategori"
                                class="mt-1 w-full bg-gray-100 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-klinik-blue/40"
                            >
                                <option value="konsultasi">Konsultasi</option>
                                <option value="tindakan">Tindakan</option>
                                <option value="lainnya">Lainnya</option>
                            </select>
                        </div>
                        <div>
                            <label class="text-xs font-medium text-gray-500">Harga (Rp)</label>
                            <input
                                type="number"
                                min="0"
                                wire:model="layananHarga"
                                class="mt-1 w-full bg-gray-100 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-klinik-blue/40"
                            >
                            @error('layananHarga') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <label class="flex items-center gap-2 text-sm text-gray-600">
                        <input type="checkbox" wire:model="layananAktif" class="rounded border-gray-300 text-klinik-green focus:ring-klinik-green">
                        Layanan aktif (tampil sebagai pilihan di Kasir & Billing)
                    </label>

                    @if ($layananKategori === 'konsultasi' && $layananPoliId)
                        <p class="text-xs text-amber-600 bg-amber-50 rounded-lg px-3 py-2">
                            Layanan konsultasi ini akan otomatis dipakai sebagai harga default saat kasir membuat tagihan untuk antrian poli ini.
                        </p>
                    @endif

                    <div class="flex items-center justify-end gap-3 pt-2">
                        <button type="button" wire:click="tutupLayananModal" class="text-sm font-medium text-gray-500 px-4 py-2 rounded-full hover:bg-gray-100">
                            Batal
                        </button>
                        <button
                            type="submit"
                            wire:loading.attr="disabled"
                            wire:target="simpanLayanan"
                            class="text-sm font-medium text-white bg-klinik-blue px-5 py-2 rounded-full hover:bg-klinik-blue-dark disabled:opacity-60"
                        >
                            <span wire:loading.remove wire:target="simpanLayanan">Simpan</span>
                            <span wire:loading wire:target="simpanLayanan">Menyimpan...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
