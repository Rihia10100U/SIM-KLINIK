<div class="space-y-6">

    {{-- ===================== HEADER + FILTER ===================== --}}
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-gray-800">Laporan</h2>
            <p class="text-sm text-gray-400 mt-1">Rekap kunjungan & pendapatan klinik</p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <button wire:click="setRentangCepat('hari-ini')" class="text-xs font-medium text-gray-500 bg-gray-100 px-3 py-1.5 rounded-full hover:bg-gray-200">
                Hari Ini
            </button>
            <button wire:click="setRentangCepat('minggu-ini')" class="text-xs font-medium text-gray-500 bg-gray-100 px-3 py-1.5 rounded-full hover:bg-gray-200">
                Minggu Ini
            </button>
            <button wire:click="setRentangCepat('bulan-ini')" class="text-xs font-medium text-gray-500 bg-gray-100 px-3 py-1.5 rounded-full hover:bg-gray-200">
                Bulan Ini
            </button>

            <input type="date" wire:model.live="dari" class="bg-gray-100 rounded-full px-3 py-1.5 text-xs focus:outline-none focus:ring-2 focus:ring-klinik-blue/40">
            <span class="text-gray-400 text-xs">s/d</span>
            <input type="date" wire:model.live="sampai" class="bg-gray-100 rounded-full px-3 py-1.5 text-xs focus:outline-none focus:ring-2 focus:ring-klinik-blue/40">

            <button
                wire:click="exportCsv"
                class="flex items-center gap-1.5 text-xs font-medium text-white bg-klinik-blue px-3 py-1.5 rounded-full hover:bg-klinik-blue-dark whitespace-nowrap"
            >
                <x-icon name="download" class="w-3.5 h-3.5" /> Export CSV
            </button>
        </div>
    </div>

    {{-- ===================== RINGKASAN ===================== --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-5">
        <div class="card p-5">
            <div class="w-10 h-10 rounded-xl bg-sky-100 flex items-center justify-center text-sky-500 mb-3">
                <x-icon name="users" class="w-5 h-5" />
            </div>
            <p class="text-xl font-bold text-gray-800">{{ $ringkasan['total_kunjungan'] }}</p>
            <p class="text-xs text-gray-400">Total Kunjungan</p>
        </div>

        <div class="card p-5">
            <div class="w-10 h-10 rounded-xl bg-sky-100 flex items-center justify-center text-sky-500 mb-3">
                <x-icon name="user-plus" class="w-5 h-5" />
            </div>
            <p class="text-xl font-bold text-gray-800">{{ $ringkasan['pasien_baru'] }}</p>
            <p class="text-xs text-gray-400">Pasien Baru</p>
        </div>

        <div class="card p-5">
            <div class="w-10 h-10 rounded-xl bg-sky-100 flex items-center justify-center text-sky-500 mb-3">
                <x-icon name="wallet" class="w-5 h-5" />
            </div>
            <p class="text-xl font-bold text-gray-800">Rp {{ number_format($ringkasan['total_pendapatan'], 0, ',', '.') }}</p>
            <p class="text-xs text-gray-400">Total Pendapatan</p>
        </div>

        <div class="card p-5">
            <div class="w-10 h-10 rounded-xl bg-sky-100 flex items-center justify-center text-sky-500 mb-3">
                <x-icon name="credit-card" class="w-5 h-5" />
            </div>
            @php
                $rataRata = $ringkasan['jumlah_transaksi'] > 0
                    ? intdiv($ringkasan['total_pendapatan'], $ringkasan['jumlah_transaksi'])
                    : 0;
            @endphp
            <p class="text-xl font-bold text-gray-800">Rp {{ number_format($rataRata, 0, ',', '.') }}</p>
            <p class="text-xs text-gray-400">Rata-rata per Transaksi</p>
        </div>
    </div>

    {{-- ===================== GRAFIK KUNJUNGAN HARIAN ===================== --}}
    <div class="card p-5">
        <h3 class="font-semibold text-gray-800 mb-6">Kunjungan Harian</h3>

        @php $maxKunjungan = max(1, collect($kunjunganHarian)->max('jumlah')); @endphp

        <div class="overflow-x-auto">
            <div class="flex items-end gap-3 h-48 min-w-max px-1">
                @foreach ($kunjunganHarian as $h)
                    <div class="flex flex-col items-center justify-end h-full w-10 shrink-0">
                        <span class="text-xs font-semibold text-gray-600 mb-1">{{ $h['jumlah'] }}</span>
                        <div
                            class="w-7 rounded-t-md {{ $h['jumlah'] === $maxKunjungan ? 'bg-sky-500' : 'bg-sky-100' }}"
                            style="height: {{ $h['jumlah'] / $maxKunjungan * 100 }}%"
                        ></div>
                        <span class="text-[10px] text-gray-400 mt-2 whitespace-nowrap">{{ $h['label'] }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        @if (count($kunjunganHarian) > 31)
            <p class="text-xs text-gray-400 mt-3">
                Tips: rentang yang dipilih cukup panjang ({{ count($kunjunganHarian) }} hari) — grafik bisa digeser ke samping untuk melihat semua data.
            </p>
        @endif
    </div>

    {{-- ===================== PENDAPATAN PER POLI & OBAT TERLARIS ===================== --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

        <div class="card overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-50">
                <h3 class="font-semibold text-gray-800">Pendapatan per Poli</h3>
            </div>
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 text-left text-gray-400 text-xs uppercase tracking-wide">
                        <th class="px-5 py-3 font-medium">Poli</th>
                        <th class="px-5 py-3 font-medium">Transaksi</th>
                        <th class="px-5 py-3 font-medium text-right">Pendapatan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse ($pendapatanPerPoli as $p)
                        <tr>
                            <td class="px-5 py-3 text-gray-700">{{ $p['nama'] }}</td>
                            <td class="px-5 py-3 text-gray-500">{{ $p['jumlah'] }}</td>
                            <td class="px-5 py-3 text-right font-medium text-gray-700 whitespace-nowrap">
                                Rp {{ number_format($p['pendapatan'], 0, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-5 py-8 text-center text-gray-400 text-sm">
                                Belum ada transaksi pada rentang ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="card overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-50">
                <h3 class="font-semibold text-gray-800">Obat Terlaris</h3>
            </div>
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 text-left text-gray-400 text-xs uppercase tracking-wide">
                        <th class="px-5 py-3 font-medium">Nama Obat</th>
                        <th class="px-5 py-3 font-medium">Terjual</th>
                        <th class="px-5 py-3 font-medium text-right">Pendapatan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse ($obatTerlaris as $o)
                        <tr>
                            <td class="px-5 py-3 text-gray-700">{{ $o->nama_item }}</td>
                            <td class="px-5 py-3 text-gray-500">{{ $o->total_qty }}</td>
                            <td class="px-5 py-3 text-right font-medium text-gray-700 whitespace-nowrap">
                                Rp {{ number_format($o->total_pendapatan, 0, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-5 py-8 text-center text-gray-400 text-sm">
                                Belum ada obat terjual pada rentang ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
