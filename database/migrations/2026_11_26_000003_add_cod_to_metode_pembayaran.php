<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE pesanan MODIFY COLUMN metode_pembayaran ENUM('transfer', 'qris', 'cod') DEFAULT 'transfer' AFTER qr_token");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE pesanan MODIFY COLUMN metode_pembayaran ENUM('transfer', 'qris') DEFAULT 'transfer' AFTER qr_token");
    }
};