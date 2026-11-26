<?php

namespace Tests\Feature;

use App\Enums\StatusPesanan;
use App\Models\Pesanan;
use App\Models\Produk;
use App\Models\ProdukVariasi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OtorisasiDanStatusTest extends TestCase
{
    use RefreshDatabase;

    /** Skenario #6: pelanggan A akses pesanan B -> 403. */
    public function test_pelanggan_tidak_bisa_akses_pesanan_orang_lain(): void
    {
        $a = User::factory()->pelanggan()->create();
        $b = User::factory()->pelanggan()->create();
        $pesananB = Pesanan::factory()->create(['user_id' => $b->id]);

        $this->actingAs($a)
            ->get("/pesanan/{$pesananB->kode_pesanan}")
            ->assertForbidden();

        $this->actingAs($b)
            ->get("/pesanan/{$pesananB->kode_pesanan}")
            ->assertOk();
    }

    /** Skenario #7: transisi menunggu_pembayaran -> dikirim ditolak. */
    public function test_transisi_status_ilegal_ditolak(): void
    {
        $admin = User::factory()->admin()->create();
        $pesanan = Pesanan::factory()->create(['status' => StatusPesanan::MenungguPembayaran]);

        $this->actingAs($admin)
            ->patch("/admin/pesanan/{$pesanan->kode_pesanan}/status", ['status' => 'dikirim', 'no_resi' => 'RESI-1'])
            ->assertSessionHas('galat');

        $this->assertSame(StatusPesanan::MenungguPembayaran, $pesanan->fresh()->status);
    }

    /** Skenario #8: batalkan menunggu_pembayaran -> status batal, stok kembali. */
    public function test_pembatalan_mengembalikan_stok(): void
    {
        $user = User::factory()->pelanggan()->create();
        $produk = Produk::factory()->create(['stok' => 10, 'harga' => 10000]);
        $pesanan = Pesanan::factory()->create([
            'user_id' => $user->id,
            'status' => StatusPesanan::MenungguPembayaran,
            'subtotal' => 20000,
            'ongkir' => 15000,
            'total' => 35000,
        ]);
        $pesanan->detailPesanan()->create([
            'produk_id' => $produk->id,
            'nama_produk' => $produk->nama,
            'harga_satuan' => 10000,
            'jumlah' => 2,
            'subtotal' => 20000,
        ]);

        $this->actingAs($user)
            ->patch("/pesanan/{$pesanan->kode_pesanan}/batal")
            ->assertSessionHas('sukses');

        $this->assertSame(StatusPesanan::Dibatalkan, $pesanan->fresh()->status);
        $this->assertSame(12, $produk->fresh()->stok);

        $this->actingAs($user)
            ->patch("/pesanan/{$pesanan->kode_pesanan}/batal")
            ->assertSessionHas('galat');

        $this->assertSame(12, $produk->fresh()->stok);
    }

    public function test_pembatalan_admin_mengembalikan_stok_variasi_yang_dipesan(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->pelanggan()->create();
        $produk = Produk::factory()->create(['stok' => 3, 'harga' => 10000]);
        $variasi = ProdukVariasi::create([
            'produk_id' => $produk->id,
            'ukuran' => '40',
            'warna' => 'Hitam',
            'stok' => 3,
        ]);
        $pesanan = Pesanan::factory()->create([
            'user_id' => $user->id,
            'status' => StatusPesanan::MenungguPembayaran,
            'subtotal' => 10000,
            'ongkir' => 15000,
            'total' => 25000,
        ]);
        $pesanan->detailPesanan()->create([
            'produk_id' => $produk->id,
            'produk_variasi_id' => $variasi->id,
            'nama_produk' => $produk->nama,
            'ukuran' => '40',
            'warna' => 'Hitam',
            'harga_satuan' => 10000,
            'jumlah' => 1,
            'subtotal' => 10000,
        ]);
        $variasi->decrement('stok');
        $produk->decrement('stok');

        $this->actingAs($admin)
            ->patch("/admin/pesanan/{$pesanan->kode_pesanan}/status", ['status' => 'dibatalkan'])
            ->assertSessionHas('sukses');

        $this->assertSame(3, $variasi->fresh()->stok);
        $this->assertSame(3, $produk->fresh()->stok);
    }

    /** Skenario #9: registrasi dengan role=admin tetap pelanggan. */
    public function test_registrasi_tidak_bisa_mengatur_role(): void
    {
        $this->post('/daftar', [
            'name' => 'Penyusup',
            'email' => 'penyusup@beli.test',
            'no_hp' => '081299999999',
            'alamat' => 'Jl. Tes',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'admin',
        ]);

        $user = User::where('email', 'penyusup@beli.test')->first();

        $this->assertNotNull($user);
        $this->assertSame('pelanggan', $user->role);
    }

    public function test_pelanggan_bisa_mengonfirmasi_pesanan_dikirim(): void
    {
        $user = User::factory()->pelanggan()->create();
        $pesanan = Pesanan::factory()->create([
            'user_id' => $user->id,
            'status' => StatusPesanan::Dikirim,
        ]);

        $this->actingAs($user)
            ->patch("/pesanan/{$pesanan->kode_pesanan}/terima")
            ->assertSessionHas('sukses');

        $this->assertSame(StatusPesanan::Selesai, $pesanan->fresh()->status);
    }
}
