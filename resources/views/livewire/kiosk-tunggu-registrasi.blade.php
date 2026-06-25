<div
    wire:poll.5s
    x-data="papanRegistrasi({{ $idTerbaru ?? 'null' }}, {{ $kodeTerbaru ? json_encode($kodeTerbaru) : 'null' }})"
    x-init="init()"
    class="min-h-screen bg-gradient-to-br from-amber-50 to-white flex flex-col"
>

    {{-- Header --}}
    <div class="bg-white border-b border-amber-100 px-8 py-5 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-klinik-blue/10 flex items-center justify-center text-klinik-blue">
                <x-icon name="cross" class="w-6 h-6" />
            </div>
            <div>
                <p class="font-bold text-gray-800 leading-none">SIM-KLINIK</p>
                <p class="text-xs text-gray-400">Papan Antrian Registrasi</p>
            </div>
        </div>

        <div class="flex items-center gap-4">
            {{-- Tombol aktifkan/matikan suara --}}
            <button
                @click="toggleSuara()"
                :class="suaraAktif ? 'bg-klinik-green text-white' : 'bg-gray-100 text-gray-500'"
                class="flex items-center gap-2 px-4 py-2 rounded-full text-sm font-medium transition-colors"
                :title="suaraAktif ? 'Klik untuk matikan suara' : 'Klik untuk aktifkan suara'"
            >
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor" class="w-4 h-4">
                    <path x-show="suaraAktif" stroke-linecap="round" stroke-linejoin="round" d="M19.114 5.636a9 9 0 010 12.728M16.463 8.288a5.25 5.25 0 010 7.424M6.75 8.25l4.72-4.72a.75.75 0 011.28.53v15.88a.75.75 0 01-1.28.53l-4.72-4.72H4.51c-.88 0-1.704-.507-1.938-1.354A9.01 9.01 0 012.25 12c0-.83.112-1.633.322-2.396C2.806 8.756 3.63 8.25 4.51 8.25H6.75z"/>
                    <path x-show="!suaraAktif" stroke-linecap="round" stroke-linejoin="round" d="M17.25 9.75L19.5 12m0 0l2.25 2.25M19.5 12l2.25-2.25M19.5 12l-2.25 2.25m-10.5-6l4.72-4.72a.75.75 0 011.28.53v15.88a.75.75 0 01-1.28.53l-4.72-4.72H4.51c-.88 0-1.704-.507-1.938-1.354A9.01 9.01 0 012.25 12c0-.83.112-1.633.322-2.396C2.806 8.756 3.63 8.25 4.51 8.25H6.75z"/>
                </svg>
                <span x-text="suaraAktif ? 'Suara Aktif' : 'Suara Mati'"></span>
            </button>

            <div class="text-right">
                <p class="text-sm font-medium text-gray-700">{{ now()->locale('id')->translatedFormat('l, d F Y') }}</p>
                <p class="text-xs text-gray-400">{{ now()->format('H:i') }} — otomatis diperbarui tiap 5 detik</p>
            </div>
        </div>
    </div>

    {{-- Banner animasi saat sedang bicara --}}
    <div
        x-show="sedangBicara"
        x-transition
        class="bg-amber-500 text-white text-center py-2 text-sm font-medium"
    >
        🔊 Memanggil nomor antrian...
    </div>

    {{-- Konten utama --}}
    <div class="flex-1 flex flex-col p-8 gap-6">

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
            @php $terbaru = $dipanggil->first(); @endphp
            <div class="flex justify-center">
                <div class="bg-amber-500 text-white rounded-3xl px-16 py-10 text-center shadow-xl shadow-amber-200">
                    <p class="text-sm font-semibold uppercase tracking-widest opacity-80 mb-2">Dipanggil Sekarang</p>
                    <p class="text-7xl font-extrabold tracking-tight">{{ $terbaru->kode_antrian }}</p>
                    <p class="text-sm opacity-80 mt-3">Silakan menuju Loket Pendaftaran</p>
                </div>
            </div>

            @if ($dipanggil->count() > 1)
                <div>
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide text-center mb-3">Sebelumnya</p>
                    <div class="flex flex-wrap justify-center gap-3">
                        @foreach ($dipanggil->skip(1) as $a)
                            <div class="bg-amber-100 text-amber-700 rounded-2xl px-6 py-3 text-center opacity-60" wire:key="reg-prev-{{ $a->id }}">
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

    @script
    <script>
        function papanRegistrasi(idAwal, kodeAwal) {
            return {
                idTerakhirDiucapkan: null,
                suaraAktif: true,
                sedangBicara: false,

                init() {
                    // Simpan status suara di localStorage supaya tidak reset tiap refresh
                    const tersimpan = localStorage.getItem('simklinik_suara_registrasi');
                    if (tersimpan !== null) {
                        this.suaraAktif = tersimpan === 'true';
                    }

                    // Cek nomor awal saat halaman baru dibuka
                    if (idAwal && kodeAwal) {
                        this.idTerakhirDiucapkan = idAwal;
                        // Jangan ucapkan saat baru buka halaman (bisa jadi nomor lama)
                    }

                    // Dengarkan event dari Livewire setiap kali komponen selesai di-render ulang oleh poll
                    Livewire.hook('commit', ({ component, respond }) => {
                        respond(() => {
                            if (component.name !== 'kiosk-tunggu-registrasi') return;

                            const idBaru = component.snapshot?.data?.idTerbaru ?? null;
                            const kodeBaru = component.snapshot?.data?.kodeTerbaru ?? null;

                            if (idBaru && kodeBaru && idBaru !== this.idTerakhirDiucapkan) {
                                this.idTerakhirDiucapkan = idBaru;
                                this.ucapkan(kodeBaru);
                            }
                        });
                    });
                },

                toggleSuara() {
                    this.suaraAktif = !this.suaraAktif;
                    localStorage.setItem('simklinik_suara_registrasi', this.suaraAktif);

                    if (!this.suaraAktif) {
                        window.speechSynthesis.cancel();
                        this.sedangBicara = false;
                    }
                },

                ucapkan(kode) {
                    if (!this.suaraAktif) return;
                    if (!window.speechSynthesis) return;

                    window.speechSynthesis.cancel();

                    // Format: "REG-001" → "R E G 0 0 1" agar dieja per karakter, bukan dibaca sebagai kata
                    const kodeEja = kode.replace(/-/g, ' ').split('').join(' ');
                    const teks = `Nomor antrian ${kodeEja}, silahkan ke resepsionis`;

                    const ucapan = new SpeechSynthesisUtterance(teks);
                    ucapan.lang = 'id-ID';
                    ucapan.rate = 0.85;
                    ucapan.pitch = 1;
                    ucapan.volume = 1;

                    ucapan.onstart = () => { this.sedangBicara = true; };
                    ucapan.onend   = () => { this.sedangBicara = false; };
                    ucapan.onerror = () => { this.sedangBicara = false; };

                    // Ulangi pengumuman 2 kali supaya lebih terdengar
                    ucapan.onend = () => {
                        this.sedangBicara = false;

                        setTimeout(() => {
                            if (!this.suaraAktif) return;

                            const ucapanUlang = new SpeechSynthesisUtterance(teks);
                            ucapanUlang.lang  = 'id-ID';
                            ucapanUlang.rate  = 0.85;
                            ucapanUlang.pitch = 1;
                            window.speechSynthesis.speak(ucapanUlang);
                        }, 800);
                    };

                    window.speechSynthesis.speak(ucapan);
                    this.sedangBicara = true;
                },
            };
        }
    </script>
    @endscript
</div>
