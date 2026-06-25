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

    @livewireScripts
</body>
</html>