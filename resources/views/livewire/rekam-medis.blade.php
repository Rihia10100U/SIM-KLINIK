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
            <h2 class="text-xl font-bold text-gray-800">Rekam Medis</h2>
            <p class="text-sm text-gray-400 mt-1">Riwayat pemeriksaan, diagnosis & tagihan pasien</p>
        </div>
        <div class="flex items-center gap-3">
            <div class="relative">
                <x-icon name="search" class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" />
                <input
                    type="text"
                    wire:model.live.debounce.400ms="cari"
                    placeholder="Cari nama atau No. RM..."
                    class="bg-gray-100 rounded-full pl-9 pr-4 py-2 text-sm w-64 focus:outline-none focus:ring-2 focus:ring-klinik-blue-dark/40"
                >
            </div>
            <button
                wire:click="bukaFormManual"
                class="flex items-center gap-2 bg-klinik-blue-dark text-white text-sm font-medium px-4 py-2 rounded-full hover:bg-blue-800 transition-colors whitespace-nowrap"
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
                        <span class="text-klinik-blue-dark font-bold text-sm w-14">{{ $a->kode_antrian }}</span>
                        <div>
                            <p class="text-sm font-medium text-gray-700">{{ $a->pasien->nama }}</p>
                            <p class="text-xs text-gray-400">{{ $a->poli->nama }}</p>
                        </div>
                    </div>
                    <button
                        wire:click="bukaFormDariAntrian({{ $a->id }})"
                        class="text-xs font-medium text-white bg-klinik-blue-dark px-4 py-1.5 rounded-full hover:bg-klinik-blue-800 transition-colors whitespace-nowrap"
                    >
                        Catat Hasil Pemeriksaan
                    </button>
                </div>
            @empty
                <p class="text-sm text-gray-400 text-center py-6">Tidak ada pasien yang sedang diperiksa.</p>
            @endforelse
        </div>
    </div>

    {{-- ===================== TABEL RIWAYAT ===================== --}}
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
                            <td class="px-5 py-3 text-gray-500 whitespace-nowrap">{{ $r->tanggal_periksa->translatedFormat('d M Y') }}</td>
                            <td class="px-5 py-3">
                                <p class="text-gray-700 font-medium">{{ $r->pasien->nama }}</p>
                                <p class="text-xs text-klinik-blue-dark">{{ $r->pasien->no_rm }}</p>
                            </td>
                            <td class="px-5 py-3 text-gray-500 whitespace-nowrap">{{ $r->poli->nama ?? '-' }}</td>
                            <td class="px-5 py-3 text-gray-500 whitespace-nowrap">{{ $r->dokter ?: '-' }}</td>
                            <td class="px-5 py-3 text-gray-600">{{ \Illuminate\Support\Str::limit($r->diagnosis, 50) }}</td>
                            <td class="px-5 py-3">
                                <div class="flex items-center justify-end gap-3">
                                    <button wire:click="edit({{ $r->id }})" class="text-xs font-medium text-klinik-blue-dark hover:underline">
                                        Detail / Edit
                                    </button>
                                    <button
                                        wire:click="hapus({{ $r->id }})"
                                        wire:confirm="Hapus rekam medis ini?"
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
            <div class="px-5 py-4 border-t border-gray-50">{{ $rekamMedis->links() }}</div>
        @endif
    </div>

    {{-- ===================== MODAL CATAT / EDIT ===================== --}}
    @if ($showModal)
        <div class="modal-overlay fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4 overflow-y-auto" wire:click.self="tutupForm">
            <div class="modal-content bg-white rounded-2xl shadow-xl w-full max-w-4xl p-6 m-auto">

                {{-- Header modal --}}
                <div class="flex items-center justify-between mb-5">
                    <div>
                        <h3 class="font-bold text-gray-800">
                            {{ $antrianId ? 'Catat Hasil Pemeriksaan' : ($editId ? 'Detail / Edit Rekam Medis' : 'Catat Rekam Medis Manual') }}
                        </h3>
                        @if ($antrianId)
                            <p class="text-xs text-klinik-blue-dark mt-0.5">Tarif dan resep akan dikirim ke Farmasi & Pembayaran</p>
                        @endif
                    </div>
                    <button wire:click="tutupForm" class="text-gray-400 hover:text-gray-600">
                        <x-icon name="cross" class="w-5 h-5 rotate-45" />
                    </button>
                </div>

                <form wire:submit="simpan" class="space-y-5">

                    {{-- Pasien --}}
                    @if ($antrianId || $editId)
                        <div class="bg-sky-50 rounded-lg px-3 py-2 text-sm text-klinik-blue-dark flex items-center justify-between">
                            <span>Pasien: <strong>{{ $namaPasienTerpilih }}</strong></span>
                            @if ($bpjs)
                                <span class="text-[10px] font-semibold bg-blue-600 text-white px-2 py-0.5 rounded-full">BPJS</span>
                            @endif
                        </div>
                    @else
                        <div class="relative">
                            <label class="text-xs font-medium text-gray-500">Pasien</label>
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
                                        <button type="button" wire:click="pilihPasien({{ $opt->id }})"
                                            class="w-full text-left px-3 py-2 text-sm hover:bg-gray-50 flex items-center justify-between">
                                            <span class="text-gray-700">{{ $opt->nama }}</span>
                                            <span class="text-xs text-gray-400">{{ $opt->no_rm }}</span>
                                        </button>
                                    @empty
                                        <p class="px-3 py-2 text-sm text-gray-400">Pasien tidak ditemukan.</p>
                                    @endforelse
                                </div>
                            @endif
                        </div>
                        {{-- BPJS toggle untuk catat manual --}}
                        <div>
                            <label class="text-xs font-medium text-gray-500 block mb-2">Jenis Pasien</label>
                            <div class="flex items-center gap-6">
                                <label class="flex items-center gap-2 cursor-pointer group">
                                    <input type="radio" name="bpjs_rm" wire:click="$set('bpjs', false)" {{ !$bpjs ? 'checked' : '' }} class="w-4 h-4 text-klinik-blue-dark border-gray-300 focus:ring-klinik-blue-dark focus:ring-2 cursor-pointer">
                                    <span class="text-sm font-medium text-gray-700 group-hover:text-gray-900 transition-colors">Tidak BPJS</span>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer group">
                                    <input type="radio" name="bpjs_rm" wire:click="$set('bpjs', true)" {{ $bpjs ? 'checked' : '' }} class="w-4 h-4 text-klinik-blue-dark border-gray-300 focus:ring-klinik-blue-dark focus:ring-2 cursor-pointer">
                                    <span class="text-sm font-medium text-gray-700 group-hover:text-gray-900 transition-colors">Pasien BPJS</span>
                                </label>
                            </div>
                        </div>
                    @endif

                    {{-- Poli + Tanggal --}}
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-xs font-medium text-gray-500">Poli</label>
                            <select wire:model.live="poli_id" {{ $antrianId ? 'disabled' : '' }}
                                class="mt-1 w-full bg-gray-100 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-klinik-blue-dark/40 disabled:opacity-70">
                                <option value="">-</option>
                                @foreach ($polis as $poli)
                                    <option value="{{ $poli->id }}">{{ $poli->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="text-xs font-medium text-gray-500">Tanggal Periksa</label>
                            <input type="date" wire:model="tanggal_periksa"
                                class="mt-1 w-full bg-gray-100 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-klinik-blue-dark/40">
                            @error('tanggal_periksa') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    {{-- Dokter --}}
                    <div>
                        <label class="text-xs font-medium text-gray-500">Dokter</label>
                        <input type="text" wire:model="dokter" placeholder="mis. dr. Hendra"
                            class="mt-1 w-full bg-gray-100 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-klinik-blue-dark/40">
                    </div>

                    {{-- Keluhan --}}
                    <div>
                        <label class="text-xs font-medium text-gray-500">Keluhan</label>
                        <textarea wire:model="keluhan" rows="2"
                            class="mt-1 w-full bg-gray-100 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-klinik-blue-dark/40"></textarea>
                    </div>

                    {{-- Diagnosis + Tindakan --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="text-xs font-medium text-gray-500">Diagnosis</label>
                            <textarea wire:model="diagnosis" rows="3"
                                class="mt-1 w-full bg-gray-100 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-klinik-blue-dark/40"></textarea>
                            @error('diagnosis') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="text-xs font-medium text-gray-500">Tindakan</label>
                            <textarea wire:model="tindakan" rows="3"
                                class="mt-1 w-full bg-gray-100 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-klinik-blue-dark/40"></textarea>
                        </div>
                    </div>

                    <div>
                        <label class="text-xs font-medium text-gray-500">Catatan Tambahan</label>
                        <textarea wire:model="catatan" rows="2"
                            class="mt-1 w-full bg-gray-100 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-klinik-blue-dark/40"></textarea>
                    </div>

                    {{-- ========== TAGIHAN (dari antrian aktif / catat manual) ========== --}}
                    @if (! $editId)
                        <div class="border-t border-gray-100 pt-4 space-y-4">

                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Tagihan Pemeriksaan</p>

                            {{-- BAGIAN A: Layanan & Tindakan --}}
                            <div>
                                <p class="text-xs font-medium text-gray-500 mb-2">Pilihan Tindakan / Konsultasi <span class="font-normal italic">(Sesuai Poli Aktif)</span></p>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mt-2">
                                    {{-- Gunakan $layanansAktif, bukan $daftarLayanan --}}
                                    @forelse ($layanansAktif as $layanan)
                                        <label class="flex items-start gap-3 border rounded-lg px-3 py-2 cursor-pointer transition-colors {{ $selectedLayananId == $layanan->id ? 'bg-sky-50 border-klinik-blue-dark' : 'bg-white border-gray-200 hover:bg-gray-50' }}">
                                            
                                            <div class="flex items-center h-5 mt-0.5">
                                                <input type="radio" 
                                                    wire:model.live="selectedLayananId" 
                                                    value="{{ $layanan->id }}" 
                                                    class="w-4 h-4 text-klinik-blue-dark border-gray-300 focus:ring-klinik-blue-dark cursor-pointer">
                                            </div>
                                            
                                            <div class="flex-1 min-w-0">
                                                <p class="text-sm font-medium {{ $selectedLayananId == $layanan->id ? 'text-klinik-blue-dark' : 'text-gray-700' }}">
                                                    {{ $layanan->nama }}
                                                </p>
                                                <p class="text-xs {{ $selectedLayananId == $layanan->id ? 'text-klinik-blue-dark' : 'text-gray-400' }} capitalize">
                                                    {{ $layanan->kategori }} • Rp {{ number_format($layanan->harga, 0, ',', '.') }}
                                                </p>
                                            </div>
                                        </label>
                                    @empty
                                        <div class="col-span-full border border-gray-100 rounded-xl p-4 bg-gray-50/50 text-center">
                                            <p class="text-xs text-gray-500">
                                                @if($poli_id) 
                                                    Belum ada data layanan aktif untuk poli ini. 
                                                @else 
                                                    Pilih <strong>Poli</strong> terlebih dahulu untuk melihat daftar layanan. 
                                                @endif
                                            </p>
                                        </div>
                                    @endforelse
                                </div>
                            </div>


                        </div>

                        {{-- BAGIAN B: Obat Resep --}}
                        <div>
                            <p class="text-xs font-medium text-gray-500 mb-2">Resep Obat</p>

                            @if (count($resep) > 0)
                                <div class="space-y-3 mb-2">
                                    @foreach ($resep as $idx => $item)
                                        <div class="bg-green-50 rounded-lg px-3 py-2" wire:key="resep-{{ $idx }}">
                                            <div class="flex items-center gap-2">
                                                <span class="flex-1 text-sm text-gray-700 truncate">{{ $item['nama'] }}</span>
                                                <input type="number" min="1"
                                                    wire:model.live="resep.{{ $idx }}.qty"
                                                    class="w-14 bg-white border border-green-200 rounded px-2 py-1 text-sm text-center">
                                                <button type="button" wire:click="hapusResep({{ $idx }})" class="text-red-400 hover:text-red-600 shrink-0 ml-2">
                                                    <x-icon name="cross" class="w-4 h-4 rotate-45" />
                                                </button>
                                            </div>
                                            <input type="text"
                                                wire:model="resep.{{ $idx }}.catatan"
                                                placeholder="Aturan pakai (mis. 3x sehari)..."
                                                class="mt-1.5 w-full bg-white border border-green-200 rounded px-2 py-1 text-xs text-gray-600 placeholder-gray-400 focus:outline-none focus:ring-1 focus:ring-klinik-green/40">
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            {{-- Cari & tambah obat --}}
                            <div class="relative">
                                <input type="text"
                                    wire:model.live.debounce.300ms="cariObat"
                                    placeholder="+ Cari & tambah obat..."
                                    autocomplete="off"
                                    class="w-full bg-gray-100 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-klinik-blue-dark/40">
                                
                                @if (strlen($cariObat) >= 2)
                                    <div class="absolute z-10 mt-1 w-full bg-white border border-gray-100 rounded-lg shadow-lg max-h-48 overflow-y-auto">
                                        @forelse ($this->obatOptions() as $opt)
                                            <button type="button" wire:click="tambahObat({{ $opt->id }})"
                                                class="w-full text-left px-3 py-2 text-sm hover:bg-gray-50 flex items-center justify-between">
                                                <span class="text-gray-700">{{ $opt->nama }}</span>
                                            </button>
                                        @empty
                                            <p class="px-3 py-2 text-sm text-gray-400">Obat tidak ditemukan.</p>
                                        @endforelse
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- Ringkasan total tagihan --}}
                        <div class="bg-gray-50 rounded-xl px-4 py-3 border border-gray-100">
                            <p class="text-sm font-medium text-gray-700 mb-1">Status Penagihan</p>
                            <p class="text-xs text-gray-500">Rincian harga, total tagihan, dan resep akan dikirim otomatis ke <strong>Farmasi & Pembayaran</strong> untuk diproses lebih lanjut oleh apoteker/kasir.</p>
                        </div>
                    @endif
                    
                    {{-- Tombol aksi --}}
                    <div class="flex items-center justify-end gap-3 pt-2">
                        <button type="button" wire:click="tutupForm"
                            class="text-sm font-medium text-gray-500 px-4 py-2 rounded-full hover:bg-gray-100">
                            Batal
                        </button>
                        <button type="submit"
                            wire:loading.attr="disabled" wire:target="simpan"
                            class="text-sm font-medium text-white bg-klinik-blue-dark px-5 py-2 rounded-full hover:bg-klinik-blue-800 transition-colors whitespace-nowrap">
                            <span wire:loading.remove wire:target="simpan">
                                {{ $antrianId || $selectedLayananId || count($resep) > 0 ? 'Simpan & Kirim ke Farmasi' : 'Simpan' }}
                            </span>
                            <span wire:loading wire:target="simpan">Menyimpan...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>