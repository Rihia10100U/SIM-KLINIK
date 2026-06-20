<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PengaturanKlinik extends Model
{
    protected $table = 'pengaturan_kliniks';

    protected $fillable = ['nama_klinik', 'alamat', 'telepon', 'jam_buka', 'jam_tutup'];
}
