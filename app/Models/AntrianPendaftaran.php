<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AntrianPendaftaran extends Model
{
    protected $table = 'antrian_pendaftarans';

    protected $fillable = ['kode_antrian', 'status', 'pasien_id', 'tanggal'];

    protected $casts = [
        'tanggal' => 'date',
    ];

    public function pasien(): BelongsTo
    {
        return $this->belongsTo(Pasien::class);
    }
}
