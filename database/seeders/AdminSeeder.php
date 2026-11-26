<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        // 'role' tidak ada di $fillable, jadi di-set eksplisit di sini
        // (hanya seeder yang boleh; form tidak bisa).
        $admin = new User([
            'name' => 'Admin Beli.',
            'email' => 'admin@beli.test',
            'password' => Hash::make('password'),
            'no_hp' => '081200000001',
            'alamat' => 'Kantor Pusat Beli., Jakarta',
        ]);
        $admin->role = 'admin';
        $admin->save();
    }
}
