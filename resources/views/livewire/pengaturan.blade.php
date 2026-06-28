<div class="space-y-6 max-w-3xl">

    <div>
        <h2 class="text-xl font-bold text-gray-800">Pengaturan</h2>
        <p class="text-sm text-gray-400 mt-1">Kelola profil akun dan informasi klinik</p>
    </div>

    {{-- ===================== PROFIL SAYA ===================== --}}
    <div class="card p-6">
        <div class="flex items-center justify-between mb-1">
            <h3 class="font-semibold text-gray-800">Profil Saya</h3>
            <span class="badge bg-sky-100 text-klinik-blue-dark capitalize">{{ auth()->user()->role?->label() }}</span>
        </div>
        <p class="text-sm text-gray-400 mb-5">Perbarui nama dan email akun kamu</p>

        @if (session('sukses_profil'))
            <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 5000)" x-show="show" x-transition.duration.500ms
                class="bg-blue-50 border border-blue-200 text-blue-700 text-sm rounded-xl px-4 py-3 mb-4">
                {{ session('sukses_profil') }}
            </div>
        @endif

        <form wire:submit="simpanProfil" class="space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="text-xs font-medium text-gray-500">Nama</label>
                    <input type="text" wire:model="nama" class="mt-1 w-full bg-gray-100 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-klinik-blue-dark/40">
                    @error('nama') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="text-xs font-medium text-gray-500">Email</label>
                    <input type="email" wire:model="email" class="mt-1 w-full bg-gray-100 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-klinik-blue-dark/40">
                    @error('email') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <p class="text-xs text-gray-400">
                Role hanya bisa diubah Admin lewat menu Manajemen User.
            </p>

            <div class="flex justify-end">
                <button
                    type="submit"
                    wire:loading.attr="disabled"
                    wire:target="simpanProfil"
                    class="flex items-center gap-2 bg-klinik-blue-dark text-white text-sm font-medium px-4 py-2 rounded-full hover:bg-blue-800 transition-colors whitespace-nowrap"
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
            <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 5000)" x-show="show" x-transition.duration.500ms
                class="bg-blue-50 border border-blue-200 text-blue-700 text-sm rounded-xl px-4 py-3 mb-4">
                {{ session('sukses_password') }}
            </div>
        @endif

        <form wire:submit="ubahPassword" class="space-y-4">
            <div>
                <label class="text-xs font-medium text-gray-500">Password Saat Ini</label>
                <input type="password" wire:model="passwordSaatIni" class="mt-1 w-full bg-gray-100 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-klinik-blue-dark/40">
                @error('passwordSaatIni') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="text-xs font-medium text-gray-500">Password Baru</label>
                    <input type="password" wire:model="passwordBaru" class="mt-1 w-full bg-gray-100 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-klinik-blue-dark/40">
                    @error('passwordBaru') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="text-xs font-medium text-gray-500">Konfirmasi Password Baru</label>
                    <input type="password" wire:model="passwordBaruKonfirmasi" class="mt-1 w-full bg-gray-100 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-klinik-blue-dark/40">
                    @error('passwordBaruKonfirmasi') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="flex justify-end">
                <button
                    type="submit"
                    wire:loading.attr="disabled"
                    wire:target="ubahPassword"
                    class="flex items-center gap-2 bg-klinik-blue-dark text-white text-sm font-medium px-4 py-2 rounded-full hover:bg-blue-800 transition-colors whitespace-nowrap"
                >
                    <span wire:loading.remove wire:target="ubahPassword">Ubah Password</span>
                    <span wire:loading wire:target="ubahPassword">Menyimpan...</span>
                </button>
            </div>
        </form>
    </div>

    @if ($isAdmin)
        {{-- ===================== INFO KLINIK ===================== --}}
        <div class="card p-6">
            <h3 class="font-semibold text-gray-800 mb-1">Info Klinik</h3>
            <p class="text-sm text-gray-400 mb-5">Informasi umum klinik — hanya bisa diubah oleh Admin</p>

            @if (session('sukses_klinik'))
                <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 5000)" x-show="show" x-transition.duration.500ms
                    class="bg-blue-50 border border-blue-200 text-blue-700 text-sm rounded-xl px-4 py-3 mb-4">
                    {{ session('sukses_klinik') }}
                </div>
            @endif

            <form wire:submit="simpanKlinik" class="space-y-4">
                <div>
                    <label class="text-xs font-medium text-gray-500">Nama Klinik</label>
                    <input type="text" wire:model="namaKlinik" class="mt-1 w-full bg-gray-100 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-klinik-blue-dark/40">
                    @error('namaKlinik') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="text-xs font-medium text-gray-500">Alamat</label>
                    <textarea wire:model="alamatKlinik" rows="2" class="mt-1 w-full bg-gray-100 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-klinik-blue-dark/40"></textarea>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="text-xs font-medium text-gray-500">Telepon</label>
                        <input type="text" wire:model="teleponKlinik" class="mt-1 w-full bg-gray-100 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-klinik-blue-dark/40">
                    </div>
                    <div>
                        <label class="text-xs font-medium text-gray-500">Jam Buka</label>
                        <input type="time" wire:model="jamBuka" class="mt-1 w-full bg-gray-100 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-klinik-blue-dark/40">
                        @error('jamBuka') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="text-xs font-medium text-gray-500">Jam Tutup</label>
                        <input type="time" wire:model="jamTutup" class="mt-1 w-full bg-gray-100 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-klinik-blue-dark/40">
                        @error('jamTutup') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="flex justify-end">
                    <button
                        type="submit"
                        wire:loading.attr="disabled"
                        wire:target="simpanKlinik"
                        class="flex items-center gap-2 bg-klinik-blue-dark text-white text-sm font-medium px-4 py-2 rounded-full hover:bg-blue-800 transition-colors whitespace-nowrap"
                    >
                        <span wire:loading.remove wire:target="simpanKlinik">Simpan Info Klinik</span>
                        <span wire:loading wire:target="simpanKlinik">Menyimpan...</span>
                    </button>
                </div>
            </form>
        </div>

        {{-- ===================== PRINTER THERMAL ===================== --}}
        <div class="card p-6">
            <div class="flex items-center justify-between mb-1">
                <h3 class="font-semibold text-gray-800">Printer Thermal</h3>
                <span class="badge {{ $koneksiPrinter === 'none' ? 'bg-gray-100 text-gray-500' : 'bg-green-100 text-green-600' }}">
                    {{ $koneksiPrinter === 'none' ? 'Belum Aktif' : strtoupper($koneksiPrinter) }}
                </span>
            </div>
            <p class="text-sm text-gray-400 mb-5">
                Dipakai untuk mencetak nomor antrian otomatis di Kiosk Ruang Antrian.
                Diatur lewat file <code class="bg-gray-100 px-1 rounded">.env</code> — bukan dari sini.
            </p>

            @if ($koneksiPrinter === 'windows')
                <p class="text-xs text-klinik-blue-dark bg-sky-50 rounded-lg px-3 py-2 mb-4">
                    <strong>SmartCOM RPP02N:</strong> Setelah install driver, cek nama printer di
                    Control Panel → Devices and Printers. Ubah <code class="bg-white/60 px-1 rounded">PRINTER_WINDOWS_NAME</code>
                    di .env sesuai nama tersebut (default: <code class="bg-white/60 px-1 rounded">SmartCOM RPP02N</code>).
                </p>
            @elseif ($koneksiPrinter === 'com')
                <p class="text-xs text-klinik-blue-dark bg-sky-50 rounded-lg px-3 py-2 mb-4">
                    <strong>Koneksi COM:</strong> Cek nomor port di Device Manager → Ports (COM & LPT).
                    Ubah <code class="bg-white/60 px-1 rounded">PRINTER_COM_PORT</code> di .env
                    (default: <code class="bg-white/60 px-1 rounded">COM3</code>).
                    Untuk RPP02N via USB, biasanya <code class="bg-white/60 px-1 rounded">COM3</code> atau <code class="bg-white/60 px-1 rounded">COM4</code>.
                </p>
            @elseif ($koneksiPrinter === 'network')
                <p class="text-xs text-klinik-blue-dark bg-sky-50 rounded-lg px-3 py-2 mb-4">
                    Pastikan <code class="bg-white/60 px-1 rounded">PRINTER_HOST</code> (IP printer) dan
                    <code class="bg-white/60 px-1 rounded">PRINTER_PORT</code> (default 9100) sudah benar di .env.
                </p>
            @endif

            @if (session('sukses_printer'))
                <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 5000)" x-show="show" x-transition.duration.500ms
                    class="bg-blue-50 border border-blue-200 text-blue-700 text-sm rounded-xl px-4 py-3 mb-4">
                    {{ session('sukses_printer') }}
                </div>
            @endif
            @if (session('gagal_printer'))
                <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 5000)" x-show="show" x-transition.duration.500ms
                    class="bg-red-50 border border-red-200 text-red-600 text-sm rounded-xl px-4 py-3 mb-4">
                    {{ session('gagal_printer') }}
                </div>
            @endif

            <button
                wire:click="tesCetak"
                wire:loading.attr="disabled"
                wire:target="tesCetak"
                class="btn-primary"
            >
                <span wire:loading.remove wire:target="tesCetak">Tes Cetak</span>
                <span wire:loading wire:target="tesCetak">Mencetak...</span>
            </button>
        </div>
    @endif
</div>