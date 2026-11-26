<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kupon extends Model
{
    use HasFactory;

    protected $table = 'kupons';
    protected $fillable = ['kode', 'persentase', 'kuota', 'terpakai', 'kedaluwarsa', 'aktif'];
    protected function casts(): array
    {
        return ['kedaluwarsa' => 'datetime', 'aktif' => 'boolean'];
    }
}
