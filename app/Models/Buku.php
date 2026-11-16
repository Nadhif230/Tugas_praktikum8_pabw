<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Buku extends Model
{

public function peminjamans()
{
    return $this->hasMany(Peminjaman::class);
}

    protected $fillable = [
        'judul',
        'penulis',
        'penerbit',
        'tahun_terbit',
        'stok',
    ];
}

