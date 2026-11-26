<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('produk', function (Blueprint $table) {
            $table->json('panduan_ukuran')->nullable();
        });

        Schema::create('produk_variasi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('produk_id')->constrained('produk')->cascadeOnDelete();
            $table->string('ukuran', 40);
            $table->string('warna', 60);
            $table->unsignedInteger('stok')->default(0);
            $table->timestamps();

            $table->unique(['produk_id', 'ukuran', 'warna'], 'produk_variasi_pilihan_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('produk_variasi');

        Schema::table('produk', function (Blueprint $table) {
            $table->dropColumn('panduan_ukuran');
        });
    }
};
