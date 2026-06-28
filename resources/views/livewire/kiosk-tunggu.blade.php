<div wire:poll.5s class="max-w-5xl mx-auto space-y-8">

    <div class="text-center">
        <h1 class="text-2xl font-bold text-gray-800">Papan Antrian</h1>
        <p class="text-sm text-gray-400 mt-1">{{ now()->locale('id')->translatedFormat('l, d F Y - H:i') }}</p>
    </div>

    {{-- ===================== PANGGILAN PENDAFTARAN ===================== --}}
    <div class="card p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-sm font-semibold text-gray-400 uppercase tracking-wide">Dipanggil ke Loket Pendaftaran</h2>
            <span class="text-xs text-gray-400">{{ $pendaftaranMenunggu }} menunggu nomor</span>
        </div>

        @if ($pendaftaranDipanggil->isEmpty())
            <p class="text-center text-gray-400 py-8">Belum ada nomor pendaftaran yang dipanggil.</p>
        @else
            <div class="grid grid-cols-2 md:grid-cols-4 gap-5">
                @foreach ($pendaftaranDipanggil as $a)
                    <div class="bg-amber-50 border-2 border-amber-200 rounded-2xl p-5 text-center" wire:key="reg-{{ $a->id }}">
                        <p class="text-3xl font-extrabold text-amber-600">{{ $a->kode_antrian }}</p>
                        <p class="text-xs text-gray-500 mt-2">Loket Pendaftaran</p>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- ===================== PANGGILAN POLI (PEMERIKSAAN) ===================== --}}
    <div class="card p-6">
        <h2 class="text-sm font-semibold text-gray-400 uppercase tracking-wide mb-4">Dipanggil untuk Pemeriksaan</h2>

        @if ($poliDipanggil->isEmpty())
            <p class="text-center text-gray-400 py-10">Belum ada nomor poli yang dipanggil.</p>
        @else
            <div class="grid grid-cols-2 md:grid-cols-3 gap-5">
                @foreach ($poliDipanggil as $a)
                    <div class="bg-sky-50 border-2 border-sky-200 rounded-2xl p-6 text-center" wire:key="poli-{{ $a->id }}">
                        <p class="text-4xl font-extrabold text-klinik-blue-dark">{{ $a->kode_antrian }}</p>
                        <p class="text-sm text-gray-500 mt-2">{{ $a->poli->nama }}</p>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- ===================== MENUNGGU PER POLI ===================== --}}
    <div class="card p-6">
        <h2 class="text-sm font-semibold text-gray-400 uppercase tracking-wide mb-4">Antrian Menunggu per Poli</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            @foreach ($menungguPerPoli as $p)
                <div class="bg-gray-50 rounded-xl p-4 text-center">
                    <p class="text-2xl font-bold text-gray-700">{{ $p->antrians_count }}</p>
                    <p class="text-xs text-gray-400 mt-1">{{ $p->nama }}</p>
                </div>
            @endforeach
        </div>
    </div>

    <p class="text-center text-xs text-gray-300">Halaman ini otomatis diperbarui setiap 5 detik</p>
</div>
