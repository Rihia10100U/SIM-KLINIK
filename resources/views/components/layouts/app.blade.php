<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Dashboard' }} - SIM-KLINIK</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="font-sans text-gray-700 antialiased">
<div class="flex min-h-screen">

    {{-- ===================== SIDEBAR ===================== --}}
    <aside class="w-64 bg-white border-r border-gray-100 hidden lg:flex lg:flex-col">

        {{-- Logo --}}
        <div class="flex items-center gap-2 px-6 py-6">
            <div class="w-9 h-9 rounded-lg bg-klinik-green/10 flex items-center justify-center text-klinik-green">
                <x-icon name="cross" class="w-6 h-6" />
            </div>
            <div>
                <p class="font-bold text-gray-800 leading-none">SIM-KLINIK</p>
                <p class="text-[11px] text-gray-400">Sistem Informasi Klinik</p>
            </div>
        </div>

        <nav class="flex-1 overflow-y-auto px-4 pb-6 space-y-6">

            {{-- Menu utama --}}
            <div class="space-y-1">
                <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <x-icon name="home" /> Dashboard
                </a>

                {{-- Kiosk dibagi jadi 3: ambil nomor, papan registrasi, papan poli --}}
                <a href="{{ route('kiosk.antrian') }}" target="_blank" class="nav-link">
                    <x-icon name="queue" /> Kiosk Ambil Nomor
                </a>
                <a href="{{ route('kiosk.tunggu.registrasi') }}" target="_blank" class="nav-link">
                    <x-icon name="ticket" /> Papan Antrian Registrasi
                </a>
                <a href="{{ route('kiosk.tunggu.poli') }}" target="_blank" class="nav-link">
                    <x-icon name="users" /> Papan Antrian Poli
                </a>
            </div>

            {{-- Grup ADMINISTRASI --}}
            @if (auth()->user()->hasRole('admin', 'resepsionis', 'dokter', 'petugas_rekam_medis'))
                <div>
                    <div class="flex items-center justify-between px-4 mb-2">
                        <p class="text-xs font-semibold text-gray-400 tracking-wide">ADMINISTRASI</p>
                        <x-icon name="chevron-up" class="w-3.5 h-3.5 text-gray-300" />
                    </div>
                    <div class="space-y-1">
                        @if (auth()->user()->hasRole('admin', 'resepsionis'))
                            <a href="{{ route('panggilan-pendaftaran') }}" class="nav-link {{ request()->routeIs('panggilan-pendaftaran') ? 'active' : '' }}">
                                <x-icon name="ticket" /> Panggilan Pendaftaran
                            </a>
                            <a href="{{ route('cetak-antrian') }}" class="nav-link {{ request()->routeIs('cetak-antrian') ? 'active' : '' }}">
                                <x-icon name="printer" /> Cetak Antrian
                            </a>
                            <a href="{{ route('pendaftaran-pasien') }}" class="nav-link {{ request()->routeIs('pendaftaran-pasien') ? 'active' : '' }}">
                                <x-icon name="user-plus" /> Pendaftaran Pasien
                            </a>
                        @endif

                        @if (auth()->user()->hasRole('admin', 'resepsionis', 'dokter', 'petugas_rekam_medis'))
                            <a href="{{ route('manajemen-antrian') }}" class="nav-link {{ request()->routeIs('manajemen-antrian') ? 'active' : '' }}">
                                <x-icon name="clipboard" /> Manajemen Antrian
                            </a>
                        @endif

                        @if (auth()->user()->hasRole('admin', 'dokter', 'petugas_rekam_medis'))
                            <a href="{{ route('rekam-medis') }}" class="nav-link {{ request()->routeIs('rekam-medis') ? 'active' : '' }}">
                                <x-icon name="document-search" /> Rekam Medis
                            </a>
                        @endif
                    </div>
                </div>
            @endif

            {{-- Grup OPERASIONAL --}}
            @if (auth()->user()->hasRole('admin', 'apoteker'))
                <div>
                    <div class="flex items-center justify-between px-4 mb-2">
                        <p class="text-xs font-semibold text-gray-400 tracking-wide">OPERASIONAL</p>
                        <x-icon name="chevron-up" class="w-3.5 h-3.5 text-gray-300" />
                    </div>
                    <div class="space-y-1">
                        <a href="{{ route('farmasi') }}" class="nav-link {{ request()->routeIs('farmasi') ? 'active' : '' }}">
                            <x-icon name="pill" /> Farmasi
                        </a>
                        <a href="{{ route('kasir-billing') }}" class="nav-link {{ request()->routeIs('kasir-billing') ? 'active' : '' }}">
                            <x-icon name="credit-card" /> Farmasi & Pembayaran
                        </a>
                    </div>
                </div>
            @endif

            {{-- Grup PENGATURAN --}}
            <div>
                <div class="flex items-center justify-between px-4 mb-2">
                    <p class="text-xs font-semibold text-gray-400 tracking-wide">PENGATURAN</p>
                    <x-icon name="chevron-up" class="w-3.5 h-3.5 text-gray-300" />
                </div>
                <div class="space-y-1">
                    @if (auth()->user()->hasRole('admin'))
                        <a href="{{ route('data-layanan') }}" class="nav-link {{ request()->routeIs('data-layanan') ? 'active' : '' }}">
                            <x-icon name="tag" /> Data Layanan
                        </a>
                        <a href="{{ route('laporan') }}" class="nav-link {{ request()->routeIs('laporan') ? 'active' : '' }}">
                            <x-icon name="chart" /> Laporan
                        </a>
                        <a href="{{ route('manajemen-user') }}" class="nav-link {{ request()->routeIs('manajemen-user') ? 'active' : '' }}">
                            <x-icon name="users" /> Manajemen User
                        </a>
                    @endif

                    <a href="{{ route('pengaturan') }}" class="nav-link {{ request()->routeIs('pengaturan') ? 'active' : '' }}">
                        <x-icon name="cog" /> Pengaturan
                    </a>
                </div>
            </div>
        </nav>
    </aside>

    {{-- ===================== KONTEN UTAMA ===================== --}}
    <div class="flex-1 flex flex-col min-w-0">

        {{-- Topbar --}}
        <header class="h-20 bg-white border-b border-gray-100 flex items-center justify-between px-6">
            <h1 class="text-xl font-bold text-gray-800">{{ $title ?? 'Dashboard' }}</h1>

            <div class="flex items-center gap-4">
                <div class="relative hidden md:block">
                    <x-icon name="search" class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" />
                    <input
                        type="text"
                        placeholder="Cari pasien, RM, dokter..."
                        class="bg-gray-100 rounded-full pl-9 pr-4 py-2 text-sm w-72 focus:outline-none focus:ring-2 focus:ring-klinik-blue/40"
                    >
                </div>

                <button class="relative w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center text-gray-500">
                    <x-icon name="bell" class="w-5 h-5" />
                    <span class="absolute top-2 right-2.5 w-2 h-2 bg-red-500 rounded-full border border-white"></span>
                </button>

                <div class="flex items-center gap-3 pl-1">
                    <div class="text-right hidden sm:block">
                        <p class="text-sm font-medium text-gray-700 leading-none">{{ auth()->user()->name }}</p>
                        <p class="text-xs text-gray-400 mt-1 capitalize">{{ auth()->user()->role?->label() }}</p>
                    </div>

                    <a href="{{ route('pengaturan') }}">
                        <img
                            src="https://i.pravatar.cc/40?img=47"
                            class="w-10 h-10 rounded-full object-cover"
                            alt="Foto profil"
                        >
                    </a>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-gray-400 hover:text-red-500 transition-colors" title="Keluar">
                            <x-icon name="logout" class="w-5 h-5" />
                        </button>
                    </form>
                </div>
            </div>
        </header>

        {{-- Slot: konten tiap halaman dirender di sini --}}
        <main class="flex-1 p-6">
            {{ $slot }}
        </main>
    </div>
</div>

@livewireScripts
</body>
</html>
