<?php

namespace App\Livewire;

use App\Models\MediaInformasi as MediaModel;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;

#[Layout('components.layouts.app')]
class MediaInformasi extends Component
{
    use WithFileUploads;

    public string $title = 'Media Informasi';

    public $video;

    public string $judul = '';

    public ?MediaModel $editing = null;

    public function render()
    {
        $mediaList = MediaModel::latest()->get();

        return view('livewire.media-informasi', [
            'mediaList' => $mediaList,
        ]);
    }

    public function simpan()
    {
        $this->validate([
            'judul' => 'required|string|max:255',
            'video' => 'required|file|mimetypes:video/mp4,video/webm,video/ogg,video/x-msvideo|max:204800',
        ], [
            'video.required' => 'Pilih file video terlebih dahulu.',
            'video.mimetypes' => 'Format video harus MP4, WebM, OGG, atau AVI.',
            'video.max' => 'Ukuran video maksimal 200MB.',
        ]);

        $fileName = time() . '_' . $this->video->getClientOriginalName();
        $filePath = $this->video->storeAs('media', $fileName, 'public');

        MediaModel::create([
            'judul' => $this->judul,
            'file_path' => $filePath,
            'file_name' => $fileName,
            'mime_type' => $this->video->getMimeType(),
        ]);

        $this->reset(['video', 'judul']);

        session()->flash('sukses', 'Video berhasil diupload.');
    }

    public function setAktif(MediaModel $media)
    {
        MediaModel::where('aktif', true)->update(['aktif' => false]);
        $media->update(['aktif' => true]);

        session()->flash('sukses', "Video \"{$media->judul}\" sekarang aktif ditampilkan di kiosk.");
    }

    public function nonaktifkan(MediaModel $media)
    {
        $media->update(['aktif' => false]);

        session()->flash('sukses', "Video \"{$media->judul}\" dinonaktifkan.");
    }

    public function hapus(MediaModel $media)
    {
        Storage::disk('public')->delete($media->file_path);
        $media->delete();

        session()->flash('sukses', 'Video berhasil dihapus.');
    }
}
