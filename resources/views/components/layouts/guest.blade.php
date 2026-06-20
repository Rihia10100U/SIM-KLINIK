<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Masuk - SIM-KLINIK</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="font-sans text-gray-700 antialiased bg-klinik-bg min-h-screen flex items-center justify-center p-4">

    <div class="w-full max-w-sm">
        <div class="flex flex-col items-center mb-8">
            <div class="w-14 h-14 rounded-2xl bg-klinik-green/10 flex items-center justify-center text-klinik-green mb-3">
                <x-icon name="cross" class="w-8 h-8" />
            </div>
            <h1 class="font-bold text-gray-800 text-lg">SIM-KLINIK</h1>
            <p class="text-xs text-gray-400">Sistem Informasi Klinik</p>
        </div>

        {{ $slot }}
    </div>

@livewireScripts
</body>
</html>
