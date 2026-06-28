<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Kiosk' }} - SIM-KLINIK</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles

    {{-- Engine suara terpusat — WAJIB tanpa defer agar fungsi siap sebelum Alpine jalan --}}
    <script src="{{ asset('js/antrian-speaker.js') }}"></script>
</head>
<body class="font-sans text-gray-700 antialiased">

    {{ $slot }}

    {{-- Tombol Fullscreen — floating di pojok kanan bawah, hanya di kiosk --}}
    <div
        x-data="{ fullscreen: false }"
        x-init="$watch('fullscreen', val => val ? document.documentElement.requestFullscreen() : document.exitFullscreen())"
        @fullscreenchange.window="fullscreen = !!document.fullscreenElement"
        class="fixed bottom-5 right-5 z-50"
    >
        <button
            @click="fullscreen = !fullscreen"
            class="w-12 h-12 bg-white/20 hover:bg-white/40 backdrop-blur-md text-white rounded-2xl flex items-center justify-center transition-all duration-300 shadow-xl border border-white/30 hover:scale-110"
            :class="fullscreen ? 'bg-red-500/30 hover:bg-red-500/50 border-red-400/40' : ''"
            :title="fullscreen ? 'Keluar Fullscreen' : 'Fullscreen'"
        >
            <svg x-show="!fullscreen" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3.75v4.5m0-4.5h4.5m-4.5 0L9 9M3.75 20.25v-4.5m0 4.5h4.5m-4.5 0L9 15M20.25 3.75h-4.5m4.5 0v4.5m0-4.5L15 9m5.25 11.25h-4.5m4.5 0v-4.5m0 4.5L15 15"/>
            </svg>
            <svg x-show="fullscreen" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 9V4.5M9 9H4.5M9 9L3.75 3.75M9 15v4.5M9 15H4.5M9 15l-5.25 5.25M15 9h4.5M15 9V4.5M15 9l5.25-5.25M15 15h4.5M15 15v4.5m0-4.5l5.25 5.25"/>
            </svg>
        </button>
    </div>

    @livewireScripts
</body>
</html>