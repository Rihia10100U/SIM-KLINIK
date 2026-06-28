<div class="space-y-6">

    {{-- Notifikasi sukses --}}
    @if (session('sukses'))
        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 5000)" x-show="show" x-transition.duration.500ms
            class="bg-green-50 border border-green-200 text-green-700 text-sm rounded-xl px-4 py-3">
            {{ session('sukses') }}
        </div>
    @endif

    <div>
        <h2 class="text-xl font-bold text-gray-800">Panggilan Pendaftaran</h2>
        <p class="text-sm text-gray-400 mt-1">{{ now()->locale('id')->translatedFormat('l, d F Y') }}</p>
    </div>

    {{-- ===================== 2 KOLOM: MENUNGGU / DIPANGGIL ===================== --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

        {{-- Menunggu --}}
        <div class="card p-4">
            <div class="flex items-center justify-between mb-4 px-1">
                <h3 class="font-semibold text-gray-800 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-amber-500"></span> Menunggu Nomor
                </h3>
                <span class="badge bg-amber-100 text-amber-600">{{ $antrianMenunggu->count() }}</span>
            </div>

            <div class="space-y-3 max-h-[28rem] overflow-y-auto pr-1">
                @forelse ($antrianMenunggu as $a)
                    <div class="flex items-center justify-between bg-gray-50 rounded-xl p-3" wire:key="menunggu-{{ $a->id }}">
                        <span class="text-lg font-bold text-gray-700">{{ $a->kode_antrian }}</span>
                        <button
                            wire:click="panggil({{ $a->id }})"
                            class="text-xs font-medium text-white bg-klinik-blue-dark px-4 py-1.5 rounded-full hover:bg-blue-800"
                        >
                            Panggil
                        </button>
                    </div>
                @empty
                    <p class="text-sm text-gray-400 text-center py-8">Tidak ada nomor menunggu.</p>
                @endforelse
            </div>
        </div>

        {{-- Dipanggil --}}
        <div class="card p-4">
            <div class="flex items-center justify-between mb-4 px-1">
                <h3 class="font-semibold text-gray-800 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-klinik-blue-dark"></span> Dipanggil — Siap Didaftarkan
                </h3>
                <span class="badge bg-sky-100 text-klinik-blue-dark">{{ $antrianDipanggil->count() }}</span>
            </div>

            <div class="space-y-3 max-h-[28rem] overflow-y-auto pr-1">
                @forelse ($antrianDipanggil as $a)
                    <div class="flex items-center justify-between bg-gray-50 rounded-xl p-3" wire:key="dipanggil-{{ $a->id }}">
                        <span class="text-lg font-bold text-gray-700">{{ $a->kode_antrian }}</span>
                        <button
                            wire:click="bukaForm({{ $a->id }})"
                            class="text-xs font-medium text-white bg-klinik-green px-4 py-1.5 rounded-full hover:bg-klinik-green-dark"
                        >
                            Daftarkan
                        </button>
                    </div>
                @empty
                    <p class="text-sm text-gray-400 text-center py-8">Tidak ada nomor yang sedang dipanggil.</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- ===================== MODAL DAFTARKAN ===================== --}}
    @if ($showModal)
        <div class="modal-overlay fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4 overflow-y-auto" wire:click.self="tutupForm">
            <div class="modal-content bg-white rounded-2xl shadow-xl w-full max-w-xl p-6 my-8">

                <div class="flex items-center justify-between mb-1">
                    <h3 class="font-bold text-gray-800">Daftarkan Pasien</h3>
                    <button wire:click="tutupForm" class="text-gray-400 hover:text-gray-600">
                        <x-icon name="cross" class="w-5 h-5 rotate-45" />
                    </button>
                </div>
                <p class="text-sm text-gray-500 mb-5">Nomor antrian: <strong>{{ $kodeAntrianPendaftaran }}</strong></p>

                {{-- Toggle pasien lama / baru --}}
                <div class="grid grid-cols-2 gap-2 bg-gray-100 rounded-full p-1 mb-5">
                    <button
                        type="button"
                        wire:click="toggleModePasien(false)"
                        class="text-sm font-medium py-1.5 rounded-full transition-colors {{ ! $pasienBaru ? 'bg-klinik-green text-white' : 'text-gray-500' }}"
                    >
                        Pasien Lama
                    </button>
                    <button
                        type="button"
                        wire:click="toggleModePasien(true)"
                        class="text-sm font-medium py-1.5 rounded-full transition-colors {{ $pasienBaru ? 'bg-klinik-green text-white' : 'text-gray-500' }}"
                    >
                        Pasien Baru
                    </button>
                </div>

                <form wire:submit="daftarkan" class="space-y-4">

                    @if (! $pasienBaru)
                        {{-- ===== CARI PASIEN LAMA ===== --}}
                        <div class="relative">
                            <label class="text-xs font-medium text-gray-500">Cari Pasien</label>
                            <input
                                type="text"
                                wire:model.live.debounce.300ms="cariPasien"
                                placeholder="Ketik nama atau No. RM..."
                                autocomplete="off"
                                class="mt-1 w-full bg-gray-100 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-klinik-blue-dark/40"
                            >
                            @error('pasien_id') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror

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
                    @else
                        {{-- ===== FORM PASIEN BARU ===== --}}
                        <div>
                            <label class="text-xs font-medium text-gray-500">Nama Lengkap</label>
                            <input type="text" wire:model="nama" class="mt-1 w-full bg-gray-100 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-klinik-blue-dark/40">
                            @error('nama') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="text-xs font-medium text-gray-500">NIK</label>
                                <input type="text" wire:model="nik" class="mt-1 w-full bg-gray-100 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-klinik-blue-dark/40">
                            </div>
                            <div>
                                <label class="text-xs font-medium text-gray-500">Jenis Kelamin</label>
                                <select wire:model="jenis_kelamin" class="mt-1 w-full bg-gray-100 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-klinik-blue-dark/40">
                                    <option value="">Pilih</option>
                                    <option value="L">Laki-laki</option>
                                    <option value="P">Perempuan</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="text-xs font-medium text-gray-500">Tanggal Lahir</label>
                                <input type="date" wire:model="tanggal_lahir" class="mt-1 w-full bg-gray-100 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-klinik-blue-dark/40">
                            </div>
                            <div>
                                <label class="text-xs font-medium text-gray-500">No. HP</label>
                                <input type="text" wire:model="no_hp" class="mt-1 w-full bg-gray-100 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-klinik-blue-dark/40">
                            </div>
                        </div>

                        <div>
                            <label class="text-xs font-medium text-gray-500">Alamat</label>
                            <textarea wire:model="alamat" rows="2" class="mt-1 w-full bg-gray-100 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-klinik-blue-dark/40"></textarea>
                        </div>
                    @endif

                    {{-- Pilih poli --}}
                    <div>
                        <label class="text-xs font-medium text-gray-500">Poli Tujuan</label>
                        <select wire:model="poli_id" class="mt-1 w-full bg-gray-100 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-klinik-blue-dark/40">
                            <option value="">Pilih poli</option>
                            @foreach ($polis as $poli)
                                <option value="{{ $poli->id }}">{{ $poli->nama }}</option>
                            @endforeach
                        </select>
                        @error('poli_id') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-2">
                        <button type="button" wire:click="tutupForm" class="text-sm font-medium text-gray-500 px-4 py-2 rounded-full hover:bg-gray-100">
                            Batal
                        </button>
                        <button
                            type="submit"
                            wire:loading.attr="disabled"
                            wire:target="daftarkan"
                            class="text-sm font-medium text-white bg-klinik-green px-5 py-2 rounded-full hover:bg-klinik-green-dark disabled:opacity-60"
                        >
                            <span wire:loading.remove wire:target="daftarkan">Daftarkan & Antrikan ke Poli</span>
                            <span wire:loading wire:target="daftarkan">Menyimpan...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
