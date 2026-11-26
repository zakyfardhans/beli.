<?php

namespace Database\Seeders;

use App\Models\Kategori;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class KategoriSeeder extends Seeder
{
    public function run(): void
    {
        $kategori = ['Elektronik', 'Fashion', 'Rumah Tangga', 'Kecantikan'];

        foreach ($kategori as $nama) {
            Kategori::create([
                'nama' => $nama,
                'slug' => Str::slug($nama),
            ]);
        }
    }
}
