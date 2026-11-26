<?php

namespace Database\Seeders;

use App\Models\Kupon;
use Illuminate\Database\Seeder;

class KuponSeeder extends Seeder
{
    public function run(): void
    {
        Kupon::updateOrCreate(
            ['kode' => 'BELI10'],
            [
                'persentase' => 10,
                'kuota' => 100,
                'terpakai' => 0,
                'kedaluwarsa' => now()->addMonths(3),
                'aktif' => true,
            ]
        );
    }
}
