<?php

namespace Database\Factories;

use App\Models\DetailPesanan;
use App\Models\Pesanan;
use App\Models\Produk;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DetailPesanan>
 */
class DetailPesananFactory extends Factory
{
    protected $model = DetailPesanan::class;

    public function definition(): array
    {
        $produk = Produk::factory()->create();
        $jumlah = fake()->numberBetween(1, 3);
        $subtotal = $produk->harga * $jumlah;

        return [
            'pesanan_id' => Pesanan::factory(),
            'produk_id' => $produk->id,
            'nama_produk' => $produk->nama,
            'harga_satuan' => $produk->harga,
            'jumlah' => $jumlah,
            'subtotal' => $subtotal,
        ];
    }
}
