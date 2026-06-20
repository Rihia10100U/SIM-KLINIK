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
            <h2 class="text-xl font-bold text-gray-800">Rekam Medis</h2>
            <p class="text-sm text-gray-400 mt-1">Riwayat pemeriksaan & diagnosis pasien</p>
        </div>

        <div class="flex items-center gap-3">
            <div class="relative">
                <x-icon name="search" class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" />
                <input
                    type="text"
                    wire:model.live.debounce.400ms="cari"
                    placeholder="Cari nama pasien atau No. RM..."
                    class="w-64 pl-9 pr-4 py-2 text-sm bg-gray-50 border border-gray-300 rounded-full transition-all duration-300 outline-none hover:border-gray-400 focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/20"
                >
            </div>

            <button
                wire:click="bukaForm"
                class="flex items-center gap-2 bg-klinik-blue text-white text-sm font-medium px-4 py-2 rounded-full hover:bg-klinik-blue-dark transition-colors whitespace-nowrap"
            >
                <x-icon name="document-search" class="w-4 h-4" /> Tambah Rekam Medis
            </button>
        </div>
    </div>

    {{-- ===================== TABEL REKAM MEDIS ===================== --}}
    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 text-left text-gray-400 text-xs uppercase tracking-wide">
                        <th class="px-5 py-3 font-medium">Tanggal</th>
                        <th class="px-5 py-3 font-medium">Pasien</th>
                        <th class="px-5 py-3 font-medium">Poli</th>
                        <th class="px-5 py-3 font-medium">Dokter</th>
                        <th class="px-5 py-3 font-medium">Diagnosis</th>
                        <th class="px-5 py-3 font-medium text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse ($rekamMedis as $r)
                        <tr class="hover:bg-gray-50/60 align-top" wire:key="rm-{{ $r->id }}">
                            <td class="px-5 py-3 text-gray-500 whitespace-nowrap">
                                {{ $r->tanggal_periksa->translatedFormat('d M Y') }}
                            </td>
                            <td class="px-5 py-3">
                                <p class="text-gray-700 font-medium">{{ $r->pasien->nama }}</p>
                                <p class="text-xs text-sky-500">{{ $r->pasien->no_rm }}</p>
                            </td>
                            <td class="px-5 py-3 text-gray-500 whitespace-nowrap">{{ $r->poli->nama ?? '-' }}</td>
                            <td class="px-5 py-3 text-gray-500 whitespace-nowrap">{{ $r->dokter ?: '-' }}</td>
                            <td class="px-5 py-3 text-gray-600">{{ \Illuminate\Support\Str::limit($r->diagnosis, 50) }}</td>
                            <td class="px-5 py-3">
                                <div class="flex items-center justify-end gap-3">
                                    <button wire:click="edit({{ $r->id }})" class="text-xs font-medium text-sky-500 hover:underline">
                                        Detail / Edit
                                    </button>
                                    <button
                                        wire:click="hapus({{ $r->id }})"
                                        wire:confirm="Hapus rekam medis {{ $r->pasien->nama }} tanggal {{ $r->tanggal_periksa->format('d M Y') }}?"
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
                                    Tidak ada rekam medis untuk pencarian "{{ $cari }}".
                                @else
                                    Belum ada data rekam medis. Klik "Tambah Rekam Medis" untuk mencatat pemeriksaan baru.
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($rekamMedis->hasPages())
            <div class="px-5 py-4 border-t border-gray-50">
                {{ $rekamMedis->links() }}
            </div>
        @endif
    </div>

    {{-- ===================== MODAL TAMBAH / EDIT ===================== --}}
    @if ($showModal)
        <div
            class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4 overflow-y-auto"
            wire:click.self="tutupForm"
        >
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-xl p-6 my-8">

                <div class="flex items-center justify-between mb-5">
                    <h3 class="font-bold text-gray-800">
                        {{ $editId ? 'Detail / Edit Rekam Medis' : 'Tambah Rekam Medis' }}
                    </h3>
                    <button wire:click="tutupForm" class="text-gray-400 hover:text-gray-600">
                        <x-icon name="cross" class="w-5 h-5 rotate-45" />
                    </button>
                </div>

                <form wire:submit="simpan" class="space-y-4">

                    {{-- Cari pasien --}}
                    <div class="relative">
                        <label class="text-xs font-medium text-gray-500">Pasien</label>
                        <input
                            type="text"
                            wire:model.live.debounce.300ms="cariPasien"
                            placeholder="Ketik nama atau No. RM..."
                            autocomplete="off"
                            {{ $editId ? 'disabled' : '' }}
                            class="mt-1 w-full bg-gray-100 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-klinik-blue/40 disabled:opacity-60"
                        >
                        @error('pasien_id') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror

                        @if (! $editId && ! $pasien_id && strlen($cariPasien) >= 2)
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

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-xs font-medium text-gray-500">Poli</label>
                            <select wire:model="poli_id" class="mt-1 w-full bg-gray-100 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-klinik-blue/40">
                                <option value="">-</option>
                                @foreach ($polis as $poli)
                                    <option value="{{ $poli->id }}">{{ $poli->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="text-xs font-medium text-gray-500">Tanggal Periksa</label>
                            <input type="date" wire:model="tanggal_periksa" class="mt-1 w-full bg-gray-100 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-klinik-blue/40">
                            @error('tanggal_periksa') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div>
                        <label class="text-xs font-medium text-gray-500">Dokter</label>
                        <input type="text" wire:model="dokter" placeholder="mis. dr. Hendra" class="mt-1 w-full bg-gray-100 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-klinik-blue/40">
                    </div>

                    <div>
                        <label class="text-xs font-medium text-gray-500">Keluhan</label>
                        <textarea wire:model="keluhan" rows="2" class="mt-1 w-full bg-gray-100 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-klinik-blue/40"></textarea>
                    </div>

                    <div>
                        <label class="text-xs font-medium text-gray-500">Diagnosis</label>
                        <textarea wire:model="diagnosis" rows="2" class="mt-1 w-full bg-gray-100 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-klinik-blue/40"></textarea>
                        @error('diagnosis') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="text-xs font-medium text-gray-500">Tindakan</label>
                        <textarea wire:model="tindakan" rows="2" class="mt-1 w-full bg-gray-100 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-klinik-blue/40"></textarea>
                    </div>

                    <div>
                        <label class="text-xs font-medium text-gray-500">Catatan Tambahan</label>
                        <textarea wire:model="catatan" rows="2" class="mt-1 w-full bg-gray-100 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-klinik-blue/40"></textarea>
                    </div>

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
</div>
