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

    public function antrians(): HasMany
    {
        return $this->hasMany(Antrian::class);
    }
}
