<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Poli extends Model
{
    protected $fillable = ['kode', 'nama', 'aktif'];

    protected $casts = [
        'aktif' => 'boolean',
    ];

    /**
     * Relasi ke model Antrian
     */
    public function antrians(): HasMany
    {
        return $this->hasMany(Antrian::class);
    }

    /**
     * TAMBAHKAN INI: Relasi ke model Layanan
     * Menghubungkan satu poli ke banyak layanan medis didalamnya
     */
    public function layanans(): HasMany
    {
        return $this->hasMany(Layanan::class);
    }
}
