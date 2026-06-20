<div class="max-w-xl mx-auto">

    @if ($step === 'cari')
        {{-- ===================== LANGKAH 1: CARI PASIEN ===================== --}}
        <div class="card p-8 text-center">
            <h1 class="text-xl font-bold text-gray-800 mb-2">Ambil Nomor Antrian</h1>
            <p class="text-sm text-gray-400 mb-6">Masukkan No. Rekam Medis (RM) kamu untuk mengambil nomor antrian</p>

            <form wire:submit="cariPasien" class="space-y-4">
                <input
                    type="text"
                    wire:model="noRm"
                    placeholder="mis. RM-2024-0001"
                    autofocus
                    class="w-full text-center text-lg bg-gray-100 rounded-xl px-4 py-4 focus:outline-none focus:ring-2 focus:ring-klinik-blue/40"
                >

                @if ($errorPasien)
                    <p class="text-sm text-red-500">{{ $errorPasien }}</p>
                @endif

                <button
                    type="submit"
                    wire:loading.attr="disabled"
                    wire:target="cariPasien"
                    class="w-full bg-klinik-green text-white font-semibold py-4 rounded-xl hover:bg-klinik-green-dark disabled:opacity-60"
                >
                    <span wire:loading.remove wire:target="cariPasien">Cari</span>
                    <span wire:loading wire:target="cariPasien">Mencari...</span>
                </button>
            </form>

            <p class="text-xs text-gray-400 mt-6">
                Belum terdaftar? Silakan menuju loket Pendaftaran Pasien terlebih dahulu.
            </p>
        </div>
    @elseif ($step === 'pilih-poli' && $pasien)
        {{-- ===================== LANGKAH 2: PILIH POLI ===================== --}}
        <div class="card p-8 text-center">
            <h1 class="text-xl font-bold text-gray-800 mb-1">Halo, {{ $pasien->nama }}</h1>
            <p class="text-sm text-gray-400 mb-6">Pilih poli tujuan kamu</p>

            <div class="grid grid-cols-2 gap-4">
                @foreach ($polis as $poli)
                    <button
                        wire:click="pilihPoli({{ $poli->id }})"
                        class="bg-gray-50 hover:bg-sky-50 hover:border-sky-300 border-2 border-transparent rounded-2xl p-6 text-center transition-colors"
                    >
                        <p class="text-2xl font-bold text-sky-500">{{ $poli->kode }}</p>
                        <p class="text-sm text-gray-600 mt-1">{{ $poli->nama }}</p>
                    </button>
                @endforeach
            </div>

            <button wire:click="ulangi" class="text-xs text-gray-400 hover:underline mt-6">
                Batal & kembali
            </button>
        </div>
    @elseif ($step === 'tiket' && $tiket)
        {{-- ===================== LANGKAH 3: TIKET ===================== --}}
        <div class="card p-10 text-center">
            <p class="text-sm text-gray-400 mb-2">Nomor Antrian Kamu</p>
            <p class="text-6xl font-extrabold text-klinik-green mb-3">{{ $tiket->kode_antrian }}</p>
            <p class="text-sm text-gray-600">{{ $tiket->poli->nama }}</p>
            <p class="text-xs text-gray-400 mt-1">{{ $pasien->nama ?? '' }}</p>

            <p class="text-sm text-gray-500 mt-6">Silakan tunggu nomor kamu dipanggil di layar antrian.</p>

            <button
                wire:click="ulangi"
                class="mt-8 text-sm font-medium text-white bg-klinik-green px-6 py-3 rounded-full hover:bg-klinik-green-dark"
            >
                Selesai
            </button>
        </div>
    @endif
</div>
