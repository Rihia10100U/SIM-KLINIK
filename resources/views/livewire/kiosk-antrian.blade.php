<div class="max-w-xl mx-auto">

    @if (! $tiket)
        {{-- ===================== AMBIL NOMOR ===================== --}}
        <div class="card p-10 text-center">
            <h1 class="text-xl font-bold text-gray-800 mb-2">Selamat Datang</h1>
            <p class="text-sm text-gray-400 mb-8">
                Tekan tombol di bawah untuk mengambil nomor antrian pendaftaran.
                Staf pendaftaran akan memanggil nomor kamu untuk proses selanjutnya.
            </p>

            <button
                wire:click="ambilNomor"
                wire:loading.attr="disabled"
                wire:target="ambilNomor"
                class="w-full bg-klinik-green text-white font-semibold py-5 rounded-xl text-lg hover:bg-klinik-green-dark disabled:opacity-60"
            >
                <span wire:loading.remove wire:target="ambilNomor">Ambil Nomor Antrian</span>
                <span wire:loading wire:target="ambilNomor">Memproses...</span>
            </button>
        </div>
    @else
        {{-- ===================== TIKET (TAMPILAN LAYAR) ===================== --}}
        <div class="card p-10 text-center">
            <p class="text-sm text-gray-400 mb-2">Nomor Antrian Pendaftaran Kamu</p>
            <p class="text-6xl font-extrabold text-klinik-green mb-3">{{ $tiket->kode_antrian }}</p>
            <p class="text-sm text-gray-500 mt-4">
                Silakan duduk dan tunggu nomor kamu dipanggil oleh staf pendaftaran di layar antrian.
            </p>

            @if ($pesanPrinter)
                <p class="text-xs mt-4 {{ str_contains($pesanPrinter, 'berhasil') ? 'text-green-500' : 'text-amber-500' }}">
                    {{ $pesanPrinter }}
                </p>
            @endif

            <div class="flex flex-col gap-2 mt-8">
                @if ($printerAktif)
                    <button
                        wire:click="cetakUlang"
                        wire:loading.attr="disabled"
                        wire:target="cetakUlang"
                        class="text-sm font-medium text-klinik-green border border-klinik-green px-6 py-3 rounded-full hover:bg-klinik-green/5 disabled:opacity-60"
                    >
                        <span wire:loading.remove wire:target="cetakUlang">Cetak Ulang ke Printer</span>
                        <span wire:loading wire:target="cetakUlang">Mencetak...</span>
                    </button>
                @endif

                <button
                    onclick="window.print()"
                    class="text-sm font-medium text-klinik-green border border-klinik-green px-6 py-3 rounded-full hover:bg-klinik-green/5"
                >
                    Cetak via Browser
                </button>

                <button wire:click="ulangi" class="text-sm font-medium text-white bg-klinik-green px-6 py-3 rounded-full hover:bg-klinik-green-dark">
                    Selesai
                </button>
            </div>
        </div>

        {{-- ===================== MARKUP KHUSUS CETAK (THERMAL 58mm) ===================== --}}
        {{-- Tersembunyi di layar (lihat .print-ticket di resources/css/app.css), hanya muncul saat window.print() --}}
        <div class="print-ticket">
            <div style="font-family: monospace; text-align: center; padding: 4px;">
                <p style="font-size: 12px; font-weight: bold; margin: 0;">SIM-KLINIK</p>
                <p style="font-size: 10px; margin: 2px 0;">Nomor Antrian Pendaftaran</p>
                <p style="font-size: 28px; font-weight: bold; margin: 6px 0;">{{ $tiket->kode_antrian }}</p>
                <p style="font-size: 10px; margin: 2px 0;">{{ now()->format('d-m-Y H:i') }}</p>
                <p style="font-size: 9px; margin: 4px 0;">Silakan tunggu nomor Anda dipanggil</p>
            </div>
        </div>
    @endif

    @script
    <script>
        $wire.on('tiket-dibuat', () => {
            // Mau browser ikut nampilkan dialog print otomatis tiap ada tiket baru?
            // Hapus tanda komentar baris di bawah. Default dimatikan supaya tidak dobel
            // dengan printer ESC/POS yang sudah cetak otomatis (kalau diaktifkan di .env).
            // setTimeout(() => window.print(), 300);
        });
    </script>
    @endscript
</div>
