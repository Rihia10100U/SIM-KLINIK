<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class MediaInformasi extends Model
{
    protected $table = 'media_informasis';

    protected $fillable = ['judul', 'file_path', 'file_name', 'mime_type', 'aktif'];

    protected function casts(): array
    {
        return [
            'aktif' => 'boolean',
        ];
    }

    public function url(): string
    {
        return Storage::url($this->file_path);
    }

    public function scopeAktif($query)
    {
        return $query->where('aktif', true);
    }
}
