<?php

namespace Tests\Feature;

use App\Enums\StatusPesanan;
use App\Models\Alamat;
use App\Models\Keranjang;
use App\Models\Pesanan;
use App\Models\Produk;
use App\Models\ProdukVariasi;
use App\Models\UlasanProduk;
use App\Models\User;
use App\Models\Wishlist;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FiturPelangganTest extends TestCase
{
    use RefreshDatabase;

    public function test_pelanggan_dapat_mengelola_favorit_dan_mengirim_ulasan_pesanan_selesai(): void
    {
        Storage::fake('public');
        $user = User::factory()->pelanggan()->create();
        $produk = Produk::factory()->create(['stok' => 5]);
        $pesanan = Pesanan::factory()->create([
            'user_id' => $user->id,
            'status' => StatusPesanan::Selesai,
        ]);
        $pesanan->detailPesanan()->create([
            'produk_id' => $produk->id,
            'nama_produk' => $produk->nama,
            'harga_satuan' => $produk->harga,
            'jumlah' => 1,
            'subtotal' => $produk->harga,
        ]);

        $this->actingAs($user)
            ->post(route('produk.favorit', $produk))
            ->assertSessionHas('sukses');

        $this->actingAs($user)
            ->get(route('favorit.index'))
            ->assertOk()
            ->assertSee($produk->nama);

        $this->actingAs($user)
            ->get(route('produk.show', $produk))
            ->assertOk()
            ->assertSee('Beli Sekarang')
            ->assertSee('Tambah ke Keranjang')
            ->assertSee('Bagikan Pengalaman Anda');

        $this->actingAs($user)
            ->post(route('produk.ulasan', $produk), [
                'pesanan_id' => $pesanan->id,
                'rating' => 4,
                'komentar' => 'Sesuai deskripsi.',
                'foto' => [
                    $this->fotoPng('produk-1.png'),
                    $this->fotoPng('produk-2.png'),
                ],
            ])
            ->assertSessionHas('sukses');

        $ulasan = \App\Models\UlasanProduk::where('user_id', $user->id)
            ->where('produk_id', $produk->id)
            ->where('pesanan_id', $pesanan->id)
            ->firstOrFail();

        $this->assertDatabaseHas('ulasan_produks', [
            'user_id' => $user->id,
            'produk_id' => $produk->id,
            'pesanan_id' => $pesanan->id,
            'rating' => 4,
        ]);
        $this->assertCount(2, $ulasan->foto);
        foreach ($ulasan->foto as $foto) {
            Storage::disk('public')->assertExists($foto);
        }

        $this->actingAs($user)
            ->get(route('produk.show', $produk))
            ->assertOk()
            ->assertSee($user->name)
            ->assertSee($ulasan->created_at->format('d/m/Y'))
            ->assertSee('Foto ulasan oleh ' . $user->name);

        $fotoLama = $ulasan->foto;
        $this->actingAs($user)
            ->post(route('produk.ulasan', $produk), [
                'pesanan_id' => $pesanan->id,
                'rating' => 5,
                'komentar' => 'Foto diperbarui.',
            ])
            ->assertSessionHas('sukses');

        $this->assertSame($fotoLama, $ulasan->fresh()->foto);

        $this->actingAs($user)
            ->post(route('produk.ulasan', $produk), [
                'pesanan_id' => $pesanan->id,
                'rating' => 5,
                'komentar' => 'Foto terbaru.',
                'foto' => [$this->fotoPng('produk-baru.png')],
            ])
            ->assertSessionHas('sukses');

        $fotoTerbaru = $ulasan->fresh()->foto;
        $this->assertCount(1, $fotoTerbaru);
        Storage::disk('public')->assertExists($fotoTerbaru[0]);
        foreach ($fotoLama as $foto) {
            Storage::disk('public')->assertMissing($foto);
        }

        $this->actingAs($user)
            ->post(route('produk.favorit', $produk))
            ->assertSessionHas('sukses');

        $this->assertDatabaseMissing('wishlists', [
            'user_id' => $user->id,
            'produk_id' => $produk->id,
        ]);
    }

    public function test_ulasan_menolak_lebih_dari_tiga_foto(): void
    {
        Storage::fake('public');
        $user = User::factory()->pelanggan()->create();
        $produk = Produk::factory()->create();
        $pesanan = Pesanan::factory()->create([
            'user_id' => $user->id,
            'status' => StatusPesanan::Selesai,
        ]);
        $pesanan->detailPesanan()->create([
            'produk_id' => $produk->id,
            'nama_produk' => $produk->nama,
            'harga_satuan' => $produk->harga,
            'jumlah' => 1,
            'subtotal' => $produk->harga,
        ]);

        $this->actingAs($user)
            ->post(route('produk.ulasan', $produk), [
                'pesanan_id' => $pesanan->id,
                'rating' => 5,
                'foto' => [
                    $this->fotoPng('satu.png'),
                    $this->fotoPng('dua.png'),
                    $this->fotoPng('tiga.png'),
                    $this->fotoPng('empat.png'),
                ],
            ])
            ->assertSessionHasErrors('foto');

        $this->assertDatabaseMissing('ulasan_produks', [
            'user_id' => $user->id,
            'produk_id' => $produk->id,
            'pesanan_id' => $pesanan->id,
        ]);
        $this->assertSame([], Storage::disk('public')->allFiles('ulasan'));
    }

    public function test_checkout_menggunakan_alamat_tersimpan_milik_pelanggan(): void
    {
        $user = User::factory()->pelanggan()->create();
        $pemilikLain = User::factory()->pelanggan()->create();
        $alamat = Alamat::create([
            'user_id' => $user->id,
            'label' => 'Rumah',
            'nama_penerima' => 'Penerima Tersimpan',
            'no_hp' => '081234567890',
            'alamat_pengiriman' => 'Jalan Tersimpan 10',
            'utama' => true,
        ]);
        $alamatOrangLain = Alamat::create([
            'user_id' => $pemilikLain->id,
            'label' => 'Kantor',
            'nama_penerima' => 'Milik Orang Lain',
            'no_hp' => '081234567891',
            'alamat_pengiriman' => 'Jalan Rahasia 20',
            'utama' => true,
        ]);
        $produk = Produk::factory()->create(['stok' => 3, 'harga' => 10000]);
        Keranjang::create(['user_id' => $user->id, 'produk_id' => $produk->id, 'jumlah' => 1]);

        $this->actingAs($user)
            ->post(route('checkout.store'), ['alamat_id' => $alamat->id])
            ->assertRedirect();

        $pesanan = Pesanan::where('user_id', $user->id)->firstOrFail();
        $this->assertSame('Penerima Tersimpan', $pesanan->nama_penerima);
        $this->assertSame('Jalan Tersimpan 10', $pesanan->alamat_pengiriman);

        Keranjang::create(['user_id' => $user->id, 'produk_id' => $produk->id, 'jumlah' => 1]);
        $jumlahPesanan = Pesanan::count();

        $this->actingAs($user)
            ->post(route('checkout.store'), ['alamat_id' => $alamatOrangLain->id])
            ->assertSessionHasErrors('alamat_id');

        $this->assertSame($jumlahPesanan, Pesanan::count());
    }

    public function test_alamat_pertama_menjadi_utama_dan_penghapusan_memindahkan_default(): void
    {
        $user = User::factory()->pelanggan()->create();
        $dataAlamat = [
            'label' => 'Rumah',
            'nama_penerima' => 'Penerima',
            'no_hp' => '081234567890',
            'alamat_pengiriman' => 'Jalan Contoh 1',
        ];

        $this->actingAs($user)->post(route('profil.alamat.store'), $dataAlamat)->assertSessionHas('sukses');
        $alamatUtama = Alamat::where('user_id', $user->id)->firstOrFail();
        $this->assertTrue($alamatUtama->utama);

        $this->actingAs($user)->post(route('profil.alamat.store'), [
            ...$dataAlamat,
            'label' => 'Kantor',
            'alamat_pengiriman' => 'Jalan Contoh 2',
        ])->assertSessionHas('sukses');
        $alamatKedua = Alamat::where('user_id', $user->id)->where('label', 'Kantor')->firstOrFail();

        $this->actingAs($user)
            ->patch(route('profil.alamat.update', $alamatKedua), [...$dataAlamat, 'utama' => 1])
            ->assertSessionHas('sukses');

        $this->assertFalse($alamatUtama->fresh()->utama);
        $this->assertTrue($alamatKedua->fresh()->utama);

        $this->actingAs($user)
            ->delete(route('profil.alamat.destroy', $alamatKedua))
            ->assertSessionHas('sukses');

        $this->assertTrue($alamatUtama->fresh()->utama);
    }

    public function test_katalog_menampilkan_rating_dan_total_unit_terjual_dari_pesanan_selesai(): void
    {
        $produk = Produk::factory()->create();
        $pelanggan = User::factory()->pelanggan()->create();

        foreach ([
            [StatusPesanan::Selesai, 3, 5],
            [StatusPesanan::Selesai, 2, 4],
            [StatusPesanan::MenungguPembayaran, 7, null],
            [StatusPesanan::Dibatalkan, 4, null],
        ] as [$status, $jumlah, $rating]) {
            $pesanan = Pesanan::factory()->create([
                'user_id' => $pelanggan->id,
                'status' => $status,
            ]);
            $pesanan->detailPesanan()->create([
                'produk_id' => $produk->id,
                'nama_produk' => $produk->nama,
                'harga_satuan' => $produk->harga,
                'jumlah' => $jumlah,
                'subtotal' => $produk->harga * $jumlah,
            ]);

            if ($rating !== null) {
                UlasanProduk::create([
                    'user_id' => $pelanggan->id,
                    'produk_id' => $produk->id,
                    'pesanan_id' => $pesanan->id,
                    'rating' => $rating,
                ]);
            }
        }

        $produkBelumDinilai = Produk::factory()->create([
            'kategori_id' => $produk->kategori_id,
            'stok' => 0,
        ]);
        $produkDetail = Produk::factory()->create(['kategori_id' => $produk->kategori_id]);

        $this->get(route('katalog'))
            ->assertOk()
            ->assertSee($produk->nama)
            ->assertSee('4,5')
            ->assertSee('2 ulasan')
            ->assertSee('Terjual 5')
            ->assertSee($produkBelumDinilai->nama)
            ->assertSee('Belum ada ulasan')
            ->assertSee('Terjual 0');

        $this->get(route('produk.show', $produkDetail))
            ->assertOk()
            ->assertSee($produk->nama)
            ->assertSee('Rating rata-rata 4,5 dari 5 berdasarkan 2 ulasan')
            ->assertSee('Terjual 5')
            ->assertSee(sprintf('%d tersedia', $produk->stok))
            ->assertSee($produkBelumDinilai->nama)
            ->assertSee('Belum ada ulasan')
            ->assertSee('Terjual 0')
            ->assertSee('Stok Habis')
            ->assertSee('Habis');
    }

    public function test_detail_produk_menampilkan_galeri_foto_dan_lightbox(): void
    {
        $produk = Produk::factory()->create([
            'gambar' => 'produk/tampak-depan.png',
            'gambar_tambahan' => ['produk/tampak-samping.png'],
        ]);

        $this->get(route('produk.show', $produk))
            ->assertOk()
            ->assertSee('data-gallery-strip', false)
            ->assertSee('data-gallery-strip-scroll="next"', false)
            ->assertSee('role="dialog"', false)
            ->assertSee('data-gallery-prev', false)
            ->assertSee('data-gallery-next', false)
            ->assertSee('Tampilkan foto 2 dari 2')
            ->assertSee('Semua Foto (2)');
    }

    public function test_detail_produk_menampilkan_pilihan_ukuran_warna_dan_tabel_ukuran(): void
    {
        $user = User::factory()->pelanggan()->create();
        $produk = Produk::factory()->create([
            'stok' => 5,
            'panduan_ukuran' => [
                ['ukuran' => '36', 'panduan' => 'Panjang kaki 23 cm'],
            ],
        ]);
        ProdukVariasi::create([
            'produk_id' => $produk->id,
            'ukuran' => '36',
            'warna' => 'Black Rainbow',
            'stok' => 2,
        ]);
        ProdukVariasi::create([
            'produk_id' => $produk->id,
            'ukuran' => '37',
            'warna' => 'Black Rainbow',
            'stok' => 3,
        ]);

        $this->actingAs($user)
            ->get(route('produk.show', $produk))
            ->assertOk()
            ->assertSee('data-variant-picker', false)
            ->assertSee('data-variant-choice="ukuran"', false)
            ->assertSee('data-variant-choice="warna"', false)
            ->assertSee('Tabel Ukuran')
            ->assertSee('Black Rainbow')
            ->assertSee('Panjang kaki 23 cm');
    }

    public function test_pagination_menandai_halaman_aktif_dengan_warna_brand_dan_halaman_lain_dengan_outline(): void
    {
        Produk::factory()->count(14)->create();

        $this->get(route('katalog', ['page' => 2]))
            ->assertOk()
            ->assertSee('aria-current="page"', false)
            ->assertSee('border border-brand bg-brand px-3 py-2 text-sm font-semibold text-white', false)
            ->assertSee('border border-brand bg-transparent px-3 py-2 text-sm font-semibold text-brand', false)
            ->assertSee('Menampilkan')
            ->assertSee('sampai')
            ->assertSee('>13</span>', false)
            ->assertSee('>14</span> hasil', false);
    }

    private function fotoPng(string $nama): UploadedFile
    {
        return UploadedFile::fake()->createWithContent(
            $nama,
            base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAIAAACQd1PeAAAADUlEQVR4nGP4z8AAAAMBAQDJ/pLvAAAAAElFTkSuQmCC')
        );
    }
}
