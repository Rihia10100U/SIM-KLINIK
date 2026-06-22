<div class="space-y-6">

    {{-- Notifikasi sukses --}}
    @if (session('sukses'))
        <div class="bg-blue-50 border border-blue-200 text-blue-700 text-sm rounded-xl px-4 py-3">
            {{ session('sukses') }}
        </div>
    @endif

    {{-- ===================== HEADER ===================== --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-gray-800">Rekam Medis</h2>
            <p class="text-sm text-gray-400 mt-1">Riwayat pemeriksaan, diagnosis & resep pasien</p>
        </div>

        <div class="flex items-center gap-3">
            <div class="relative">
                <x-icon name="search" class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" />
                <input
                    type="text"
                    wire:model.live.debounce.400ms="cari"
                    placeholder="Cari nama pasien atau No. RM..."
                    class="bg-gray-100 rounded-full pl-9 pr-4 py-2 text-sm w-64 focus:outline-none focus:ring-2 focus:ring-klinik-blue/40"
                >
            </div>

            <button
                wire:click="bukaFormManual"
                class="flex items-center gap-2 bg-gray-100 text-gray-600 text-sm font-medium px-4 py-2 rounded-full hover:bg-gray-200 transition-colors whitespace-nowrap"
            >
                <x-icon name="document-search" class="w-4 h-4" /> Catat Manual
            </button>
        </div>
    </div>

    {{-- ===================== SEDANG DIPERIKSA ===================== --}}
    <div class="card p-5">
        <h3 class="font-semibold text-gray-800 mb-4">Sedang Diperiksa</h3>

        <div class="space-y-3">
            @forelse ($antrianAktif as $a)
                <div class="flex items-center justify-between bg-gray-50 rounded-xl p-3" wire:key="aktif-{{ $a->id }}">
                    <div class="flex items-center gap-3">
                        <span class="text-sky-500 font-bold text-sm w-14">{{ $a->kode_antrian }}</span>
                        <div>
                            <p class="text-sm font-medium text-gray-700">{{ $a->pasien->nama }}</p>
                            <p class="text-xs text-gray-400">{{ $a->poli->nama }}</p>
                        </div>
                    </div>
                    <button
                        wire:click="bukaFormDariAntrian({{ $a->id }})"
                        class="text-xs font-medium text-white bg-klinik-blue px-4 py-1.5 rounded-full hover:bg-klinik-blue-dark"
                    >
                        Catat Hasil Pemeriksaan
                    </button>
                </div>
            @empty
                <p class="text-sm text-gray-400 text-center py-6">
                    Tidak ada pasien yang sedang diperiksa. Panggil antrian dulu di halaman Manajemen Antrian.
                </p>
            @endforelse
        </div>
    </div>

    {{-- ===================== TABEL RIWAYAT REKAM MEDIS ===================== --}}
    <div class="card overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-50">
            <h3 class="font-semibold text-gray-800">Riwayat Rekam Medis</h3>
        </div>
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
                                    Belum ada data rekam medis.
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

    {{-- ===================== MODAL CATAT / EDIT ===================== --}}
    @if ($showModal)
        <div class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4 overflow-y-auto" wire:click.self="tutupForm">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-2xl p-6 my-8">

                <div class="flex items-center justify-between mb-5">
                    <h3 class="font-bold text-gray-800">
                        {{ $antrianId ? 'Catat Hasil Pemeriksaan' : ($editId ? 'Detail / Edit Rekam Medis' : 'Catat Rekam Medis Manual') }}
                    </h3>
                    <button wire:click="tutupForm" class="text-gray-400 hover:text-gray-600">
                        <x-icon name="cross" class="w-5 h-5 rotate-45" />
                    </button>
                </div>

                <form wire:submit="simpan" class="space-y-4">

                    {{-- Pasien --}}
                    @if ($antrianId)
                        {{-- Dikunci, karena sudah ditentukan dari antrian yang dipilih --}}
                        <div class="bg-sky-50 rounded-lg px-3 py-2 text-sm text-sky-700">
                            Pasien: <strong>{{ $namaPasienTerpilih }}</strong>
                        </div>
                    @elseif ($editId)
                        <div class="bg-gray-50 rounded-lg px-3 py-2 text-sm text-gray-600">
                            Pasien: <strong>{{ $namaPasienTerpilih }}</strong>
                        </div>
                    @else
                        <div class="relative">
                            <label class="text-xs font-medium text-gray-500">Pasien</label>
                            <input
                                type="text"
                                wire:model.live.debounce.300ms="cariPasien"
                                placeholder="Ketik nama atau No. RM..."
                                autocomplete="off"
                                class="mt-1 w-full bg-gray-100 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-klinik-blue/40"
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
                    @endif

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-xs font-medium text-gray-500">Poli</label>
                            <select wire:model="poli_id" {{ $antrianId ? 'disabled' : '' }} class="mt-1 w-full bg-gray-100 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-klinik-blue/40 disabled:opacity-70">
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

                    {{-- ===== RESEP (hanya saat catat dari antrian aktif) ===== --}}
                    @if ($antrianId)
                        <div class="border-t border-gray-100 pt-4">
                            <label class="text-xs font-medium text-gray-500 block mb-2">Resep Obat (opsional)</label>

                            @if (count($resep) > 0)
                                <div class="space-y-2 mb-3">
                                    @foreach ($resep as $index => $item)
                                        <div class="flex items-center justify-between bg-gray-50 rounded-lg px-3 py-2" wire:key="resep-{{ $index }}">
                                            <div class="flex items-center gap-3 flex-1">
                                                <span class="text-sm text-gray-700">{{ $item['nama'] }}</span>
                                                <input
                                                    type="number"
                                                    min="1"
                                                    wire:model.live="resep.{{ $index }}.qty"
                                                    class="w-16 bg-white border border-gray-200 rounded px-2 py-1 text-sm text-center"
                                                >
                                                <span class="text-xs text-gray-400">
                                                    Rp {{ number_format($item['harga_satuan'], 0, ',', '.') }} / item
                                                </span>
                                            </div>
                                            <button type="button" wire:click="hapusResep({{ $index }})" class="text-red-400 hover:text-red-600">
                                                <x-icon name="cross" class="w-4 h-4 rotate-45" />
                                            </button>
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            <div class="relative">
                                <input
                                    type="text"
                                    wire:model.live.debounce.300ms="cariObat"
                                    placeholder="Cari & tambah obat ke resep..."
                                    autocomplete="off"
                                    class="w-full bg-gray-100 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-klinik-blue/40"
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

                            @if (count($resep) > 0)
                                <p class="text-xs text-gray-400 mt-2">
                                    Total resep: <strong>Rp {{ number_format($this->totalResep(), 0, ',', '.') }}</strong>
                                    — akan dikirim ke Apoteker untuk pembayaran setelah disimpan.
                                </p>
                            @endif
                        </div>
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
                            <span wire:loading.remove wire:target="simpan">
                                {{ $antrianId ? 'Simpan & Selesaikan Pemeriksaan' : 'Simpan' }}
                            </span>
                            <span wire:loading wire:target="simpan">Menyimpan...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
