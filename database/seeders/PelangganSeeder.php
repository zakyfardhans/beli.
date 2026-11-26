<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class PelangganSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            ['name' => 'Zaky Fardhan', 'email' => 'zaky@beli.test', 'no_hp' => '081211111111', 'alamat' => 'Jl. Merdeka No. 1, Jakarta'],
            ['name' => 'Rina Lestari', 'email' => 'rina@beli.test', 'no_hp' => '081222222222', 'alamat' => 'Jl. Sudirman No. 22, Jakarta'],
            ['name' => 'Budi Santoso', 'email' => 'budi@beli.test', 'no_hp' => '081233333333', 'alamat' => 'Jl. Thamrin No. 33, Jakarta'],
        ];

        foreach ($data as $row) {
            User::create([
                ...$row,
                'password' => Hash::make('password'),
                'role' => 'pelanggan',
            ]);
        }
    }
}
