<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Layanan extends Model
{
    protected $fillable = ['poli_id', 'nama', 'kategori', 'harga', 'aktif'];

    protected $casts = [
        'harga' => 'integer',
        'aktif' => 'boolean',
    ];

    public function poli(): BelongsTo
    {
        return $this->belongsTo(Poli::class);
    }
}
