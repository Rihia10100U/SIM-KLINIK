<div wire:poll.5s class="min-h-screen bg-gradient-to-br from-sky-50 to-white flex flex-col">

    {{-- Header --}}
    <div class="bg-white border-b border-sky-100 px-8 py-5 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-klinik-green/10 flex items-center justify-center text-klinik-green">
                <x-icon name="cross" class="w-6 h-6" />
            </div>
            <div>
                <p class="font-bold text-gray-800 leading-none">SIM-KLINIK</p>
                <p class="text-xs text-gray-400">Papan Antrian Pemeriksaan</p>
            </div>
        </div>
        <div class="text-right">
            <p class="text-sm font-medium text-gray-700">{{ now()->locale('id')->translatedFormat('l, d F Y') }}</p>
            <p class="text-xs text-gray-400">{{ now()->format('H:i') }} — otomatis diperbarui tiap 5 detik</p>
        </div>
    </div>

    {{-- Konten utama --}}
    <div class="flex-1 flex flex-col p-8 gap-6">

        <div class="text-center">
            <h1 class="text-2xl font-bold text-gray-700 uppercase tracking-widest">Ruang Tunggu Pemeriksaan</h1>
            <p class="text-sm text-gray-400 mt-1">Silakan memasuki ruang poli saat nomor Anda dipanggil</p>
        </div>

        {{-- Panel nomor dipanggil --}}
        @if ($dipanggil->isEmpty())
            <div class="flex-1 flex items-center justify-center">
                <div class="text-center">
                    <p class="text-5xl font-extrabold text-sky-100 mb-3">—</p>
                    <p class="text-gray-400">Belum ada nomor yang dipanggil ke ruang periksa</p>
                </div>
            </div>
        @else
            {{-- Nomor dipanggil: tampil sebagai grid kartu besar --}}
            <div class="grid {{ $dipanggil->count() === 1 ? 'grid-cols-1 max-w-sm mx-auto w-full' : ($dipanggil->count() <= 2 ? 'grid-cols-2' : 'grid-cols-3') }} gap-5">
                @foreach ($dipanggil as $a)
                    <div
                        class="bg-sky-500 text-white rounded-3xl p-8 text-center shadow-xl shadow-sky-200 {{ $loop->first ? 'ring-4 ring-sky-300' : 'opacity-80' }}"
                        wire:key="poli-{{ $a->id }}"
                    >
                        @if ($loop->first)
                            <p class="text-xs font-semibold uppercase tracking-widest opacity-80 mb-2">Dipanggil Sekarang</p>
                        @endif
                        <p class="text-6xl font-extrabold tracking-tight">{{ $a->kode_antrian }}</p>
                        <p class="text-sm font-medium opacity-90 mt-3">{{ $a->poli->nama }}</p>
                    </div>
                @endforeach
            </div>
        @endif

        {{-- Status per poli --}}
        <div class="mt-auto">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-3">Status Antrian per Poli</p>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                @foreach ($polis as $p)
                    <div class="bg-white rounded-2xl border border-sky-100 p-4 text-center">
                        <div class="w-8 h-8 rounded-lg {{ $p->aktif ? 'bg-sky-500' : 'bg-gray-200' }} text-white flex items-center justify-center text-sm font-bold mx-auto mb-2">
                            {{ $p->kode }}
                        </div>
                        <p class="text-xs font-medium text-gray-600 mb-2">{{ $p->nama }}</p>
                        <div class="flex items-center justify-center gap-3 text-xs">
                            <span class="text-amber-500 font-semibold">{{ $p->menunggu_count }} menunggu</span>
                            <span class="text-gray-300">·</span>
                            <span class="text-green-500 font-semibold">{{ $p->selesai_count }} selesai</span>
                        </div>
                        @if (! $p->aktif)
                            <p class="text-[10px] text-red-400 mt-1">Tidak Aktif</p>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
