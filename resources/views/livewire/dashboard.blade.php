<div class="space-y-6" wire:poll.5s>

    {{-- ===================== SAPAAN ===================== --}}
<div>
        <h2 class="text-xl font-bold text-gray-800">Selamat Pagi, {{ $namaUser }}</h2>
        <p class="text-gray-400 text-sm mt-1">{{ now()->locale('id')->translatedFormat('l, d F Y') }}</p>
    </div>

    {{-- ===================== STAT CARDS ===================== --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">

        <div class="card p-5 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-sky-100 flex items-center justify-center text-sky-500 shrink-0">
                <x-icon name="users" class="w-6 h-6" />
            </div>
            <div>
                <p class="text-2xl font-bold text-gray-800">{{ $statistik['total_antrian'] }}</p>
                <p class="text-sm text-gray-400">Total antrian hari ini</p>
            </div>
        </div>

        <div class="card p-5 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-sky-100 flex items-center justify-center text-sky-500 shrink-0">
                <x-icon name="users" class="w-6 h-6" />
            </div>
            <div>
                <p class="text-2xl font-bold text-gray-800">{{ $statistik['pasien_dalam_antrian'] }}</p>
                <p class="text-sm text-gray-400">Pasien dalam antrian</p>
            </div>
        </div>

        <div class="card p-5 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-sky-100 flex items-center justify-center text-sky-500 shrink-0">
                <x-icon name="wallet" class="w-6 h-6" />
            </div>
            <div>
                <p class="text-2xl font-bold text-gray-800">
                    Rp {{ number_format($statistik['pendapatan_hari_ini'] / 1000000, 1, ',', '.') }} Jt
                </p>
                <p class="text-sm text-gray-400">Pendapatan hari ini</p>
            </div>
        </div>
    </div>

    {{-- ===================== GRAFIK + ANTRIAN AKTIF ===================== --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

        {{-- Grafik kunjungan (CSS bar chart, tanpa library JS) --}}
        <div class="card p-5 lg:col-span-2 ring-2 ring-sky-100">
            <div class="flex items-center justify-between mb-6">
                <h3 class="font-semibold text-gray-800">Kunjungan Pasien - Minggu Ini</h3>
                <span class="text-xs bg-sky-50 text-sky-500 px-3 py-1 rounded-full font-medium">
                    {{ now()->locale('id')->translatedFormat('M Y') }}
                </span>
            </div>

            @php $max = max(array_column($kunjunganMingguan, 'jumlah')) ?: 1; @endphp

            <div class="flex items-end justify-between gap-3 h-48">
                @foreach ($kunjunganMingguan as $item)
                    <div class="flex flex-col items-center flex-1 h-full justify-end">
                        <span class="text-xs font-semibold text-gray-600 mb-1">{{ $item['jumlah'] }}</span>
                        <div
                            class="w-8 sm:w-10 rounded-t-md transition-all {{ $item['jumlah'] === $max ? 'bg-sky-500' : 'bg-sky-100' }}"
                            style="height: {{ $item['jumlah'] / $max * 100 }}%"
                        ></div>
                        <span class="text-xs text-gray-400 mt-2">{{ $item['hari'] }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Antrian aktif --}}
        <div class="card p-5">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-gray-800">Antrian Aktif</h3>
                <span class="flex items-center gap-1 text-xs text-green-500 font-medium">
                    <span class="w-2 h-2 rounded-full bg-green-500"></span> Live
                </span>
            </div>

            <div class="space-y-3">
                @foreach ($antrianAktif as $a)
                    <div class="flex items-center justify-between bg-gray-50 rounded-xl p-3">
                        <div class="flex items-center gap-3">
                            <span class="text-sky-500 font-bold text-sm w-12">{{ $a['kode'] }}</span>
                            <div>
                                <p class="text-sm font-medium text-gray-700">{{ $a['nama'] }}</p>
                                <p class="text-xs text-gray-400">{{ $a['poli'] }}</p>
                            </div>
                        </div>
                        <span class="badge bg-amber-100 text-amber-600">{{ $a['status'] }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- ===================== AKTIVITAS + PELAYANAN ===================== --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

        {{-- Aktivitas terbaru --}}
        <div class="card p-5 lg:col-span-2">
            <h3 class="font-semibold text-gray-800 mb-4">Aktivitas Terbaru</h3>

            <div class="space-y-4">
                @foreach ($aktivitasTerbaru as $item)
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex items-start gap-3">
                            <span class="w-2 h-2 rounded-full {{ $item['warna'] }} mt-1.5 shrink-0"></span>
                            <p class="text-sm text-gray-600">{!! $item['pesan'] !!}</p>
                        </div>
                        <span class="text-xs text-gray-400 whitespace-nowrap">{{ $item['waktu'] }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Pelayanan yang aktif --}}
        <div class="card p-5">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-gray-800">Pelayanan yang Aktif</h3>
                <span class="flex items-center gap-1 text-xs text-green-500 font-medium">
                    <span class="w-2 h-2 rounded-full bg-green-500"></span> Live
                </span>
            </div>

            <div class="space-y-3">
                @foreach ($pelayananAktif as $p)
                    <div class="flex items-center justify-between bg-gray-50 rounded-xl p-3">
                        <div class="flex items-center gap-3">
                            <span class="w-7 h-7 rounded-lg flex items-center justify-center text-xs font-bold
                                {{ $p['aktif'] ? 'bg-green-100 text-green-600' : 'bg-red-100 text-red-500' }}">
                                {{ $p['kode'] }}
                            </span>
                            <span class="text-sm font-medium {{ $p['aktif'] ? 'text-gray-700' : 'text-red-500' }}">
                                {{ $p['nama'] }}
                            </span>
                        </div>
                        <span class="badge {{ $p['aktif'] ? 'bg-green-100 text-green-600' : 'bg-red-100 text-red-500' }}">
                            {{ $p['aktif'] ? 'Aktif' : 'Tidak Aktif' }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
