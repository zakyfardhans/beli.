<?php

namespace App\Models;

use App\Enums\StatusPesanan;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Produk extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'produk';

    protected $fillable = [
        'kategori_id',
        'nama',
        'slug',
        'deskripsi',
        'harga',
        'stok',
        'gambar',
        'gambar_tambahan',
        'panduan_ukuran',
        'aktif',
    ];

    protected function casts(): array
    {
        return [
            'harga' => 'integer',
            'stok' => 'integer',
            'gambar_tambahan' => 'array',
            'panduan_ukuran' => 'array',
            'aktif' => 'boolean',
        ];
    }

    public function gambarGaleris(): array
    {
        return array_values(array_unique(array_filter(
            array_merge([$this->gambar], $this->gambar_tambahan ?? []),
            fn ($gambar) => is_string($gambar) && $gambar !== '',
        )));
    }

    public function kategori(): BelongsTo
    {
        return $this->belongsTo(Kategori::class);
    }

    public function detailPesanan(): HasMany
    {
        return $this->hasMany(DetailPesanan::class);
    }

    public function variasi(): HasMany
    {
        return $this->hasMany(ProdukVariasi::class)->orderBy('ukuran')->orderBy('warna');
    }

    public function cariVariasi(string $ukuran, string $warna): ?ProdukVariasi
    {
        return $this->variasi()
            ->where('ukuran', trim($ukuran))
            ->where('warna', trim($warna))
            ->first();
    }

    public function ulasanProduk(): HasMany
    {
        return $this->hasMany(UlasanProduk::class);
    }

    public function scopeDenganMetrikKatalog(Builder $query): Builder
    {
        return $query
            ->withAvg('ulasanProduk', 'rating')
            ->withCount('ulasanProduk')
            ->withSum([
                'detailPesanan as total_terjual' => fn ($detail) => $detail->whereHas(
                    'pesanan',
                    fn ($pesanan) => $pesanan->where('status', StatusPesanan::Selesai),
                ),
            ], 'jumlah');
    }

    public function scopeAktif($query)
    {
        return $query->where('aktif', true);
    }
}
