<div wire:poll.5s class="min-h-screen bg-gradient-to-br from-amber-50 to-white flex flex-col">

    {{-- Header --}}
    <div class="bg-white border-b border-amber-100 px-8 py-5 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-klinik-green/10 flex items-center justify-center text-klinik-green">
                <x-icon name="cross" class="w-6 h-6" />
            </div>
            <div>
                <p class="font-bold text-gray-800 leading-none">SIM-KLINIK</p>
                <p class="text-xs text-gray-400">Papan Antrian Registrasi</p>
            </div>
        </div>
        <div class="text-right">
            <p class="text-sm font-medium text-gray-700">{{ now()->locale('id')->translatedFormat('l, d F Y') }}</p>
            <p class="text-xs text-gray-400">{{ now()->format('H:i') }} — otomatis diperbarui tiap 5 detik</p>
        </div>
    </div>

    {{-- Konten utama --}}
    <div class="flex-1 flex flex-col p-8 gap-6">

        {{-- Judul loket --}}
        <div class="text-center">
            <h1 class="text-2xl font-bold text-gray-700 uppercase tracking-widest">Loket Pendaftaran</h1>
            <p class="text-sm text-gray-400 mt-1">Silakan menuju loket saat nomor Anda dipanggil</p>
        </div>

        {{-- Panel nomor dipanggil --}}
        @if ($dipanggil->isEmpty())
            <div class="flex-1 flex items-center justify-center">
                <div class="text-center">
                    <p class="text-5xl font-extrabold text-amber-200 mb-3">—</p>
                    <p class="text-gray-400">Belum ada nomor yang dipanggil</p>
                </div>
            </div>
        @else
            {{-- Nomor paling baru (tampil besar di tengah) --}}
            @php $terbaru = $dipanggil->first(); @endphp
            <div class="flex justify-center">
                <div class="bg-amber-500 text-white rounded-3xl px-16 py-10 text-center shadow-xl shadow-amber-200">
                    <p class="text-sm font-semibold uppercase tracking-widest opacity-80 mb-2">Dipanggil Sekarang</p>
                    <p class="text-7xl font-extrabold tracking-tight">{{ $terbaru->kode_antrian }}</p>
                    <p class="text-sm opacity-80 mt-3">Silakan menuju Loket Pendaftaran</p>
                </div>
            </div>

            {{-- Nomor sebelumnya (tampil kecil di bawah) --}}
            @if ($dipanggil->count() > 1)
                <div>
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide text-center mb-3">Sebelumnya</p>
                    <div class="flex flex-wrap justify-center gap-3">
                        @foreach ($dipanggil->skip(1) as $a)
                            <div class="bg-amber-100 text-amber-700 rounded-2xl px-6 py-3 text-center opacity-60"
                                wire:key="reg-prev-{{ $a->id }}">
                                <p class="text-2xl font-bold">{{ $a->kode_antrian }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        @endif

        {{-- Statistik bawah --}}
        <div class="grid grid-cols-2 gap-4 mt-auto">
            <div class="bg-white rounded-2xl border border-amber-100 p-5 text-center">
                <p class="text-3xl font-bold text-amber-500">{{ $jumlahMenunggu }}</p>
                <p class="text-xs text-gray-400 mt-1">Masih Menunggu</p>
            </div>
            <div class="bg-white rounded-2xl border border-amber-100 p-5 text-center">
                <p class="text-3xl font-bold text-gray-600">{{ $totalHariIni }}</p>
                <p class="text-xs text-gray-400 mt-1">Total Antrian Hari Ini</p>
            </div>
        </div>
    </div>
</div>
