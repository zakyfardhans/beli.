<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $keranjangHasVariant = Schema::hasColumn('keranjang', 'produk_variasi_id');
        $keranjangHasKey = Schema::hasColumn('keranjang', 'variasi_key');
        $keranjangHasLegacyUnique = Schema::hasIndex(
            'keranjang',
            ['user_id', 'produk_id'],
            'unique',
        );
        $keranjangHasVariantUnique = Schema::hasIndex(
            'keranjang',
            ['user_id', 'produk_id', 'variasi_key'],
            'unique',
        );

        Schema::table('keranjang', function (Blueprint $table) use (
            $keranjangHasVariant,
            $keranjangHasKey,
            $keranjangHasLegacyUnique,
            $keranjangHasVariantUnique,
        ) {
            if (! $keranjangHasVariant) {
                $table->foreignId('produk_variasi_id')->nullable()
                    ->constrained('produk_variasi')->nullOnDelete();
            }

            if (! $keranjangHasKey) {
                $table->string('variasi_key', 100)->default('utama');
            }

            if (! $keranjangHasVariantUnique) {
                $table->unique(['user_id', 'produk_id', 'variasi_key'], 'keranjang_user_produk_variasi_unique');
            }

            if ($keranjangHasLegacyUnique) {
                $table->dropUnique(['user_id', 'produk_id']);
            }
        });

        Schema::table('detail_pesanan', function (Blueprint $table) {
            if (! Schema::hasColumn('detail_pesanan', 'produk_variasi_id')) {
                $table->foreignId('produk_variasi_id')->nullable()
                    ->constrained('produk_variasi')->nullOnDelete();
            }

            if (! Schema::hasColumn('detail_pesanan', 'ukuran')) {
                $table->string('ukuran', 40)->nullable();
            }

            if (! Schema::hasColumn('detail_pesanan', 'warna')) {
                $table->string('warna', 60)->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('detail_pesanan', function (Blueprint $table) {
            $table->dropConstrainedForeignId('produk_variasi_id');
            $table->dropColumn(['ukuran', 'warna']);
        });

        Schema::table('keranjang', function (Blueprint $table) {
            $table->dropUnique('keranjang_user_produk_variasi_unique');
            $table->dropConstrainedForeignId('produk_variasi_id');
            $table->dropColumn('variasi_key');
            $table->unique(['user_id', 'produk_id']);
        });
    }
};
