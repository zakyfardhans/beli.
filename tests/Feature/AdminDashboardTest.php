<?php

namespace Tests\Feature;

use App\Enums\StatusPesanan;
use App\Models\Pesanan;
use App\Models\Produk;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_omzet_dan_produk_terlaris_tidak_menghitung_pesanan_belum_diproses(): void
    {
        $admin = User::factory()->admin()->create();
        $belumDibayar = Pesanan::factory()->create([
            'status' => StatusPesanan::MenungguPembayaran,
            'total' => 100000,
        ]);
        $terjual = Pesanan::factory()->create([
            'status' => StatusPesanan::Selesai,
            'total' => 200000,
        ]);
        $produkBelumDibayar = Produk::factory()->create(['stok' => 100]);
        $produkTerjual = Produk::factory()->create(['stok' => 100]);

        $belumDibayar->detailPesanan()->create([
            'produk_id' => $produkBelumDibayar->id,
            'nama_produk' => $produkBelumDibayar->nama,
            'harga_satuan' => $produkBelumDibayar->harga,
            'jumlah' => 9,
            'subtotal' => $produkBelumDibayar->harga * 9,
        ]);
        $terjual->detailPesanan()->create([
            'produk_id' => $produkTerjual->id,
            'nama_produk' => $produkTerjual->nama,
            'harga_satuan' => $produkTerjual->harga,
            'jumlah' => 2,
            'subtotal' => $produkTerjual->harga * 2,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Rp200.000')
            ->assertSee($produkTerjual->nama)
            ->assertDontSee('Rp300.000')
            ->assertDontSee($produkBelumDibayar->nama);
    }
}
