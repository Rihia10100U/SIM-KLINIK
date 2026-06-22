<div class="space-y-6">

    {{-- ===================== HEADER + FILTER ===================== --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-gray-800">Cetak Antrian</h2>
            <p class="text-sm text-gray-400 mt-1">Cetak atau cetak ulang nomor antrian poli untuk pasien</p>
        </div>

        <div class="flex items-center gap-3">
            <select wire:model.live="filterPoli" class="bg-gray-100 rounded-full px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-klinik-blue/40">
                <option value="">Semua Poli</option>
                @foreach ($polis as $poli)
                    <option value="{{ $poli->id }}">{{ $poli->nama }}</option>
                @endforeach
            </select>

            <div class="relative">
                <x-icon name="search" class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" />
                <input
                    type="text"
                    wire:model.live.debounce.400ms="cari"
                    placeholder="Cari kode/nama pasien..."
                    class="bg-gray-100 rounded-full pl-9 pr-4 py-2 text-sm w-56 focus:outline-none focus:ring-2 focus:ring-klinik-blue/40"
                >
            </div>
        </div>
    </div>

    {{-- Notifikasi status cetak --}}
    @if ($pesanCetak)
        <div class="{{ str_contains($pesanCetak, 'berhasil') ? 'bg-green-50 border-green-200 text-green-700' : 'bg-amber-50 border-amber-200 text-amber-700' }} border text-sm rounded-xl px-4 py-3">
            {{ $pesanCetak }}
        </div>
    @endif

    @if (! $printerAktif)
        <div class="bg-sky-50 border border-sky-200 text-sky-700 text-sm rounded-xl px-4 py-3">
            Printer thermal belum diaktifkan (<code class="bg-white/60 px-1 rounded">PRINTER_CONNECTION=none</code> di .env) — semua tombol cetak di bawah akan memakai dialog print browser.
        </div>
    @endif

    {{-- ===================== TABEL ANTRIAN HARI INI ===================== --}}
    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 text-left text-gray-400 text-xs uppercase tracking-wide">
                        <th class="px-5 py-3 font-medium">Jam</th>
                        <th class="px-5 py-3 font-medium">Kode</th>
                        <th class="px-5 py-3 font-medium">Pasien</th>
                        <th class="px-5 py-3 font-medium">Poli</th>
                        <th class="px-5 py-3 font-medium">Status</th>
                        <th class="px-5 py-3 font-medium text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse ($antrians as $a)
                        @php
                            $badgeStatus = match ($a->status) {
                                'menunggu'  => 'bg-amber-100 text-amber-600',
                                'dipanggil' => 'bg-sky-100 text-sky-600',
                                'selesai'   => 'bg-green-100 text-green-600',
                                default     => 'bg-gray-100 text-gray-500',
                            };
                        @endphp
                        <tr class="hover:bg-gray-50/60" wire:key="cetak-{{ $a->id }}">
                            <td class="px-5 py-3 text-gray-500 whitespace-nowrap">{{ $a->created_at->format('H:i') }}</td>
                            <td class="px-5 py-3 font-bold text-sky-500 whitespace-nowrap">{{ $a->kode_antrian }}</td>
                            <td class="px-5 py-3 text-gray-700">{{ $a->pasien->nama }}</td>
                            <td class="px-5 py-3 text-gray-500 whitespace-nowrap">{{ $a->poli->nama }}</td>
                            <td class="px-5 py-3">
                                <span class="badge {{ $badgeStatus }}">{{ ucfirst($a->status) }}</span>
                            </td>
                            <td class="px-5 py-3">
                                <div class="flex items-center justify-end gap-3">
                                    @if ($printerAktif)
                                        <button
                                            wire:click="cetak({{ $a->id }})"
                                            wire:loading.attr="disabled"
                                            wire:target="cetak({{ $a->id }})"
                                            class="text-xs font-medium text-white bg-klinik-green px-3 py-1.5 rounded-full hover:bg-klinik-green-dark disabled:opacity-60"
                                        >
                                            <span wire:loading.remove wire:target="cetak({{ $a->id }})">Cetak</span>
                                            <span wire:loading wire:target="cetak({{ $a->id }})">...</span>
                                        </button>
                                    @endif
                                    <button wire:click="cetakBrowser({{ $a->id }})" class="text-xs font-medium text-sky-500 hover:underline">
                                        Cetak via Browser
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-10 text-center text-gray-400 text-sm">
                                @if ($cari || $filterPoli)
                                    Tidak ada antrian yang cocok dengan filter saat ini.
                                @else
                                    Belum ada antrian poli hari ini.
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ===================== MARKUP KHUSUS CETAK (THERMAL 58mm) ===================== --}}
    @if ($tiketUntukCetak)
        <div class="print-ticket">
            <div style="font-family: monospace; text-align: center; padding: 4px;">
                <p style="font-size: 12px; font-weight: bold; margin: 0;">SIM-KLINIK</p>
                <p style="font-size: 10px; margin: 2px 0;">Nomor Antrian Poli</p>
                <p style="font-size: 28px; font-weight: bold; margin: 6px 0;">{{ $tiketUntukCetak->kode_antrian }}</p>
                <p style="font-size: 11px; margin: 2px 0;">{{ $tiketUntukCetak->poli->nama }}</p>
                <p style="font-size: 10px; margin: 2px 0;">{{ $tiketUntukCetak->pasien->nama }}</p>
                <p style="font-size: 9px; margin: 4px 0;">{{ $tiketUntukCetak->created_at->format('d-m-Y H:i') }}</p>
            </div>
        </div>
    @endif

    @script
    <script>
        $wire.on('cetak-browser', () => {
            setTimeout(() => window.print(), 200);
        });
    </script>
    @endscript
</div>
