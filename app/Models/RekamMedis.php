<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RekamMedis extends Model
{
    protected $table = 'rekam_medis';

    protected $fillable = [
        'pasien_id', 'poli_id', 'antrian_id', 'dokter', 'keluhan', 'diagnosis', 'tindakan', 'catatan', 'tanggal_periksa',
    ];

    protected $casts = [
        'tanggal_periksa' => 'date',
    ];

    public function pasien(): BelongsTo
    {
        return $this->belongsTo(Pasien::class);
    }

    public function poli(): BelongsTo
    {
        return $this->belongsTo(Poli::class);
    }

    public function antrian(): BelongsTo
    {
        return $this->belongsTo(Antrian::class);
    }
}
