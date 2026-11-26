<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UlasanProduk extends Model
{
    use HasFactory;

    protected $table = 'ulasan_produks';
    protected $fillable = ['user_id', 'produk_id', 'pesanan_id', 'rating', 'komentar', 'foto'];

    protected function casts(): array
    {
        return ['foto' => 'array'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
