<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StokMutasi extends Model
{
    protected $table = 'stok_mutasis';
    protected $fillable = [
        'produk_id',
        'jenis',
        'perubahan',
        'stok_sesudah',
        'keterangan',
        'user_id',
    ];
}
