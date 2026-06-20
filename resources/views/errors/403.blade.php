<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Akses Ditolak - SIM-KLINIK</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans text-gray-700 antialiased bg-klinik-bg min-h-screen flex items-center justify-center p-4">

    <div class="card p-10 max-w-md text-center">
        <div class="w-14 h-14 rounded-2xl bg-red-50 flex items-center justify-center text-red-400 mx-auto mb-4">
            <x-icon name="cross" class="w-7 h-7 rotate-45" />
        </div>

        <h1 class="text-lg font-bold text-gray-800 mb-2">Akses Ditolak</h1>
        <p class="text-sm text-gray-400 mb-6">
            {{ $exception->getMessage() ?: 'Kamu tidak punya hak akses ke halaman ini.' }}
        </p>

        @auth
            <a href="{{ route('dashboard') }}" class="inline-block text-sm font-medium text-white bg-klinik-green px-5 py-2 rounded-full hover:bg-klinik-green-dark">
                Kembali ke Dashboard
            </a>
        @else
            <a href="{{ route('login') }}" class="inline-block text-sm font-medium text-white bg-klinik-green px-5 py-2 rounded-full hover:bg-klinik-green-dark">
                Masuk
            </a>
        @endauth
    </div>

</body>
</html>
