<div
    wire:poll.5s
    x-data="{ 
        suaraAktif: localStorage.getItem('simklinik_suara_poli') !== 'false',
        waktu: '',
        tanggal: '',
        init() {
            localStorage.setItem('simklinik_suara_poli', this.suaraAktif);
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
    class="min-h-screen bg-slate-100 flex flex-col font-sans overflow-hidden select-none"
>
    {{-- ================= HEADER TAMPILAN ================= --}}
    <div class="bg-white border-b-4 border-rose-600 px-8 py-4 flex items-center justify-between shadow-md">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-2xl bg-rose-600 flex items-center justify-center text-white shadow-md shadow-rose-200">
                <x-icon name="cross" class="w-8 h-8" />
            </div>
            <div>
                <h1 class="text-2xl font-black text-slate-800 tracking-tight leading-tight">SIM-KLINIK UTAMA</h1>
                <p class="text-xs font-bold text-rose-600 uppercase tracking-widest">Sistem Informasi Manajemen Antrian</p>
            </div>
        </div>

        <div class="flex items-center gap-6">
            <button
                @click="suaraAktif = !suaraAktif; localStorage.setItem('simklinik_suara_poli', suaraAktif); if(!suaraAktif) window.speechSynthesis.cancel();"
                :class="suaraAktif ? 'bg-emerald-600 text-white shadow-emerald-100' : 'bg-slate-200 text-slate-600'"
                class="flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-bold transition-all duration-300 shadow-md"
            >
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-5 h-5">
                    <path x-show="suaraAktif" stroke-linecap="round" stroke-linejoin="round" d="M19.114 5.636a9 9 0 010 12.728M16.463 8.288a5.25 5.25 0 010 7.424M6.75 8.25l4.72-4.72a.75.75 0 011.28.53v15.88a.75.75 0 01-1.28.53l-4.72-4.72H4.51c-.88 0-1.704-.507-1.938-1.354A9.01 9.01 0 012.25 12c0-.83.112-1.633.322-2.396C2.806 8.756 3.63 8.25 4.51 8.25H6.75z"/>
                    <path x-show="!suaraAktif" stroke-linecap="round" stroke-linejoin="round" d="M17.25 9.75L19.5 12m0 0l2.25 2.25M19.5 12l2.25-2.25M19.5 12l2.25-2.25m-10.5-6l4.72-4.72a.75.75 0 011.28.53v15.88a.75.75 0 01-1.28.53l-4.72-4.72H4.51c-.88 0-1.704-.507-1.938-1.354A9.01 9.01 0 012.25 12c0-.83.112-1.633.322-2.396C2.806 8.756 3.63 8.25 4.51 8.25H6.75z"/>
                </svg>
                <span x-text="suaraAktif ? 'SUARA AKTIF' : 'SUARA MATI'"></span>
            </button>

            <div class="text-right border-l-2 border-slate-200 pl-6">
                <p x-text="waktu" class="text-4xl font-mono font-black text-slate-800 tracking-tight leading-none"></p>
                <p x-text="tanggal" class="text-sm font-bold text-slate-500 mt-1 uppercase tracking-wide"></p>
            </div>
        </div>
    </div>

    {{-- ================= KONTEN UTAMA (FULL-WIDTH LAYOUT) ================= --}}
    <div class="flex-1 flex flex-col gap-6 p-6 overflow-hidden">
        
        {{-- Row Atas: Panggilan Utama & Video --}}
        <div class="grid grid-cols-12 gap-6 flex-1">
            
            <div class="col-span-5 bg-rose-600 text-white rounded-3xl flex flex-col justify-between overflow-hidden shadow-xl shadow-rose-200 relative">
                <div class="bg-rose-700 text-center py-5 font-black text-xl uppercase tracking-widest border-b border-rose-500/30">
                    ANTRIAN DIPANGGIL
                </div>
                
                <div class="flex-1 flex items-center justify-center py-6">
                    @if($dipanggil->isEmpty())
                        <p class="text-7xl font-black tracking-tighter opacity-40">—</p>
                    @else
                        <p class="text-[10rem] font-mono font-black tracking-tight drop-shadow-[0_6px_10px_rgba(0,0,0,0.25)] animate-pulse">
                            {{ $dipanggil->first()->kode_antrian }}
                        </p>
                    @endif
                </div>
                
                <div class="bg-rose-800 text-center py-6 border-t border-rose-500/30">
                    <p class="text-3xl font-black uppercase tracking-wider">
                        {{ $dipanggil->isEmpty() ? 'BELUM ADA ANTRIAN' : $dipanggil->first()->poli->nama }}
                    </p>
                </div>
            </div>

            <div class="col-span-7 bg-slate-900 rounded-3xl shadow-xl flex items-center justify-center relative overflow-hidden group border border-slate-800">
                <div class="absolute inset-0 bg-slate-950/60 flex flex-col items-center justify-center z-10">
                    <div class="w-24 h-24 bg-white/10 group-hover:bg-rose-600 text-white rounded-full flex items-center justify-center transition-all duration-300 shadow-xl backdrop-blur-sm group-hover:scale-110 cursor-pointer">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24" class="w-12 h-12 ml-1">
                            <path d="M8 5v14l11-7z"/>
                        </svg>
                    </div>
                    <span class="text-xs font-bold text-slate-400 mt-4 tracking-widest uppercase opacity-70">Media Informasi Penyuluhan</span>
                </div>
                <div class="absolute top-4 right-4 bg-black/40 text-[10px] text-slate-400 font-mono px-2 py-0.5 rounded border border-slate-700">1080p Fhd</div>
            </div>
        </div>

        {{-- ================= BARIS BAWAH: MONITOR STATUS PER POLI ================= --}}
        <div class="h-44 bg-white border border-slate-200 rounded-3xl p-5 flex flex-col shadow-md">
            <p class="text-xs font-black text-slate-400 uppercase tracking-widest mb-3">MONITOR STATUS ANTRIAN SEKARANG PER POLIKLINIK</p>
            
            <div class="flex-1 grid grid-cols-4 gap-4">
                @foreach ($polis->take(4) as $p)
                    @php
                        $antrianAktifPoli = $dipanggil->first(function($item) use ($p) {
                            return ($item->poli_id == $p->id) || (isset($item->poli->id) && $item->poli->id == $p->id);
                        });
                    @endphp

                    <div class="flex flex-col justify-between rounded-2xl overflow-hidden shadow-sm border transition-all duration-300 {{ $antrianAktifPoli ? 'bg-amber-500 border-amber-600 text-white shadow-md shadow-amber-100 scale-[1.01]' : 'bg-slate-50 border-slate-200 text-slate-700' }}">
                        
                        <div class="flex-1 flex flex-col items-center justify-center py-2">
                            <span class="text-4xl font-mono font-black tracking-tight">
                                {{ $antrianAktifPoli ? $antrianAktifPoli->kode_antrian : '—' }}
                            </span>
                            <span class="text-[11px] font-bold uppercase mt-0.5 {{ $antrianAktifPoli ? 'text-amber-100' : 'text-slate-400' }}">
                                {{ $antrianAktifPoli ? 'Sedang Diperiksa' : "$p->menunggu_count Menunggu" }}
                            </span>
                        </div>

                        <div class="text-center py-2.5 text-xs font-black uppercase tracking-wide border-t {{ $antrianAktifPoli ? 'bg-amber-600/40 border-amber-600/20 text-white' : 'bg-slate-100 border-slate-200 text-slate-600' }}">
                            {{ $p->nama }}
                        </div>

                    </div>
                @endforeach
            </div>
        </div>

    </div>

    <script src="{{ asset('js/call-queue.js') }}"></script>
</div>