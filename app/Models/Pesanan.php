<?php

namespace App\Models;

use App\Enums\StatusPesanan;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pesanan extends Model
{
    use HasFactory;

    protected $table = 'pesanan';

    protected $fillable = [
        'kode_pesanan',
        'user_id',
        'nama_penerima',
        'no_hp',
        'alamat_pengiriman',
        'subtotal',
        'ongkir',
        'total',
        'status',
        'bukti_bayar',
        'no_resi',
        'kode_kupon',
        'diskon',
        'subtotal_setelah_diskon',
        'qr_token',
        'metode_pembayaran',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'integer',
            'ongkir' => 'integer',
            'total' => 'integer',
            'diskon' => 'integer',
            'subtotal_setelah_diskon' => 'integer',
            'status' => StatusPesanan::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function detailPesanan(): HasMany
    {
        return $this->hasMany(DetailPesanan::class);
    }

    public function getRouteKeyName(): string
    {
        return 'kode_pesanan';
    }
}
