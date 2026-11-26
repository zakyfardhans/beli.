<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            AdminSeeder::class,
            PelangganSeeder::class,
            KategoriSeeder::class,
            ProdukSeeder::class,
            PesananSeeder::class,
            KuponSeeder::class,
        ]);
    }
}
