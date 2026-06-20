<div wire:poll.5s class="max-w-[95rem] mx-auto p-4 md:p-8 h-full">

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        
        {{-- ===================== KOLOM KIRI (SIDEBAR L) ===================== --}}
        <div class="lg:col-span-4 flex flex-col gap-8">
            
            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-8 text-center" wire:ignore>
                <p id="clock-display" class="text-6xl xl:text-7xl font-black text-sky-600 tracking-tighter drop-shadow-sm">
                    00:00:00
                </p>
                <p id="date-display" class="text-lg xl:text-xl text-gray-500 font-bold mt-4 uppercase tracking-wide">
                    Memuat tanggal...
                </p>
            </div>

            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-8 flex-1">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-3 h-8 bg-gray-300 rounded-full"></div>
                    <h2 class="text-xl font-bold text-gray-700 uppercase tracking-wide">Antrian Menunggu</h2>
                </div>
                
                <div class="flex flex-col gap-4">
                    @foreach ($menunggu as $p)
                        <div class="bg-gray-50 border border-gray-100 rounded-2xl p-4 flex items-center justify-between hover:bg-gray-100 transition">
                            <span class="text-sm font-bold text-gray-500 uppercase">{{ $p->nama }}</span>
                            <span class="text-4xl font-black text-gray-700">{{ $p->antrians_count }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

        </div>

        {{-- ===================== KOLOM KANAN (KONTEN UTAMA) ===================== --}}
        <div class="lg:col-span-8 flex flex-col gap-8">
            
            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-8 flex flex-col sm:flex-row items-center justify-between text-center sm:text-left">
                <div>
                    <h1 class="text-4xl font-extrabold text-gray-800 tracking-widest uppercase">Papan Antrian</h1>
                    <p class="text-lg text-gray-400 font-medium mt-1">Harap duduk tenang menunggu giliran Anda dipanggil</p>
                </div>
            </div>

            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-8 flex-1">
                <div class="flex items-center gap-3 mb-8">
                    <div class="w-3 h-8 bg-sky-500 rounded-full"></div>
                    <h2 class="text-2xl font-bold text-gray-700 uppercase tracking-wide">Sedang Dipanggil</h2>
                </div>

                @if ($sedangDipanggil->isEmpty())
                    <div class="flex flex-col items-center justify-center py-24 bg-gray-50 rounded-3xl border-2 border-dashed border-gray-200">
                        <p class="text-2xl text-gray-400 font-medium">Belum ada nomor yang dipanggil.</p>
                    </div>
                @else
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        @foreach ($sedangDipanggil as $a)
                            <div class="bg-gradient-to-br from-sky-50 to-white border border-sky-200 shadow-md rounded-3xl p-12 text-center relative overflow-hidden" wire:key="panggil-{{ $a->id }}">
                                <div class="absolute top-0 left-0 w-full h-3 bg-sky-500"></div>
                                
                                <p class="text-[6.5rem] leading-none font-black text-sky-600 drop-shadow-md">{{ $a->kode_antrian }}</p>
                                <p class="text-2xl text-gray-700 font-extrabold mt-8 uppercase tracking-widest">{{ $a->poli->nama }}</p>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

        </div>

    </div>

    <p class="text-center text-sm text-gray-400 font-medium mt-8">Data antrian otomatis diperbarui setiap 5 detik</p>

    {{-- ===================== SCRIPT JAM REALTIME ===================== --}}
    <script>
        if (!window.clockRunning) {
            window.clockRunning = true;

            function updateTime() {
                const now = new Date();
                
                const hours = String(now.getHours()).padStart(2, '0');
                const minutes = String(now.getMinutes()).padStart(2, '0');
                const seconds = String(now.getSeconds()).padStart(2, '0');
                
                const clockEl = document.getElementById('clock-display');
                if (clockEl) {
                    clockEl.textContent = `${hours}:${minutes}:${seconds}`;
                }
                
                const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
                const dateEl = document.getElementById('date-display');
                if (dateEl) {
                    dateEl.textContent = now.toLocaleDateString('id-ID', options);
                }
            }

            updateTime();
            setInterval(updateTime, 1000);
        }
    </script>
</div>