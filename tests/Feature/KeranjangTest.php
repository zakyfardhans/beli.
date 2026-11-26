<?php

namespace Tests\Feature;

use App\Models\Keranjang;
use App\Models\Produk;
use App\Models\ProdukVariasi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KeranjangTest extends TestCase
{
    use RefreshDatabase;

    private function pelanggan(): User
    {
        return User::factory()->pelanggan()->create();
    }

    /** Skenario #1: tambah produk sama 2x -> 1 baris, jumlah bertambah. */
    public function test_menambah_produk_sama_dua_kali_hanya_satu_baris(): void
    {
        $user = $this->pelanggan();
        $produk = Produk::factory()->create(['stok' => 10]);

        $this->actingAs($user)->post('/keranjang', ['produk_id' => $produk->id, 'jumlah' => 1]);
        $this->actingAs($user)->post('/keranjang', ['produk_id' => $produk->id, 'jumlah' => 1]);

        $items = Keranjang::where('user_id', $user->id)->where('produk_id', $produk->id)->get();

        $this->assertCount(1, $items);
        $this->assertSame(2, $items->first()->jumlah);
    }

    /** Skenario #2: ubah jumlah melebihi stok ditolak. */
    public function test_ubah_jumlah_melebihi_stok_ditolak(): void
    {
        $user = $this->pelanggan();
        $produk = Produk::factory()->create(['stok' => 5]);
        $item = Keranjang::create(['user_id' => $user->id, 'produk_id' => $produk->id, 'jumlah' => 2]);

        $this->actingAs($user)
            ->patch("/keranjang/{$item->id}", ['jumlah' => 50])
            ->assertSessionHas('galat');

        $this->assertSame(2, $item->fresh()->jumlah);
    }

    public function test_variasi_produk_dipisahkan_di_keranjang_dan_stok_dibatasi_per_kombinasi(): void
    {
        $user = $this->pelanggan();
        $produk = Produk::factory()->create(['stok' => 5]);
        $ukuran36 = ProdukVariasi::create([
            'produk_id' => $produk->id,
            'ukuran' => '36',
            'warna' => 'Black Rainbow',
            'stok' => 2,
        ]);
        $ukuran37 = ProdukVariasi::create([
            'produk_id' => $produk->id,
            'ukuran' => '37',
            'warna' => 'Black Rainbow',
            'stok' => 3,
        ]);

        $this->actingAs($user)
            ->post(route('keranjang.store'), [
                'produk_id' => $produk->id,
                'jumlah' => 1,
                'ukuran' => '36',
                'warna' => 'Black Rainbow',
            ])
            ->assertRedirect();

        $this->actingAs($user)
            ->post(route('keranjang.store'), [
                'produk_id' => $produk->id,
                'jumlah' => 1,
                'ukuran' => '37',
                'warna' => 'Black Rainbow',
            ])
            ->assertRedirect();

        $this->actingAs($user)
            ->post(route('keranjang.store'), [
                'produk_id' => $produk->id,
                'jumlah' => 2,
                'ukuran' => '36',
                'warna' => 'Black Rainbow',
            ])
            ->assertRedirect()
            ->assertSessionHas('galat');

        $items = Keranjang::where('user_id', $user->id)->orderBy('produk_variasi_id')->get();

        $this->assertCount(2, $items);
        $this->assertSame([$ukuran36->id, $ukuran37->id], $items->pluck('produk_variasi_id')->all());
        $this->assertSame([1, 1], $items->pluck('jumlah')->all());
    }
}
