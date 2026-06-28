<!DOCTYPE html>
<html lang="id" class="h-full bg-gray-50/50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Dashboard' }} - SIM-KLINIK</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="font-sans text-gray-600 antialiased h-full">
<div class="flex min-h-screen">

    <aside class="w-64 bg-white border-r border-gray-200/60 hidden lg:flex lg:flex-col sticky top-0 h-screen">
        <div class="flex items-center gap-3 px-6 h-16 border-b border-gray-100">
      
                <img src="{{ asset('img/logo_sim-klinik.png') }}" alt="SIM-KLINIK" class="w-8 h-8 object-contain">

            <div>
                <p class="font-bold text-gray-950 tracking-tight leading-none">SIM-KLINIK</p>
                <p class="text-[10px] text-gray-400 font-medium uppercase tracking-wider mt-0.5">Sistem Informasi</p>
            </div>
        </div>

        <nav class="flex-1 overflow-y-auto px-4 py-6 space-y-7">
            <div class="space-y-1">
                <a href="{{ route('dashboard') }}" class="nav-link flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-medium transition-all duration-200 {{ request()->routeIs('dashboard') ? 'bg-blue-50 text-blue-600 shadow-sm' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                    <x-icon name="home" class="w-5 h-5" /> Dashboard
                </a>
                <a href="{{ route('kiosk.antrian') }}" target="_blank" class="nav-link flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-medium text-gray-600 hover:bg-gray-50 hover:text-gray-900 transition-all duration-200">
                    <x-icon name="queue" class="w-5 h-5" /> Kiosk Ambil Nomor
                </a>
                <a href="{{ route('kiosk.tunggu.registrasi') }}" target="_blank" class="nav-link flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-medium text-gray-600 hover:bg-gray-50 hover:text-gray-900 transition-all duration-200">
                    <x-icon name="ticket" class="w-5 h-5" /> Papan Registrasi
                </a>
                <a href="{{ route('kiosk.tunggu.poli') }}" target="_blank" class="nav-link flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-medium text-gray-600 hover:bg-gray-50 hover:text-gray-900 transition-all duration-200">
                    <x-icon name="users" class="w-5 h-5" /> Papan Antrian Poli
                </a>
            </div>

            @if (auth()->user()->hasRole('admin', 'resepsionis', 'dokter', 'petugas_rekam_medis'))
                <div class="space-y-2">
                    <div class="flex items-center justify-between px-4">
                        <p class="text-[11px] font-bold text-gray-400 tracking-wider uppercase">Administrasi</p>
                        <x-icon name="chevron-up" class="w-3.5 h-3.5 text-gray-400/70" />
                    </div>
                    <div class="space-y-1">
                        @if (auth()->user()->hasRole('admin', 'resepsionis'))
                            <a href="{{ route('pendaftaran-pasien') }}" class="nav-link flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-medium transition-all duration-200 {{ request()->routeIs('pendaftaran-pasien') ? 'bg-blue-50 text-blue-600 shadow-sm' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                                <x-icon name="user-plus" class="w-5 h-5" /> Registrasi Pasien
                            </a>
                        @endif
                        @if (auth()->user()->hasRole('admin', 'resepsionis', 'dokter', 'petugas_rekam_medis'))
                            <a href="{{ route('manajemen-antrian') }}" class="nav-link flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-medium transition-all duration-200 {{ request()->routeIs('manajemen-antrian') ? 'bg-blue-50 text-blue-600 shadow-sm' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                                <x-icon name="clipboard" class="w-5 h-5" /> Manajemen Antrian Poli
                            </a>
                        @endif
                        @if (auth()->user()->hasRole('admin', 'dokter', 'petugas_rekam_medis'))
                            <a href="{{ route('rekam-medis') }}" class="nav-link flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-medium transition-all duration-200 {{ request()->routeIs('rekam-medis') ? 'bg-blue-50 text-blue-600 shadow-sm' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                                <x-icon name="document-search" class="w-5 h-5" /> Rekam Medis
                            </a>
                        @endif
                    </div>
                </div>
            @endif

            @if (auth()->user()->hasRole('admin', 'apoteker'))
                <div class="space-y-2">
                    <div class="flex items-center justify-between px-4">
                        <p class="text-[11px] font-bold text-gray-400 tracking-wider uppercase">Operasional</p>
                        <x-icon name="chevron-up" class="w-3.5 h-3.5 text-gray-400/70" />
                    </div>
                    <div class="space-y-1">
                        <a href="{{ route('farmasi') }}" class="nav-link flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-medium transition-all duration-200 {{ request()->routeIs('farmasi') ? 'bg-blue-50 text-blue-600 shadow-sm' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                            <x-icon name="pill" class="w-5 h-5" /> Farmasi
                        </a>
                        <a href="{{ route('kasir-billing') }}" class="nav-link flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-medium transition-all duration-200 {{ request()->routeIs('kasir-billing') ? 'bg-blue-50 text-blue-600 shadow-sm' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                            <x-icon name="credit-card" class="w-5 h-5" /> Farmasi & Pembayaran
                        </a>
                    </div>
                </div>
            @endif

            <div class="space-y-2">
                <div class="flex items-center justify-between px-4">
                    <p class="text-[11px] font-bold text-gray-400 tracking-wider uppercase">Pengaturan</p>
                    <x-icon name="chevron-up" class="w-3.5 h-3.5 text-gray-400/70" />
                </div>
                <div class="space-y-1">
                    @if (auth()->user()->hasRole('admin'))
                        <a href="{{ route('laporan') }}" class="nav-link flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-medium transition-all duration-200 {{ request()->routeIs('laporan') ? 'bg-blue-50 text-blue-600 shadow-sm' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                            <x-icon name="chart" class="w-5 h-5" /> Laporan
                        </a>
                        <a href="{{ route('layanan-poli') }}" class="nav-link flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-medium transition-all duration-200 {{ request()->routeIs('layanan-poli') ? 'bg-blue-50 text-blue-600 shadow-sm' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                            <x-icon name="tag" class="w-5 h-5" /> Layanan & Poli
                        </a>
                        <a href="{{ route('manajemen-user') }}" class="nav-link flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-medium transition-all duration-200 {{ request()->routeIs('manajemen-user') ? 'bg-blue-50 text-blue-600 shadow-sm' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                            <x-icon name="users" class="w-5 h-5" /> Manajemen User
                        </a>
                        <a href="{{ route('media-informasi') }}" class="nav-link flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-medium transition-all duration-200 {{ request()->routeIs('media-informasi') ? 'bg-blue-50 text-blue-600 shadow-sm' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                            <x-icon name="video" class="w-5 h-5" /> Media Informasi
                        </a>
                    @endif
                    <a href="{{ route('pengaturan') }}" class="nav-link flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-medium transition-all duration-200 {{ request()->routeIs('pengaturan') ? 'bg-blue-50 text-blue-600 shadow-sm' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                        <x-icon name="cog" class="w-5 h-5" /> Pengaturan
                    </a>
                </div>
            </div>
        </nav>
    </aside>

    <div class="flex-1 flex flex-col min-w-0">

        <header class="h-16 bg-white border-b border-gray-200/60 flex items-center justify-between px-8 sticky top-0 z-10">
            <h1 class="text-xl font-bold text-gray-900 tracking-tight">{{ $title ?? 'Dashboard' }}</h1>

            <div class="flex items-center gap-5">
                <livewire:notification-bell />

                <div class="h-6 w-px bg-gray-200 hidden sm:block"></div>

                <div class="flex items-center gap-4">
                    <div class="text-right hidden sm:block">
                        <p class="text-sm font-semibold text-gray-900 leading-none">{{ auth()->user()->name }}</p>
                        <p class="text-[11px] font-medium text-gray-400 mt-1 uppercase tracking-wider">{{ auth()->user()->role?->label() }}</p>
                    </div>
                    
                    <a href="{{ route('pengaturan') }}" class="block focus:outline-none focus:ring-2 focus:ring-blue-500/20 rounded-full">
                        <div class="w-8 h-8 rounded-full bg-klinik-blue-dark flex items-center justify-center text-white text-xs font-semibold shadow-sm">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </div>
                    </a>

                    <form method="POST" action="{{ route('logout') }}" class="flex items-center">
                        @csrf
                        <button type="submit" class="p-2 text-gray-400 hover:text-red-500 rounded-xl hover:bg-red-50 transition-all duration-150" title="Keluar">
                            <x-icon name="logout" class="w-5 h-5" />
                        </button>
                    </form>
                </div>
            </div>
        </header>

        <main class="flex-1 p-8">
            {{ $slot }}
        </main>
    </div>
</div>

@livewireScripts
</body>
</html>