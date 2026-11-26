<?php

namespace Database\Factories;

use App\Models\Kategori;
use App\Models\Produk;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Produk>
 */
class ProdukFactory extends Factory
{
    protected $model = Produk::class;

    public function definition(): array
    {
        $nama = fake()->unique()->words(3, true);

        return [
            'kategori_id' => Kategori::factory(),
            'nama' => Str::title($nama),
            'slug' => Str::slug($nama) . '-' . fake()->unique()->numerify('####'),
            'deskripsi' => fake()->sentence(12),
            'harga' => fake()->numberBetween(10000, 500000),
            'stok' => fake()->numberBetween(0, 50),
            'gambar' => null,
            'aktif' => true,
        ];
    }
}
