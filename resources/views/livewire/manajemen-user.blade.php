<div class="space-y-6">

    {{-- Notifikasi --}}
    @if (session('sukses'))
        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 5000)" x-show="show" x-transition.duration.500ms
            class="bg-green-50 border border-green-200 text-green-700 text-sm rounded-xl px-4 py-3">
            {{ session('sukses') }}
        </div>
    @endif
    @if (session('gagal'))
        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 5000)" x-show="show" x-transition.duration.500ms
            class="bg-red-50 border border-red-200 text-red-600 text-sm rounded-xl px-4 py-3">
            {{ session('gagal') }}
        </div>
    @endif

    {{-- ===================== HEADER ===================== --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-gray-800">Manajemen User</h2>
            <p class="text-sm text-gray-400 mt-1">Kelola akun staf dan hak akses (role) masing-masing</p>
        </div>

        <div class="flex items-center gap-3">
            <div class="relative">
                <x-icon name="search" class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" />
                <input
                    type="text"
                    wire:model.live.debounce.400ms="cari"
                    placeholder="Cari nama atau email..."
                    class="bg-gray-100 rounded-full pl-9 pr-4 py-2 text-sm w-64 focus:outline-none focus:ring-2 focus:ring-klinik-blue-dark/40"
                >
            </div>

            <button
                wire:click="bukaForm"
                class="flex items-center gap-2 bg-klinik-blue-dark text-white text-sm font-medium px-4 py-2 rounded-full hover:bg-blue-800 transition-colors whitespace-nowrap"
            >
                <x-icon name="user-plus" class="w-4 h-4" /> Tambah User
            </button>
        </div>
    </div>

    {{-- ===================== TABEL USER ===================== --}}
    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 text-left text-gray-400 text-xs uppercase tracking-wide">
                        <th class="px-5 py-3 font-medium">Nama</th>
                        <th class="px-5 py-3 font-medium">Email</th>
                        <th class="px-5 py-3 font-medium">Role</th>
                        <th class="px-5 py-3 font-medium text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse ($users as $u)
                        @php
                            $warnaRole = match ($u->role->value) {
                                'admin'       => 'bg-purple-100 text-purple-600',
                                'resepsionis' => 'bg-sky-100 text-klinik-blue-dark',
                                'dokter'      => 'bg-green-100 text-green-600',
                                'kasir'       => 'bg-amber-100 text-amber-600',
                                'apoteker'    => 'bg-pink-100 text-pink-600',
                                default       => 'bg-gray-100 text-gray-600',
                            };
                        @endphp
                        <tr class="hover:bg-gray-50/60" wire:key="user-{{ $u->id }}">
                            <td class="px-5 py-3 text-gray-700 font-medium">
                                {{ $u->name }}
                                @if ($u->id === auth()->id())
                                    <span class="text-[10px] text-gray-400 font-normal">(kamu)</span>
                                @endif
                            </td>
                            <td class="px-5 py-3 text-gray-500">{{ $u->email }}</td>
                            <td class="px-5 py-3">
                                <span class="badge {{ $warnaRole }}">{{ $u->role->label() }}</span>
                            </td>
                            <td class="px-5 py-3">
                                <div class="flex items-center justify-end gap-3">
                                    <button wire:click="edit({{ $u->id }})" class="text-xs font-medium text-klinik-blue-dark hover:underline">
                                        Edit
                                    </button>
                                    @if ($u->id !== auth()->id())
                                        <button
                                            wire:click="hapus({{ $u->id }})"
                                            wire:confirm="Hapus user {{ $u->name }}?"
                                            class="text-xs font-medium text-red-500 hover:underline"
                                        >
                                            Hapus
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-5 py-10 text-center text-gray-400 text-sm">
                                @if ($cari)
                                    Tidak ada user yang cocok dengan pencarian "{{ $cari }}".
                                @else
                                    Belum ada data user.
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($users->hasPages())
            <div class="px-5 py-4 border-t border-gray-50">
                {{ $users->links() }}
            </div>
        @endif
    </div>

    {{-- ===================== MODAL TAMBAH / EDIT ===================== --}}
    @if ($showModal)
        <div class="modal-overlay fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4" wire:click.self="tutupForm">
            <div class="modal-content bg-white rounded-2xl shadow-xl w-full max-w-lg p-6">

                <div class="flex items-center justify-between mb-5">
                    <h3 class="font-bold text-gray-800">
                        {{ $editId ? 'Edit User' : 'Tambah User Baru' }}
                    </h3>
                    <button wire:click="tutupForm" class="text-gray-400 hover:text-gray-600">
                        <x-icon name="cross" class="w-5 h-5 rotate-45" />
                    </button>
                </div>

                <form wire:submit="simpan" class="space-y-4">
                    <div>
                        <label class="text-xs font-medium text-gray-500">Nama</label>
                        <input type="text" wire:model="nama" class="mt-1 w-full bg-gray-100 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-klinik-blue-dark/40">
                        @error('nama') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-xs font-medium text-gray-500">Email</label>
                            <input type="email" wire:model="email" class="mt-1 w-full bg-gray-100 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-klinik-blue-dark/40">
                            @error('email') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="text-xs font-medium text-gray-500">Role</label>
                            <select wire:model="role" class="mt-1 w-full bg-gray-100 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-klinik-blue-dark/40">
                                @foreach ($roles as $r)
                                    <option value="{{ $r['value'] }}">{{ $r['label'] }}</option>
                                @endforeach
                            </select>
                            @error('role') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-xs font-medium text-gray-500">
                                Password {{ $editId ? '(kosongkan jika tidak diubah)' : '' }}
                            </label>
                            <input type="password" wire:model="password" class="mt-1 w-full bg-gray-100 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-klinik-blue-dark/40">
                            @error('password') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="text-xs font-medium text-gray-500">Konfirmasi Password</label>
                            <input type="password" wire:model="passwordKonfirmasi" class="mt-1 w-full bg-gray-100 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-klinik-blue-dark/40">
                            @error('passwordKonfirmasi') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-2">
                        <button type="button" wire:click="tutupForm" class="text-sm font-medium text-gray-500 px-4 py-2 rounded-full hover:bg-gray-100">
                            Batal
                        </button>
                        <button
                            type="submit"
                            wire:loading.attr="disabled"
                            wire:target="simpan"
                            class="text-sm font-medium text-white bg-klinik-blue-dark px-5 py-2 rounded-full hover:bg-blue-800 disabled:opacity-60"
                        >
                            <span wire:loading.remove wire:target="simpan">Simpan</span>
                            <span wire:loading wire:target="simpan">Menyimpan...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
