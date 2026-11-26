<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('pesanan', function (Blueprint $table) {
            $table->string('kode_kupon', 50)->nullable()->after('total');
            $table->unsignedInteger('diskon')->default(0)->after('kode_kupon');
            $table->unsignedInteger('subtotal_setelah_diskon')->nullable()->after('diskon');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pesanan', function (Blueprint $table) {
            $table->dropColumn(['kode_kupon', 'diskon', 'subtotal_setelah_diskon']);
        });
    }
};
