<?php

namespace Tests\Feature;

use App\Enums\StatusPesanan;
use App\Exceptions\StokTidakCukupException;
use App\Models\Keranjang;
use App\Models\Kupon;
use App\Models\Pesanan;
use App\Models\Produk;
use App\Models\ProdukVariasi;
use App\Models\User;
use App\Services\CheckoutService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    use RefreshDatabase;

    private function pelanggan(): User
    {
        return User::factory()->pelanggan()->create();
    }

    /** Skenario #4: ongkir 0 bila subtotal >= 500rb, 15rb bila di bawah. */
    public function test_ongkir_gratis_di_atas_lima_ratus_ribu(): void
    {
        $svc = app(CheckoutService::class);

        $this->assertSame(0, $svc->hitungOngkir(520000));
        $this->assertSame(15000, $svc->hitungOngkir(100000));
    }

    /** Skenario #3: stok 1, dua pelanggan checkout bergantian -> kedua gagal, stok tidak negatif. */
    public function test_checkout_kedua_gagal_saat_stok_habis(): void
    {
        $svc = app(CheckoutService::class);
        $a = $this->pelanggan();
        $b = $this->pelanggan();
        $produk = Produk::factory()->create(['stok' => 1, 'harga' => 10000]);

        Keranjang::create(['user_id' => $a->id, 'produk_id' => $produk->id, 'jumlah' => 1]);
        Keranjang::create(['user_id' => $b->id, 'produk_id' => $produk->id, 'jumlah' => 1]);

        $svc->checkout($a, ['nama_penerima' => 'A', 'no_hp' => '081200000000', 'alamat_pengiriman' => 'Alamat A']);
        $this->assertSame(0, $produk->fresh()->stok);

        $jumlahPesananSebelum = Pesanan::count();

        try {
            $svc->checkout($b, ['nama_penerima' => 'B', 'no_hp' => '081200000001', 'alamat_pengiriman' => 'Alamat B']);
            $this->fail('Checkout kedua seharusnya gagal.');
        } catch (StokTidakCukupException $e) {
            $this->assertStringContainsString($produk->nama, $e->getMessage());
        }

        $this->assertSame(0, $produk->fresh()->stok);
        $this->assertSame($jumlahPesananSebelum, Pesanan::count());
        $this->assertSame(1, Keranjang::where('user_id', $b->id)->count());
    }

    /** Skenario #5: snapshot harga tidak berubah saat harga produk diubah. */
    public function test_snapshot_harga_tidak_berubah_setelah_produk_diubah(): void
    {
        $svc = app(CheckoutService::class);
        $user = $this->pelanggan();
        $produk = Produk::factory()->create(['stok' => 10, 'harga' => 5000]);

        Keranjang::create(['user_id' => $user->id, 'produk_id' => $produk->id, 'jumlah' => 2]);

        $pesanan = $svc->checkout($user, ['nama_penerima' => 'X', 'no_hp' => '081200000000', 'alamat_pengiriman' => 'Alamat']);
        $detail = $pesanan->detailPesanan()->first();

        $this->assertSame(5000, $detail->harga_satuan);
        $this->assertSame(StatusPesanan::MenungguPembayaran, $pesanan->status);

        $produk->update(['harga' => 999999]);

        $this->assertSame(5000, $detail->fresh()->harga_satuan);
    }

    public function test_beli_langsung_membuat_pesanan_tanpa_mengubah_keranjang(): void
    {
        $user = $this->pelanggan();
        $produkLangsung = Produk::factory()->create(['stok' => 5, 'harga' => 10000]);
        $produkKeranjang = Produk::factory()->create(['stok' => 4, 'harga' => 25000]);
        $itemKeranjang = Keranjang::create([
            'user_id' => $user->id,
            'produk_id' => $produkKeranjang->id,
            'jumlah' => 2,
        ]);

        $response = $this->actingAs($user)
            ->post(route('checkout.direct'), [
                'produk_id' => $produkLangsung->id,
                'jumlah' => 2,
            ])
            ->assertRedirect();

        $checkoutUrl = $response->headers->get('Location');
        parse_str((string) parse_url($checkoutUrl, PHP_URL_QUERY), $query);
        $this->assertSame('langsung', $query['mode'] ?? null);
        $this->assertArrayHasKey('token', $query);

        $this->get($checkoutUrl)
            ->assertOk()
            ->assertSee($produkLangsung->nama)
            ->assertSee('Pembelian langsung')
            ->assertDontSee($produkKeranjang->nama);

        $this->assertDatabaseMissing('keranjang', [
            'user_id' => $user->id,
            'produk_id' => $produkLangsung->id,
        ]);
        $this->assertSame(1, Keranjang::where('user_id', $user->id)->count());

        $this->post(route('checkout.store'), [
            'mode' => 'langsung',
            'token' => $query['token'],
            'nama_penerima' => 'Pelanggan',
            'no_hp' => '081234567890',
            'alamat_pengiriman' => 'Jalan Contoh',
        ])->assertSessionHas('sukses');

        $pesanan = Pesanan::where('user_id', $user->id)->firstOrFail();
        $detail = $pesanan->detailPesanan()->firstOrFail();
        $this->assertSame($produkLangsung->id, $detail->produk_id);
        $this->assertSame(2, $detail->jumlah);
        $this->assertSame(3, $produkLangsung->fresh()->stok);
        $this->assertSame(4, $produkKeranjang->fresh()->stok);
        $this->assertSame($itemKeranjang->id, Keranjang::where('user_id', $user->id)->firstOrFail()->id);
    }

    public function test_checkout_menyimpan_pilihan_dan_mengembalikan_stok_variasi_saat_dibatalkan(): void
    {
        $user = $this->pelanggan();
        $produk = Produk::factory()->create(['stok' => 5, 'harga' => 10000]);
        $variasiDipilih = ProdukVariasi::create([
            'produk_id' => $produk->id,
            'ukuran' => '36',
            'warna' => 'Black Rainbow',
            'stok' => 2,
        ]);
        $variasiLain = ProdukVariasi::create([
            'produk_id' => $produk->id,
            'ukuran' => '37',
            'warna' => 'Black Rainbow',
            'stok' => 3,
        ]);

        Keranjang::create([
            'user_id' => $user->id,
            'produk_id' => $produk->id,
            'produk_variasi_id' => $variasiDipilih->id,
            'variasi_key' => 'variasi-' . $variasiDipilih->id,
            'jumlah' => 1,
        ]);

        $pesanan = app(CheckoutService::class)->checkout($user, [
            'nama_penerima' => 'Pelanggan',
            'no_hp' => '081234567890',
            'alamat_pengiriman' => 'Jalan Contoh',
        ]);
        $detail = $pesanan->detailPesanan()->firstOrFail();

        $this->assertSame('36', $detail->ukuran);
        $this->assertSame('Black Rainbow', $detail->warna);
        $this->assertSame(1, $variasiDipilih->fresh()->stok);
        $this->assertSame(3, $variasiLain->fresh()->stok);
        $this->assertSame(4, $produk->fresh()->stok);

        $this->actingAs($user)
            ->patch(route('pesanan.batal', $pesanan))
            ->assertSessionHas('sukses');

        $this->assertSame(2, $variasiDipilih->fresh()->stok);
        $this->assertSame(3, $variasiLain->fresh()->stok);
        $this->assertSame(5, $produk->fresh()->stok);
    }

    public function test_beli_langsung_membawa_variasi_yang_dipilih_ke_pesanan(): void
    {
        $user = $this->pelanggan();
        $produk = Produk::factory()->create(['stok' => 4, 'harga' => 15000]);
        $variasi = ProdukVariasi::create([
            'produk_id' => $produk->id,
            'ukuran' => '38',
            'warna' => 'Navy',
            'stok' => 4,
        ]);

        $response = $this->actingAs($user)
            ->post(route('checkout.direct'), [
                'produk_id' => $produk->id,
                'jumlah' => 2,
                'ukuran' => '38',
                'warna' => 'Navy',
            ])
            ->assertRedirect();

        $checkoutUrl = $response->headers->get('Location');
        parse_str((string) parse_url($checkoutUrl, PHP_URL_QUERY), $query);
        $this->get($checkoutUrl)
            ->assertOk()
            ->assertSee('38')
            ->assertSee('Navy');

        $this->post(route('checkout.store'), [
            'mode' => 'langsung',
            'token' => $query['token'],
            'nama_penerima' => 'Pelanggan',
            'no_hp' => '081234567890',
            'alamat_pengiriman' => 'Jalan Contoh',
        ])->assertSessionHas('sukses');

        $detail = Pesanan::where('user_id', $user->id)->firstOrFail()->detailPesanan()->firstOrFail();
        $this->assertSame($variasi->id, $detail->produk_variasi_id);
        $this->assertSame('38', $detail->ukuran);
        $this->assertSame('Navy', $detail->warna);
        $this->assertSame(2, $variasi->fresh()->stok);
    }

    public function test_kupon_ditampilkan_diterapkan_dan_kuotanya_dibatasi(): void
    {
        $user = $this->pelanggan();
        $produk = Produk::factory()->create(['stok' => 3, 'harga' => 100000]);
        Keranjang::create(['user_id' => $user->id, 'produk_id' => $produk->id, 'jumlah' => 1]);
        $kupon = Kupon::create([
            'kode' => 'HEMAT10',
            'persentase' => 10,
            'kuota' => 1,
            'terpakai' => 0,
            'kedaluwarsa' => now()->addDay(),
            'aktif' => true,
        ]);

        $this->actingAs($user)
            ->get(route('checkout.create', ['kode_kupon' => 'hemat10']))
            ->assertOk()
            ->assertSee('Kupon HEMAT10 memberi diskon')
            ->assertSee('10%.');

        $this->actingAs($user)
            ->post(route('checkout.store'), [
                'nama_penerima' => 'Pelanggan',
                'no_hp' => '081234567890',
                'alamat_pengiriman' => 'Jalan Contoh',
                'kode_kupon' => 'hemat10',
            ])
            ->assertSessionHas('sukses');

        $pesanan = Pesanan::where('user_id', $user->id)->firstOrFail();
        $this->assertSame(10000, $pesanan->diskon);
        $this->assertSame(105000, $pesanan->total);
        $this->assertSame(1, $kupon->fresh()->terpakai);

        $pelangganKedua = $this->pelanggan();
        Keranjang::create(['user_id' => $pelangganKedua->id, 'produk_id' => $produk->id, 'jumlah' => 1]);
        $jumlahPesanan = Pesanan::count();

        $this->actingAs($pelangganKedua)
            ->post(route('checkout.store'), [
                'nama_penerima' => 'Pelanggan Kedua',
                'no_hp' => '081234567891',
                'alamat_pengiriman' => 'Jalan Lain',
                'kode_kupon' => 'HEMAT10',
            ])
            ->assertSessionHas('galat');

        $this->assertSame($jumlahPesanan, Pesanan::count());
        $this->assertSame(2, $produk->fresh()->stok);
        $this->assertSame(1, Keranjang::where('user_id', $pelangganKedua->id)->count());
    }
}
