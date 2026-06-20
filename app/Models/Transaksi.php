<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Transaksi extends Model
{
    use HasFactory;

    protected $fillable = [
        'pasien_id', 'antrian_id', 'deskripsi', 'jumlah', 'metode', 'tanggal',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    public function pasien(): BelongsTo
    {
        return $this->belongsTo(Pasien::class);
    }

    public function antrian(): BelongsTo
    {
        return $this->belongsTo(Antrian::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(TransaksiItem::class);
    }
}