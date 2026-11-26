<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Keranjang extends Model
{
    use HasFactory;

    protected $table = 'keranjang';

    protected $fillable = ['user_id', 'produk_id', 'produk_variasi_id', 'variasi_key', 'jumlah'];

    protected function casts(): array
    {
        return [
            'jumlah' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function produk(): BelongsTo
    {
        return $this->belongsTo(Produk::class);
    }

    public function variasi(): BelongsTo
    {
        return $this->belongsTo(ProdukVariasi::class, 'produk_variasi_id');
    }

    public function getSubtotalAttribute(): int
    {
        return (int) $this->produk->harga * $this->jumlah;
    }
}
