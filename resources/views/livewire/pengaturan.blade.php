<div class="space-y-6 max-w-3xl">

    <div>
        <h2 class="text-xl font-bold text-gray-800">Pengaturan</h2>
        <p class="text-sm text-gray-400 mt-1">Kelola profil akun dan informasi klinik</p>
    </div>

    {{-- ===================== PROFIL SAYA ===================== --}}
    <div class="card p-6">
        <div class="flex items-center justify-between mb-1">
            <h3 class="font-semibold text-gray-800">Profil Saya</h3>
            <span class="badge bg-sky-100 text-sky-600 capitalize">{{ auth()->user()->role?->label() }}</span>
        </div>
        <p class="text-sm text-gray-400 mb-5">Perbarui nama dan email akun kamu</p>

        @if (session('sukses_profil'))
            <div class="bg-green-50 border border-green-200 text-green-700 text-sm rounded-xl px-4 py-3 mb-4">
                {{ session('sukses_profil') }}
            </div>
        @endif

        <form wire:submit="simpanProfil" class="space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="text-xs font-medium text-gray-500">Nama</label>
                    <input type="text" wire:model="nama" class="mt-1 w-full bg-gray-100 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-klinik-blue/40">
                    @error('nama') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="text-xs font-medium text-gray-500">Email</label>
                    <input type="email" wire:model="email" class="mt-1 w-full bg-gray-100 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-klinik-blue/40">
                    @error('email') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <p class="text-xs text-gray-400">
                Role hanya bisa diubah oleh Admin lewat Tinker/database — lihat README Step 13 untuk caranya.
            </p>

            <div class="flex justify-end">
                <button
                    type="submit"
                    wire:loading.attr="disabled"
                    wire:target="simpanProfil"
                    class="text-sm font-medium text-white bg-klinik-green px-5 py-2 rounded-full hover:bg-klinik-green-dark disabled:opacity-60"
                >
                    <span wire:loading.remove wire:target="simpanProfil">Simpan Profil</span>
                    <span wire:loading wire:target="simpanProfil">Menyimpan...</span>
                </button>
            </div>
        </form>
    </div>

    {{-- ===================== UBAH PASSWORD ===================== --}}
    <div class="card p-6">
        <h3 class="font-semibold text-gray-800 mb-1">Ubah Password</h3>
        <p class="text-sm text-gray-400 mb-5">Pastikan pakai password yang kuat dan tidak dipakai di tempat lain</p>

        @if (session('sukses_password'))
            <div class="bg-green-50 border border-green-200 text-green-700 text-sm rounded-xl px-4 py-3 mb-4">
                {{ session('sukses_password') }}
            </div>
        @endif

        <form wire:submit="ubahPassword" class="space-y-4">
            <div>
                <label class="text-xs font-medium text-gray-500">Password Saat Ini</label>
                <input type="password" wire:model="passwordSaatIni" class="mt-1 w-full bg-gray-100 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-klinik-blue/40">
                @error('passwordSaatIni') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="text-xs font-medium text-gray-500">Password Baru</label>
                    <input type="password" wire:model="passwordBaru" class="mt-1 w-full bg-gray-100 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-klinik-blue/40">
                    @error('passwordBaru') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="text-xs font-medium text-gray-500">Konfirmasi Password Baru</label>
                    <input type="password" wire:model="passwordBaruKonfirmasi" class="mt-1 w-full bg-gray-100 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-klinik-blue/40">
                    @error('passwordBaruKonfirmasi') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="flex justify-end">
                <button
                    type="submit"
                    wire:loading.attr="disabled"
                    wire:target="ubahPassword"
                    class="text-sm font-medium text-white bg-klinik-green px-5 py-2 rounded-full hover:bg-klinik-green-dark disabled:opacity-60"
                >
                    <span wire:loading.remove wire:target="ubahPassword">Ubah Password</span>
                    <span wire:loading wire:target="ubahPassword">Menyimpan...</span>
                </button>
            </div>
        </form>
    </div>

    {{-- ===================== INFO KLINIK (KHUSUS ADMIN) ===================== --}}
    @if ($isAdmin)
        <div class="card p-6">
            <h3 class="font-semibold text-gray-800 mb-1">Info Klinik</h3>
            <p class="text-sm text-gray-400 mb-5">Informasi umum klinik — hanya bisa diubah oleh Admin</p>

            @if (session('sukses_klinik'))
                <div class="bg-green-50 border border-green-200 text-green-700 text-sm rounded-xl px-4 py-3 mb-4">
                    {{ session('sukses_klinik') }}
                </div>
            @endif

            <form wire:submit="simpanKlinik" class="space-y-4">
                <div>
                    <label class="text-xs font-medium text-gray-500">Nama Klinik</label>
                    <input type="text" wire:model="namaKlinik" class="mt-1 w-full bg-gray-100 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-klinik-blue/40">
                    @error('namaKlinik') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="text-xs font-medium text-gray-500">Alamat</label>
                    <textarea wire:model="alamatKlinik" rows="2" class="mt-1 w-full bg-gray-100 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-klinik-blue/40"></textarea>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="text-xs font-medium text-gray-500">Telepon</label>
                        <input type="text" wire:model="teleponKlinik" class="mt-1 w-full bg-gray-100 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-klinik-blue/40">
                    </div>
                    <div>
                        <label class="text-xs font-medium text-gray-500">Jam Buka</label>
                        <input type="time" wire:model="jamBuka" class="mt-1 w-full bg-gray-100 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-klinik-blue/40">
                        @error('jamBuka') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="text-xs font-medium text-gray-500">Jam Tutup</label>
                        <input type="time" wire:model="jamTutup" class="mt-1 w-full bg-gray-100 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-klinik-blue/40">
                        @error('jamTutup') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="flex justify-end">
                    <button
                        type="submit"
                        wire:loading.attr="disabled"
                        wire:target="simpanKlinik"
                        class="text-sm font-medium text-white bg-klinik-green px-5 py-2 rounded-full hover:bg-klinik-green-dark disabled:opacity-60"
                    >
                        <span wire:loading.remove wire:target="simpanKlinik">Simpan Info Klinik</span>
                        <span wire:loading wire:target="simpanKlinik">Menyimpan...</span>
                    </button>
                </div>
            </form>
        </div>
    @endif
</div>
