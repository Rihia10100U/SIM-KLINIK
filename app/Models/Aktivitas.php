<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Aktivitas extends Model
{
    protected $table = 'aktivitas';

    protected $fillable = ['tipe', 'pesan', 'waktu'];

    protected $casts = [
        'waktu' => 'datetime',
    ];

    /**
     * Mapping tipe aktivitas -> warna titik (dot) di dashboard.
     * Supaya warna tidak hardcode di view/seeder.
     */
    public function warnaBadge(): string
    {
        return match ($this->tipe) {
            'success' => 'bg-green-500',
            'info'    => 'bg-blue-500',
            'warning' => 'bg-amber-500',
            'danger'  => 'bg-red-500',
            default   => 'bg-gray-400',
        };
    }
}
