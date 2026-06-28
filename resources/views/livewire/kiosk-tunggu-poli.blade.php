<div
    wire:poll.5s
    x-data="{ 
        suaraAktif: localStorage.getItem('simklinik_suara_poli') !== 'false',
        waktu: '',
        tanggal: '',
        init() {
            localStorage.setItem('simklinik_suara_poli', this.suaraAktif);
            this.$nextTick(() => {
                const video = this.$refs.video;
                if (video) video.muted = !this.suaraAktif;
            });
            this.updateClock();
            setInterval(() => this.updateClock(), 1000);
        },
        updateClock() {
            const opsiTanggal = { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' };
            const sekarang = new Date();
            this.waktu = sekarang.toLocaleTimeString('id-ID', { hour12: false });
            this.tanggal = sekarang.toLocaleDateString('id-ID', opsiTanggal);
        }
    }"
    class="h-screen bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 flex flex-col font-sans overflow-hidden select-none"
>
    {{-- ================= HEADER ================= --}}
    <div class="flex-shrink-0 bg-white/95 backdrop-blur-sm border-b-4 border-klinik-blue-dark px-8 py-4 flex items-center justify-between shadow-lg z-10">
        <div class="flex items-center gap-4">
            <div class="flex items-center justify-center">
                <img src="{{ asset('img/logo_sim-klinik.png') }}" alt="SIM-KLINIK" class="w-14 h-14 object-contain">
            </div>
            <div>
                <h1 class="text-2xl font-black text-slate-800 tracking-tight leading-tight drop-shadow-sm">SIM-KLINIK UTAMA</h1>
                <p class="text-xs font-bold text-klinik-blue-dark uppercase tracking-[0.15em]">Sistem Informasi Manajemen Antrian</p>
            </div>
        </div>

        <div class="flex items-center gap-6">
            <button
                @click="suaraAktif = !suaraAktif; localStorage.setItem('simklinik_suara_poli', suaraAktif); const v = $refs.video; if (v) v.muted = !suaraAktif; if(!suaraAktif) window.speechSynthesis.cancel();"
                :class="suaraAktif ? 'bg-gradient-to-r from-emerald-500 to-emerald-600 text-white shadow-emerald-200/50' : 'bg-slate-200 text-slate-500'"
                class="flex items-center gap-2.5 px-5 py-2.5 rounded-xl text-sm font-bold transition-all duration-300 shadow-md hover:scale-105 active:scale-95"
            >
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-5 h-5">
                    <path x-show="suaraAktif" stroke-linecap="round" stroke-linejoin="round" d="M19.114 5.636a9 9 0 010 12.728M16.463 8.288a5.25 5.25 0 010 7.424M6.75 8.25l4.72-4.72a.75.75 0 011.28.53v15.88a.75.75 0 01-1.28.53l-4.72-4.72H4.51c-.88 0-1.704-.507-1.938-1.354A9.01 9.01 0 012.25 12c0-.83.112-1.633.322-2.396C2.806 8.756 3.63 8.25 4.51 8.25H6.75z"/>
                    <path x-show="!suaraAktif" stroke-linecap="round" stroke-linejoin="round" d="M17.25 9.75L19.5 12m0 0l2.25 2.25M19.5 12l2.25-2.25M19.5 12l2.25-2.25m-10.5-6l4.72-4.72a.75.75 0 011.28.53v15.88a.75.75 0 01-1.28.53l-4.72-4.72H4.51c-.88 0-1.704-.507-1.938-1.354A9.01 9.01 0 012.25 12c0-.83.112-1.633.322-2.396C2.806 8.756 3.63 8.25 4.51 8.25H6.75z"/>
                </svg>
                <span x-text="suaraAktif ? 'SUARA AKTIF' : 'SUARA MATI'"></span>
            </button>

            <div class="text-right border-l-2 border-slate-200 pl-6">
                <p x-text="waktu" class="text-4xl font-mono font-black text-slate-800 tracking-tight leading-none drop-shadow-sm"></p>
                <p x-text="tanggal" class="text-sm font-bold text-slate-500 mt-1 uppercase tracking-wide"></p>
            </div>
        </div>
    </div>

    {{-- ================= KONTEN UTAMA ================= --}}
    <div class="flex-1 flex flex-col gap-5 p-6 overflow-y-auto">
        
        {{-- Row Atas: Panggilan + Video --}}
        <div class="grid grid-cols-12 gap-5 flex-1">
            
            {{-- Panel Panggilan --}}
            <div class="col-span-5 bg-gradient-to-b from-klinik-blue-dark to-klinik-blue-dark text-white rounded-3xl flex flex-col justify-between overflow-hidden shadow-2xl shadow-klinik-blue-dark/30 relative">
                <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_top,rgba(255,255,255,0.12),transparent_70%)] pointer-events-none"></div>
                <div class="bg-klinik-blue-dark/50 text-center py-5 font-black text-xl uppercase tracking-[0.15em] border-b border-white/10 backdrop-blur-sm relative z-10">
                    ANTRIAN DIPANGGIL
                </div>
                
                <div class="flex-1 flex items-center justify-center py-6 relative z-10">
                    @if($dipanggil->isEmpty())
                        <p class="text-xl font-black tracking-tighter opacity-30 select-none">—</p>
                    @else
                        <p class="text-[10rem] font-mono font-black tracking-tight drop-shadow-[0_8px_16px_rgba(0,0,0,0.3)] animate-pulse select-none">
                            {{ $dipanggil->first()->kode_antrian }}
                        </p>
                    @endif
                </div>
                
                <div class="bg-klinik-blue-dark/80 text-center py-6 border-t border-white/10 backdrop-blur-sm relative z-10">
                    <p class="text-3xl font-black uppercase tracking-wider drop-shadow-sm">
                        {{ $dipanggil->isEmpty() ? 'BELUM ADA ANTRIAN' : $dipanggil->first()->poli->nama }}
                    </p>
                </div>

                {{-- Decorative dots --}}
                <div class="absolute bottom-4 left-1/2 -translate-x-1/2 flex gap-2 z-10">
                    <span class="w-1.5 h-1.5 rounded-full bg-white/20"></span>
                    <span class="w-1.5 h-1.5 rounded-full bg-white/40"></span>
                    <span class="w-1.5 h-1.5 rounded-full bg-white/20"></span>
                </div>
            </div>

            {{-- Panel Video --}}
            <div class="col-span-7 bg-gradient-to-br from-slate-800 to-slate-950 rounded-3xl shadow-2xl flex items-center justify-center relative overflow-hidden group border border-white/5 aspect-[14/9]">
                @if ($media)
                    <video id="kiosk-video" x-ref="video"
                        class="absolute inset-0 w-full h-full object-cover" autoplay loop playsinline
                        @click="if ($refs.video.paused) { $refs.video.play() } else { $refs.video.pause() }">
                        <source src="{{ $media->url() }}" type="{{ $media->mime_type }}">
                    </video>

                    {{-- Tombol overlay (play/pause + fullscreen) --}}
                    <div
                        x-data="{ show: false }"
                        @mouseenter="show = true" @mouseleave="show = false"
                        class="absolute inset-0 z-10"
                    >
                        {{-- Play/Pause di tengah --}}
                        <div class="absolute inset-0 flex items-center justify-center">
                            <button x-cloak x-show="show"
                                @click="if ($refs.video.paused) { $refs.video.play() } else { $refs.video.pause() }"
                                class="w-16 h-16 bg-black/50 hover:bg-black/70 text-white rounded-full flex items-center justify-center transition-all duration-300 backdrop-blur-sm border border-white/20"
                            >
                                <svg x-show="$refs.video?.paused" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24" class="w-8 h-8 ml-1">
                                    <path d="M8 5v14l11-7z"/>
                                </svg>
                                <svg x-show="!$refs.video?.paused" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24" class="w-8 h-8">
                                    <path d="M6 4h4v16H6V4zm8 0h4v16h-4V4z"/>
                                </svg>
                            </button>
                        </div>

                        {{-- Fullscreen di pojok kanan bawah --}}
                        <div class="absolute bottom-4 right-4 flex items-center gap-2">
                            <span x-cloak x-show="show"
                                class="bg-black/60 text-[10px] text-white/70 font-mono px-2.5 py-1 rounded-md backdrop-blur-sm">
                                {{ $media->judul }}
                            </span>
                            <button x-cloak x-show="show"
                                @click="if (!document.fullscreenElement) { $refs.video.requestFullscreen() } else { document.exitFullscreen() }"
                                class="w-9 h-9 bg-black/50 hover:bg-black/70 text-white rounded-lg flex items-center justify-center transition-all duration-200 backdrop-blur-sm border border-white/20"
                            >
                                <svg x-show="!document.fullscreenElement" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24" class="w-5 h-5">
                                    <path d="M7 14H5v5h5v-2H7v-3zm-2-4h2V7h3V5H5v5zm12 7h-3v2h5v-5h-2v3zM14 5v2h3v3h2V5h-5z"/>
                                </svg>
                                <svg x-show="document.fullscreenElement" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24" class="w-5 h-5">
                                    <path d="M5 16h3v3h2v-5H5v2zm3-8H5v2h5V5H8v3zm6 11h2v-3h3v-2h-5v5zm2-11V5h-2v5h5V8h-3z"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                @else
                    <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_center,rgba(255,255,255,0.05),transparent_70%)]"></div>
                    <div class="absolute inset-0 bg-[linear-gradient(rgba(255,255,255,0.02)_1px,transparent_1px)] bg-[size:100%_4px] pointer-events-none"></div>
                    <div class="absolute inset-0 flex flex-col items-center justify-center z-10 gap-4">
                        <div class="w-24 h-24 bg-white/5 group-hover:bg-klinik-blue-dark/30 text-white/60 group-hover:text-white rounded-full flex items-center justify-center transition-all duration-500 shadow-xl backdrop-blur-sm group-hover:scale-110 cursor-pointer border border-white/10 group-hover:border-klinik-blue-dark/50">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24" class="w-12 h-12 ml-1">
                                <path d="M8 5v14l11-7z"/>
                            </svg>
                        </div>
                        <span class="text-xs font-bold text-slate-500 group-hover:text-slate-300 tracking-[0.2em] uppercase transition-colors duration-500">Media Informasi</span>
                    </div>
                    <div class="absolute top-4 right-4 bg-black/50 text-[10px] text-slate-500 font-mono px-2 py-0.5 rounded-md border border-white/5 backdrop-blur-sm z-20">1080p</div>
                @endif

                {{-- Corner accents --}}
                <div class="absolute top-3 left-3 w-8 h-8 border-t-2 border-l-2 border-white/10 rounded-tl-lg"></div>
                <div class="absolute top-3 right-3 w-8 h-8 border-t-2 border-r-2 border-white/10 rounded-tr-lg"></div>
                <div class="absolute bottom-3 left-3 w-8 h-8 border-b-2 border-l-2 border-white/10 rounded-bl-lg"></div>
                <div class="absolute bottom-3 right-3 w-8 h-8 border-b-2 border-r-2 border-white/10 rounded-br-lg"></div>
            </div>
        </div>

        {{-- ================= BARIS BAWAH: ANTRIAN SELANJUTNYA ================= --}}
        <div class="flex-1 bg-white/95 backdrop-blur-sm border border-white/20 rounded-3xl p-4 flex flex-col shadow-2xl min-h-[120px]">
            <div class="flex items-center gap-2 mb-2 px-1">
                <div class="w-2 h-2 rounded-full bg-klinik-blue-dark animate-pulse"></div>
                <p class="text-[10px] font-black text-slate-500 uppercase tracking-[0.2em]">Antrian Selanjutnya Per Poliklinik</p>
            </div>

            <div class="flex-1 flex items-stretch gap-3">
                @forelse ($antrianPerPoli as $id => $item)
                    @php $poli = $item['poli']; @endphp
                    <div class="flex-1 bg-gradient-to-b from-slate-50 to-white border border-slate-200 rounded-2xl px-4 py-3 text-center flex flex-col items-center justify-center shadow-md">
                        <p class="text-xs font-black uppercase tracking-[0.2em] text-slate-400 mb-1">{{ $poli->nama }}</p>
                        @forelse ($item['antrians'] as $a)
                            <p class="text-4xl font-mono font-black text-slate-700 drop-shadow-sm tracking-tight">{{ $a->kode_antrian }}</p>
                            <p class="text-xs font-bold text-klinik-blue-dark uppercase tracking-wider mt-1">Menunggu</p>
                        @empty
                            <p class="text-2xl font-mono font-bold text-slate-300">—</p>
                            <p class="text-[10px] font-bold text-slate-300 uppercase tracking-wider mt-1">Kosong</p>
                        @endforelse
                    </div>
                @empty
                    <div class="flex items-center justify-center w-full h-full">
                        <p class="text-sm font-bold text-slate-400 uppercase tracking-wide">Tidak ada antrian menunggu</p>
                    </div>
                @endforelse
            </div>
        </div>

    </div>

    <script src="{{ asset('js/call-queue.js') }}"></script>
</div>
