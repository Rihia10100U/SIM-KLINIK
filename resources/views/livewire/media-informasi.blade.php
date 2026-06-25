<div class="space-y-6 max-w-3xl">
    <div>
        <h2 class="text-xl font-bold text-gray-800">Media Informasi</h2>
        <p class="text-sm text-gray-400 mt-1">Upload video untuk ditampilkan di layar kiosk (tunggu registrasi & tunggu poli)</p>
    </div>

    @if (session('sukses'))
        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 5000)" x-show="show" x-transition.duration.500ms
            class="bg-green-50 border border-green-200 text-green-700 text-sm rounded-xl px-4 py-3">
            {{ session('sukses') }}
        </div>
    @endif

    {{-- Form Upload --}}
    <form wire:submit="simpan" class="card p-6 space-y-4">
        <h3 class="font-semibold text-gray-800">Upload Video Baru</h3>

        <div>
            <label class="text-xs font-medium text-gray-500">Judul Video</label>
            <input type="text" wire:model="judul" placeholder="Mis: Video Profil Klinik"
                class="mt-1 w-full bg-gray-100 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-klinik-blue/40">
            @error('judul') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="text-xs font-medium text-gray-500">File Video (MP4, WebM, OGG, AVI — max 200MB)</label>
            <input type="file" wire:model="video" accept="video/mp4,video/webm,video/ogg,video/x-msvideo"
                class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-medium file:bg-klinik-blue/10 file:text-klinik-blue hover:file:bg-klinik-blue/20">
            @error('video') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            <div wire:loading wire:target="video" class="text-xs text-klinik-blue mt-2">Mengupload...</div>
        </div>

        <div class="flex justify-end">
            <button type="submit" wire:loading.attr="disabled" wire:target="simpan"
                class="text-sm font-medium text-white bg-klinik-blue px-5 py-2 rounded-full hover:opacity-90 disabled:opacity-60">
                <span wire:loading.remove wire:target="simpan">Upload Video</span>
                <span wire:loading wire:target="simpan">Mengupload...</span>
            </button>
        </div>
    </form>

    {{-- Daftar Video --}}
    <div class="card p-6">
        <h3 class="font-semibold text-gray-800 mb-4">Daftar Video</h3>

        @if ($mediaList->isEmpty())
            <p class="text-sm text-gray-400">Belum ada video. Upload video pertama kamu.</p>
        @else
            <div class="space-y-4">
                @foreach ($mediaList as $media)
                    <div class="flex items-center gap-4 p-4 bg-gray-50 rounded-xl {{ $media->aktif ? 'ring-2 ring-klinik-blue' : '' }}">
                        <div class="w-16 h-12 bg-gray-200 rounded-lg flex items-center justify-center text-gray-400 shrink-0 overflow-hidden">
                            <video class="w-full h-full object-cover" src="{{ $media->url() }}" muted preload="metadata"></video>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-800 truncate">{{ $media->judul }}</p>
                            <p class="text-xs text-gray-400">{{ $media->file_name }}</p>
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            @if ($media->aktif)
                                <span class="badge bg-green-100 text-green-600 text-xs">Aktif</span>
                                <button wire:click="nonaktifkan({{ $media->id }})"
                                    class="text-xs font-medium text-orange-500 hover:underline">
                                    Nonaktifkan
                                </button>
                            @else
                                <button wire:click="setAktif({{ $media->id }})"
                                    class="text-xs font-medium text-klinik-blue hover:underline">
                                    Aktifkan
                                </button>
                            @endif
                            <button wire:click="hapus({{ $media->id }})"
                                wire:confirm="Yakin ingin menghapus video ini?"
                                class="text-xs font-medium text-red-500 hover:underline">
                                Hapus
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
