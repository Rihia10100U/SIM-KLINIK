<div class="space-y-6">

    {{-- Notifikasi sukses --}}
    @if (session('sukses'))
        <div class="bg-green-50 border border-green-200 text-green-700 text-sm rounded-xl px-4 py-3">
            {{ session('sukses') }}
        </div>
    @endif

    {{-- ===================== HEADER ===================== --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-gray-800">Pendaftaran Pasien</h2>
            <p class="text-sm text-gray-400 mt-1">Kelola data pasien yang terdaftar di klinik</p>
        </div>

        <div class="flex items-center gap-3">
            <div class="relative">
                <x-icon name="search" class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" />
                <input
                    type="text"
                    wire:model.live.debounce.400ms="cari"
                    placeholder="Cari nama, No. RM, No. HP..."
                    class="bg-gray-100 rounded-full pl-9 pr-4 py-2 text-sm w-64 focus:outline-none focus:ring-2 focus:ring-klinik-blue/40"
                >
            </div>

            <button
                wire:click="bukaForm"
                class="flex items-center gap-2 bg-klinik-green text-white text-sm font-medium px-4 py-2 rounded-full hover:bg-klinik-green-dark transition-colors whitespace-nowrap"
            >
                <x-icon name="user-plus" class="w-4 h-4" /> Tambah Pasien
            </button>
        </div>
    </div>

    {{-- ===================== TABEL PASIEN ===================== --}}
    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 text-left text-gray-400 text-xs uppercase tracking-wide">
                        <th class="px-5 py-3 font-medium">No. RM</th>
                        <th class="px-5 py-3 font-medium">Nama</th>
                        <th class="px-5 py-3 font-medium">Jenis Kelamin</th>
                        <th class="px-5 py-3 font-medium">Tanggal Lahir</th>
                        <th class="px-5 py-3 font-medium">No. HP</th>
                        <th class="px-5 py-3 font-medium text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse ($pasiens as $p)
                        <tr class="hover:bg-gray-50/60" wire:key="pasien-{{ $p->id }}">
                            <td class="px-5 py-3 font-medium text-sky-500 whitespace-nowrap">{{ $p->no_rm }}</td>
                            <td class="px-5 py-3 text-gray-700">{{ $p->nama }}</td>
                            <td class="px-5 py-3 text-gray-500">
                                {{ $p->jenis_kelamin === 'L' ? 'Laki-laki' : ($p->jenis_kelamin === 'P' ? 'Perempuan' : '-') }}
                            </td>
                            <td class="px-5 py-3 text-gray-500 whitespace-nowrap">
                                {{ $p->tanggal_lahir?->translatedFormat('d M Y') ?? '-' }}
                            </td>
                            <td class="px-5 py-3 text-gray-500 whitespace-nowrap">{{ $p->no_hp ?? '-' }}</td>
                            <td class="px-5 py-3">
                                <div class="flex items-center justify-end gap-3">
                                    <button wire:click="edit({{ $p->id }})" class="text-xs font-medium text-sky-500 hover:underline">
                                        Edit
                                    </button>
                                    <button
                                        wire:click="hapus({{ $p->id }})"
                                        wire:confirm="Yakin ingin menghapus data pasien {{ $p->nama }}?"
                                        class="text-xs font-medium text-red-500 hover:underline"
                                    >
                                        Hapus
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-10 text-center text-gray-400 text-sm">
                                @if ($cari)
                                    Tidak ada pasien yang cocok dengan pencarian "{{ $cari }}".
                                @else
                                    Belum ada data pasien. Klik "Tambah Pasien" untuk mendaftarkan pasien baru.
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($pasiens->hasPages())
            <div class="px-5 py-4 border-t border-gray-50">
                {{ $pasiens->links() }}
            </div>
        @endif
    </div>

    {{-- ===================== MODAL TAMBAH / EDIT ===================== --}}
    @if ($showModal)
        <div
            class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4 overflow-y-auto"
            wire:click.self="tutupForm"
        >
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg p-6 my-8">

                <div class="flex items-center justify-between mb-5">
                    <h3 class="font-bold text-gray-800">
                        {{ $editId ? 'Edit Data Pasien' : 'Tambah Pasien Baru' }}
                    </h3>
                    <button wire:click="tutupForm" class="text-gray-400 hover:text-gray-600">
                        <x-icon name="cross" class="w-5 h-5 rotate-45" />
                    </button>
                </div>

                <form wire:submit="simpan" class="space-y-4">

                    <div>
                        <label class="text-xs font-medium text-gray-500">Nama Lengkap</label>
                        <input
                            type="text"
                            wire:model="nama"
                            class="mt-1 w-full bg-gray-100 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-klinik-blue/40"
                        >
                        @error('nama') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-xs font-medium text-gray-500">NIK</label>
                            <input
                                type="text"
                                wire:model="nik"
                                class="mt-1 w-full bg-gray-100 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-klinik-blue/40"
                            >
                            @error('nik') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="text-xs font-medium text-gray-500">Jenis Kelamin</label>
                            <select
                                wire:model="jenis_kelamin"
                                class="mt-1 w-full bg-gray-100 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-klinik-blue/40"
                            >
                                <option value="">Pilih</option>
                                <option value="L">Laki-laki</option>
                                <option value="P">Perempuan</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-xs font-medium text-gray-500">Tanggal Lahir</label>
                            <input
                                type="date"
                                wire:model="tanggal_lahir"
                                class="mt-1 w-full bg-gray-100 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-klinik-blue/40"
                            >
                            @error('tanggal_lahir') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="text-xs font-medium text-gray-500">No. HP</label>
                            <input
                                type="text"
                                wire:model="no_hp"
                                class="mt-1 w-full bg-gray-100 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-klinik-blue/40"
                            >
                        </div>
                    </div>

                    <div>
                        <label class="text-xs font-medium text-gray-500">Alamat</label>
                        <textarea
                            wire:model="alamat"
                            rows="2"
                            class="mt-1 w-full bg-gray-100 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-klinik-blue/40"
                        ></textarea>
                    </div>

                    {{-- Poli tujuan: cuma muncul saat menambah pasien BARU --}}
                    @if (! $editId)
                        <div class="border-t border-gray-100 pt-4">
                            <label class="text-xs font-medium text-gray-500">Poli Tujuan</label>
                            <select
                                wire:model="poli_id"
                                class="mt-1 w-full bg-gray-100 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-klinik-blue/40"
                            >
                                <option value="">Pilih poli</option>
                                @foreach ($polis as $poli)
                                    <option value="{{ $poli->id }}">{{ $poli->nama }}</option>
                                @endforeach
                            </select>
                            @error('poli_id') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                            <p class="text-xs text-gray-400 mt-1">
                                Pasien akan langsung diberi nomor antrian ke poli ini dan tiketnya dicetak setelah disimpan.
                            </p>
                        </div>
                    @endif

                    <div class="flex items-center justify-end gap-3 pt-2">
                        <button
                            type="button"
                            wire:click="tutupForm"
                            class="text-sm font-medium text-gray-500 px-4 py-2 rounded-full hover:bg-gray-100"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            wire:loading.attr="disabled"
                            wire:target="simpan"
                            class="text-sm font-medium text-white bg-klinik-green px-5 py-2 rounded-full hover:bg-klinik-green-dark disabled:opacity-60"
                        >
                            <span wire:loading.remove wire:target="simpan">
                                {{ $editId ? 'Simpan' : 'Simpan & Cetak Antrian' }}
                            </span>
                            <span wire:loading wire:target="simpan">Menyimpan...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
