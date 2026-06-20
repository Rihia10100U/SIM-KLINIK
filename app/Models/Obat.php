<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Obat extends Model
{
    protected $fillable = [
        'kode_obat', 'nama', 'kategori', 'satuan', 'stok', 'stok_minimum', 'harga',
    ];

    protected $casts = [
        'stok'         => 'integer',
        'stok_minimum' => 'integer',
        'harga'        => 'integer',
    ];

    /**
     * Status stok: 'aman', 'menipis', atau 'habis'.
     * Dipakai untuk menentukan warna badge di tabel.
     */
    public function statusStok(): string
    {
        if ($this->stok <= 0) {
            return 'habis';
        }

        if ($this->stok <= $this->stok_minimum) {
            return 'menipis';
        }

        return 'aman';
    }
}
