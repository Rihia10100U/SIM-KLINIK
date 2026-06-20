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
            <h2 class="text-xl font-bold text-gray-800">Manajemen Antrian</h2>
            <p class="text-sm text-gray-400 mt-1">{{ now()->locale('id')->translatedFormat('l, d F Y') }}</p>
        </div>

        <div class="flex items-center gap-3">
            <select
                wire:model.live="filterPoli"
                class="w-64 pl-9 pr-4 py-2 text-sm bg-gray-50 border border-gray-300 rounded-full transition-all duration-300 outline-none hover:border-gray-400 focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/20"
            >
                <option value="">Semua Poli</option>
                @foreach ($polis as $poli)
                    <option value="{{ $poli->id }}">{{ $poli->nama }}</option>
                @endforeach
            </select>

            <button
                wire:click="bukaForm"
                class="flex items-center gap-2 bg-klinik-blue text-white text-sm font-medium px-4 py-2 rounded-full hover:bg-klinik-blue-dark transition-colors whitespace-nowrap"
            >
                <x-icon name="ticket" class="w-4 h-4" /> Daftarkan ke Antrian
            </button>
        </div>
    </div>

    {{-- ===================== PAPAN KANBAN ===================== --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

        {{-- Kolom: Menunggu --}}
        <div class="card p-4">
            <div class="flex items-center justify-between mb-4 px-1">
                <h3 class="font-semibold text-gray-800 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-amber-500"></span> Menunggu
                </h3>
                <span class="badge bg-amber-100 text-amber-600">{{ $antrianMenunggu->count() }}</span>
            </div>

            <div class="space-y-3 max-h-[32rem] overflow-y-auto pr-1">
                @forelse ($antrianMenunggu as $a)
                    <div class="bg-gray-50 rounded-xl p-3" wire:key="menunggu-{{ $a->id }}">
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-sky-500 font-bold text-sm">{{ $a->kode_antrian }}</span>
                            <span class="text-[11px] text-gray-400">{{ $a->poli->nama }}</span>
                        </div>
                        <p class="text-sm font-medium text-gray-700 mb-3">{{ $a->pasien->nama }}</p>
                        <div class="flex items-center gap-2">
                            <button
                                wire:click="panggil({{ $a->id }})"
                                class="flex-1 text-xs font-medium text-white bg-sky-500 px-3 py-1.5 rounded-full hover:bg-sky-600"
                            >
                                Panggil
                            </button>
                            <button
                                wire:click="batalkan({{ $a->id }})"
                                wire:confirm="Batalkan antrian {{ $a->kode_antrian }} ({{ $a->pasien->nama }})?"
                                class="text-xs font-medium text-red-500 px-2 hover:underline"
                            >
                                Batal
                            </button>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-gray-400 text-center py-8">Tidak ada antrian menunggu.</p>
                @endforelse
            </div>
        </div>

       {{-- Kolom: Dipanggil --}}
        <div class="card p-4">
            <div class="flex items-center justify-between mb-4 px-1">
                <h3 class="font-semibold text-gray-800 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-sky-500"></span> Dipanggil
                </h3>
                <span class="badge bg-sky-100 text-sky-600">{{ $antrianDipanggil->count() }}</span>
            </div>

            <div class="space-y-3 max-h-[32rem] overflow-y-auto pr-1">
                @forelse ($antrianDipanggil as $a)
                    <div class="bg-gray-50 rounded-xl p-3" wire:key="dipanggil-{{ $a->id }}">
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-sky-500 font-bold text-sm">{{ $a->kode_antrian }}</span>
                            <span class="text-[11px] text-gray-400">{{ $a->poli->nama }}</span>
                        </div>
                        <p class="text-sm font-medium text-gray-700 mb-3">{{ $a->pasien->nama }}</p>
                        
                        {{-- PERUBAHAN DI SINI: Menggunakan flex untuk membagi dua tombol --}}
                        <div class="flex items-center gap-2">
                            <button
                                wire:click="selesaikan({{ $a->id }})"
                                class="flex-1 text-xs font-medium text-white bg-green-500 px-3 py-1.5 rounded-full hover:bg-green-600 transition-colors"
                            >
                                Tandai Selesai
                            </button>
                            <button
                                wire:click="panggilUlang({{ $a->id }})"
                                class="text-xs font-medium text-sky-500 px-2 hover:underline whitespace-nowrap"
                            >
                                Panggil Ulang
                            </button>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-gray-400 text-center py-8">Tidak ada antrian dipanggil.</p>
                @endforelse
            </div>
        </div>

        {{-- Kolom: Selesai --}}
        <div class="card p-4">
            <div class="flex items-center justify-between mb-4 px-1">
                <h3 class="font-semibold text-gray-800 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-green-500"></span> Selesai
                </h3>
                <span class="badge bg-green-100 text-green-600">{{ $antrianSelesai->count() }}</span>
            </div>

            <div class="space-y-3 max-h-[32rem] overflow-y-auto pr-1">
                @forelse ($antrianSelesai as $a)
                    <div class="bg-gray-50 rounded-xl p-3 opacity-70" wire:key="selesai-{{ $a->id }}">
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-gray-400 font-bold text-sm line-through">{{ $a->kode_antrian }}</span>
                            <span class="text-[11px] text-gray-400">{{ $a->poli->nama }}</span>
                        </div>
                        <p class="text-sm font-medium text-gray-600">{{ $a->pasien->nama }}</p>
                    </div>
                @empty
                    <p class="text-sm text-gray-400 text-center py-8">Belum ada antrian selesai.</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- ===================== MODAL DAFTARKAN ANTRIAN ===================== --}}
    @if ($showModal)
        <div
            class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4"
            wire:click.self="tutupForm"
        >
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg p-6">

                <div class="flex items-center justify-between mb-5">
                    <h3 class="font-bold text-gray-800">Daftarkan ke Antrian</h3>
                    <button wire:click="tutupForm" class="text-gray-400 hover:text-gray-600">
                        <x-icon name="cross" class="w-5 h-5 rotate-45" />
                    </button>
                </div>

                <form wire:submit="daftarkan" class="space-y-4">

                    {{-- Cari pasien --}}
                    <div class="relative">
                        <label class="text-xs font-medium text-gray-500">Cari Pasien</label>
                        <input
                            type="text"
                            wire:model.live.debounce.300ms="cariPasien"
                            placeholder="Ketik nama atau No. RM..."
                            autocomplete="off"
                            class="mt-1 w-full bg-gray-100 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-klinik-blue/40"
                        >
                        @error('pasien_id') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror

                        {{-- Dropdown hasil pencarian --}}
                        @if (! $pasien_id && strlen($cariPasien) >= 2)
                            <div class="absolute z-10 mt-1 w-full bg-white border border-gray-100 rounded-lg shadow-lg max-h-48 overflow-y-auto">
                                @forelse ($this->pasienOptions() as $opt)
                                    <button
                                        type="button"
                                        wire:click="pilihPasien({{ $opt->id }})"
                                        class="w-full text-left px-3 py-2 text-sm hover:bg-gray-50 flex items-center justify-between"
                                    >
                                        <span class="text-gray-700">{{ $opt->nama }}</span>
                                        <span class="text-xs text-gray-400">{{ $opt->no_rm }}</span>
                                    </button>
                                @empty
                                    <p class="px-3 py-2 text-sm text-gray-400">Pasien tidak ditemukan.</p>
                                @endforelse
                            </div>
                        @endif
                    </div>

                    {{-- Pilih poli --}}
                    <div>
                        <label class="text-xs font-medium text-gray-500">Poli Tujuan</label>
                        <select
                            wire:model="poli_id"
                            class="mt-1 w-full bg-gray-100 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-klinik-blue/40"
                        >
                            <option value="">Pilih poli</option>
                            @foreach ($polis as $poli)
                                @if ($poli->aktif)
                                    <option value="{{ $poli->id }}">{{ $poli->nama }}</option>
                                @endif
                            @endforeach
                        </select>
                        @error('poli_id') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-2">
                        <button
                            type="button"
                            wire:click="tutupForm"
                            class="text-sm font-medium text-gray-500 px-4 py-2 rounded-full hover:bg-gray-100"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            wire:loading.attr="disabled"
                            wire:target="daftarkan"
                            class="text-sm font-medium text-white bg-klinik-blue px-5 py-2 rounded-full hover:bg-klinik-blue-dark disabled:opacity-60"
                        >
                            <span wire:loading.remove wire:target="daftarkan">Daftarkan</span>
                            <span wire:loading wire:target="daftarkan">Menyimpan...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
