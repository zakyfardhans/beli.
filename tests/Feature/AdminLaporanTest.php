<?php

namespace Tests\Feature;

use App\Models\Pesanan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminLaporanTest extends TestCase
{
    use RefreshDatabase;

    public function test_laporan_csv_menetralkan_nilai_dengan_awalan_formula(): void
    {
        $admin = User::factory()->admin()->create();
        $pelanggan = User::factory()->pelanggan()->create(['name' => '=1+2']);
        Pesanan::factory()->create(['user_id' => $pelanggan->id]);

        $response = $this->actingAs($admin)->get(route('admin.laporan.penjualan'));

        $response->assertOk()->assertDownload('laporan-pesanan.csv');
        $this->assertStringContainsString("'=1+2", $response->streamedContent());
    }
}
