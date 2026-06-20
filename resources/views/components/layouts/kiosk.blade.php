<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Kiosk' }} - SIM-KLINIK</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="font-sans text-gray-700 antialiased bg-klinik-bg min-h-screen">

    {{-- HEADER KIOSK --}}
    <div class="flex items-center justify-between px-8 py-5 bg-white border-b border-gray-100">
        
        {{-- Bagian Kiri: Logo dan Teks --}}
        <div class="flex items-center gap-3">
            <img src="{{ asset('img/logo_sim-klinik.png') }}" alt="Logo Klinik" class="h-10 w-auto object-contain">
            <div>
                <p class="font-bold text-gray-800 leading-none">SIM-KLINIK</p>
                <p class="text-[11px] text-gray-400">Sistem Informasi Klinik</p>
            </div>
        </div>

        {{-- Bagian Kanan: Tombol Fullscreen --}}
        <button onclick="toggleFullScreen()" class="p-2 text-gray-400 hover:text-gray-700 bg-gray-50 hover:bg-gray-100 rounded-lg transition" title="Layar Penuh">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3.75v4.5m0-4.5h4.5m-4.5 0L9 9M3.75 20.25v-4.5m0 4.5h4.5m-4.5 0L9 15M20.25 3.75h-4.5m4.5 0v4.5m0-4.5L15 9m5.25 11.25h-4.5m4.5 0v-4.5m0 4.5L15 15" />
            </svg>
        </button>

    </div>

    {{-- KONTEN UTAMA KIOSK --}}
    <main class="p-8">
        {{ $slot }}
    </main>

    @livewireScripts

    {{-- SCRIPT UNTUK FULLSCREEN --}}
    <script>
        function toggleFullScreen() {
            if (!document.fullscreenElement) {
                document.documentElement.requestFullscreen().catch((err) => {
                    console.error(`Error attempting to enable fullscreen: ${err.message}`);
                });
            } else {
                if (document.exitFullscreen) {
                    document.exitFullscreen();
                }
            }
        }
    </script>
</body>
</html>