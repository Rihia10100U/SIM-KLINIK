<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Obat extends Model
{
    protected $fillable = [
        'kode_obat', 'nama', 'kategori', 'satuan', 'stok', 'stok_minimum', 'harga', 'catatan',
    ];

    protected $casts = [
        'stok' => 'integer',
        'stok_minimum' => 'integer',
        'harga' => 'integer',
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

    public function kirimNotifikasiStok(): void
    {
        $status = $this->statusStok();
        if ($status === 'aman') {
            return;
        }

        $label = $status === 'habis' ? 'Stok Habis' : 'Stok Menipis';
        $message = $status === 'habis'
            ? "Stok {$this->nama} sudah habis."
            : "Stok {$this->nama} menipis (sisa {$this->stok} {$this->satuan}).";

        foreach (User::whereIn('role', ['admin', 'apoteker'])->cursor() as $user) {
            Notification::send(
                $user->id,
                $label,
                $message,
                $status === 'habis' ? 'danger' : 'warning',
                route('farmasi'),
            );
        }
    }
}
