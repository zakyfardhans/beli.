<?php

namespace Database\Seeders;

use App\Models\Kategori;
use App\Models\Produk;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProdukSeeder extends Seeder
{
    public function run(): void
    {
        $kategori = Kategori::pluck('id', 'slug');

        // [nama, kategori_slug, harga, stok, deskripsi]
        $produk = [
            ['TWS Bluetooth Pro', 'elektronik', 349000, 42, 'Earbud TWS dengan noise cancelling aktif dan baterai 30 jam.'],
            ['Smartwatch Sport', 'elektronik', 599000, 18, 'Jam pintar AMOLED, tahan air, pemantau detak jantung.'],
            ['Powerbank 20000mAh', 'elektronik', 259000, 0, 'Powerbank fast charging 22.5W dua output USB-C.'],
            ['Kabel USB-C 1m', 'elektronik', 39000, 4, 'Kabel data & charging USB-C anyaman nilon 1 meter.'],
            ['Speaker Bluetooth Mini', 'elektronik', 189000, 27, 'Speaker portabel tahan air IPX5, baterai 12 jam.'],
            ['Sepatu Sneakers Pria', 'fashion', 429000, 31, 'Sneakers kasual ringan dengan sol karet anti-slip.'],
            ['Kaos Oversize Premium', 'fashion', 129000, 5, 'Kaos cotton combed 24s potongan oversize unisex.'],
            ['Tas Ransel Laptop', 'fashion', 279000, 22, 'Ransel anti air dengan kompartemen laptop 15 inci.'],
            ['Jaket Bomber Wanita', 'fashion', 259000, 0, 'Jaket bomber bahan parasut, lining lembut.'],
            ['Tumbler Stainless 500ml', 'rumah-tangga', 89000, 64, 'Tumbler vakum stainless steel, tahan panas/dingin 12 jam.'],
            ['Lampu Meja LED', 'rumah-tangga', 149000, 12, 'Lampu meja LED 3 mode cahaya, dapat diatur kecerahannya.'],
            ['Set Wadah Makan 3pcs', 'rumah-tangga', 79000, 3, 'Wadah makan food-grade, aman microwave, isi 3 ukuran.'],
            ['Serum Vitamin C 20ml', 'kecantikan', 119000, 40, 'Serum wajah vitamin C mencerahkan dan melembapkan.'],
            ['Lip Cream Matte', 'kecantikan', 69000, 0, 'Lip cream matte tahan lama, ringan di bibir.'],
        ];

        foreach ($produk as [$nama, $slugKat, $harga, $stok, $deskripsi]) {
            Produk::create([
                'kategori_id' => $kategori[$slugKat],
                'nama' => $nama,
                'slug' => Str::slug($nama) . '-' . Str::lower(Str::random(4)),
                'deskripsi' => $deskripsi,
                'harga' => $harga,
                'stok' => $stok,
                'gambar' => null,
                'aktif' => true,
            ]);
        }
    }
}
