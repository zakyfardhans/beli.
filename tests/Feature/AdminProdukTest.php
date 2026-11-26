<?php

namespace Tests\Feature;

use App\Models\Kategori;
use App\Models\Produk;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminProdukTest extends TestCase
{
    use RefreshDatabase;

    public function test_form_admin_mendukung_gambar_banyak_dan_penghapusan_gambar_lama(): void
    {
        $admin = User::factory()->admin()->create();
        $produk = Produk::factory()->create([
            'gambar' => 'produk/utama.png',
            'gambar_tambahan' => ['produk/samping.png'],
        ]);

        $this->actingAs($admin)
            ->get(route('admin.produk.create'))
            ->assertOk()
            ->assertSee('name="gambar[]"', false)
            ->assertSee('multiple', false)
            ->assertSee('maksimal 2 MB per gambar dan 10 gambar total');

        $this->actingAs($admin)
            ->get(route('admin.produk.edit', $produk))
            ->assertOk()
            ->assertSee('Gambar saat ini (2)')
            ->assertSee('name="hapus_gambar[]"', false)
            ->assertSee('produk/utama.png')
            ->assertSee('produk/samping.png');
    }

    public function test_admin_dapat_menambahkan_maksimal_sepuluh_gambar_produk(): void
    {
        Storage::fake('public');
        $admin = User::factory()->admin()->create();
        $kategori = Kategori::factory()->create();
        $gambar = [];

        for ($nomor = 1; $nomor <= 10; $nomor++) {
            $gambar[] = $this->fotoPng("produk-{$nomor}.png");
        }

        $this->actingAs($admin)
            ->post(route('admin.produk.store'), $this->dataProduk($kategori) + ['gambar' => $gambar])
            ->assertRedirect(route('admin.produk.index'))
            ->assertSessionHas('sukses');

        $produk = Produk::firstOrFail();
        $this->assertCount(10, $produk->gambarGaleris());

        foreach ($produk->gambarGaleris() as $path) {
            Storage::disk('public')->assertExists($path);
        }
    }

    public function test_admin_dapat_menambah_dan_menghapus_gambar_tanpa_menghapus_gambar_lain(): void
    {
        Storage::fake('public');
        $admin = User::factory()->admin()->create();
        $produkUtama = 'produk/utama.png';
        $produkTetap = 'produk/tetap.png';
        $produkDihapus = 'produk/dihapus.png';

        foreach ([$produkUtama, $produkTetap, $produkDihapus] as $path) {
            Storage::disk('public')->put($path, 'gambar lama');
        }

        $produk = Produk::factory()->create([
            'gambar' => $produkUtama,
            'gambar_tambahan' => [$produkTetap, $produkDihapus],
        ]);

        $this->actingAs($admin)
            ->put(route('admin.produk.update', $produk), $this->dataProduk($produk->kategori) + [
                'gambar' => [$this->fotoPng('produk-baru.png')],
                'hapus_gambar' => [$produkDihapus],
            ])
            ->assertRedirect(route('admin.produk.index'))
            ->assertSessionHas('sukses');

        $galeri = $produk->fresh()->gambarGaleris();
        $this->assertSame([$produkUtama, $produkTetap], array_slice($galeri, 0, 2));
        $this->assertCount(3, $galeri);
        Storage::disk('public')->assertExists($produkUtama);
        Storage::disk('public')->assertExists($produkTetap);
        Storage::disk('public')->assertExists($galeri[2]);
        Storage::disk('public')->assertMissing($produkDihapus);
    }

    public function test_admin_tidak_dapat_melampaui_batas_sepuluh_gambar(): void
    {
        Storage::fake('public');
        $admin = User::factory()->admin()->create();
        $kategori = Kategori::factory()->create();
        $gambar = [];

        for ($nomor = 1; $nomor <= 11; $nomor++) {
            $gambar[] = $this->fotoPng("produk-{$nomor}.png");
        }

        $this->actingAs($admin)
            ->post(route('admin.produk.store'), $this->dataProduk($kategori) + ['gambar' => $gambar])
            ->assertSessionHasErrors('gambar');

        $this->assertDatabaseCount('produk', 0);
        $this->assertSame([], Storage::disk('public')->allFiles('produk'));
    }

    public function test_penambahan_gambar_ditolak_jika_total_galeri_menjadi_lebih_dari_sepuluh(): void
    {
        Storage::fake('public');
        $admin = User::factory()->admin()->create();
        $gambar = array_map(fn ($nomor) => "produk/gambar-{$nomor}.png", range(1, 10));
        $produk = Produk::factory()->create([
            'gambar' => $gambar[0],
            'gambar_tambahan' => array_slice($gambar, 1),
        ]);

        $this->actingAs($admin)
            ->put(route('admin.produk.update', $produk), $this->dataProduk($produk->kategori) + [
                'gambar' => [$this->fotoPng('produk-baru.png')],
            ])
            ->assertSessionHasErrors('gambar');

        $this->assertSame($gambar, $produk->fresh()->gambarGaleris());
        $this->assertSame([], Storage::disk('public')->allFiles('produk'));
    }

    public function test_admin_dapat_menyimpan_stok_setiap_variasi_dan_tabel_ukuran(): void
    {
        $admin = User::factory()->admin()->create();
        $kategori = Kategori::factory()->create();

        $this->actingAs($admin)
            ->post(route('admin.produk.store'), $this->dataProduk($kategori) + [
                'variasi' => [
                    ['ukuran' => '36', 'warna' => 'Black Rainbow', 'stok' => '3'],
                    ['ukuran' => '37', 'warna' => 'Black Rainbow', 'stok' => '5'],
                ],
                'panduan_ukuran' => [
                    ['ukuran' => '36', 'panduan' => 'Panjang kaki 23 cm'],
                    ['ukuran' => '37', 'panduan' => 'Panjang kaki 23,5 cm'],
                ],
            ])
            ->assertRedirect(route('admin.produk.index'))
            ->assertSessionHas('sukses');

        $produk = Produk::with('variasi')->firstOrFail();
        $this->assertSame(8, $produk->stok);
        $this->assertSame(['36', '37'], $produk->variasi->pluck('ukuran')->all());
        $this->assertSame(['3', '5'], $produk->variasi->pluck('stok')->map(fn ($stok) => (string) $stok)->all());
        $this->assertSame('Panjang kaki 23 cm', $produk->panduan_ukuran[0]['panduan']);

        $this->actingAs($admin)
            ->get(route('admin.produk.edit', $produk))
            ->assertOk()
            ->assertSee('Black Rainbow')
            ->assertSee('Panjang kaki 23 cm');

        [$variasi36, $variasi37] = $produk->variasi;
        $this->actingAs($admin)
            ->put(route('admin.produk.update', $produk), $this->dataProduk($kategori) + [
                'variasi' => [
                    ['id' => $variasi36->id, 'ukuran' => '36', 'warna' => 'Black Rainbow', 'stok' => '2'],
                    ['id' => $variasi37->id, 'ukuran' => '37', 'warna' => 'Black Rainbow', 'stok' => '5'],
                ],
                'panduan_ukuran' => [
                    ['ukuran' => '36', 'panduan' => 'Panjang kaki 23 cm'],
                    ['ukuran' => '37', 'panduan' => 'Panjang kaki 23,5 cm'],
                ],
            ])
            ->assertRedirect(route('admin.produk.index'))
            ->assertSessionHas('sukses');

        $this->assertSame(7, $produk->fresh()->stok);
        $this->assertSame([2, 5], $produk->variasi()->orderBy('ukuran')->pluck('stok')->all());
    }

    public function test_admin_menolak_kombinasi_variasi_duplikat_dan_panduan_ukuran_tidak_tersedia(): void
    {
        $admin = User::factory()->admin()->create();
        $kategori = Kategori::factory()->create();

        $this->actingAs($admin)
            ->post(route('admin.produk.store'), $this->dataProduk($kategori) + [
                'variasi' => [
                    ['ukuran' => '36', 'warna' => 'Hitam', 'stok' => '2'],
                    ['ukuran' => '36', 'warna' => 'Hitam', 'stok' => '4'],
                ],
                'panduan_ukuran' => [
                    ['ukuran' => '40', 'panduan' => 'Panjang kaki 25 cm'],
                ],
            ])
            ->assertSessionHasErrors(['variasi', 'panduan_ukuran']);

        $this->assertDatabaseCount('produk', 0);
    }

    private function dataProduk(Kategori $kategori): array
    {
        return [
            'nama' => 'Sepatu Contoh',
            'kategori_id' => $kategori->id,
            'deskripsi' => 'Deskripsi produk.',
            'harga' => 100000,
            'stok' => 5,
            'aktif' => '1',
        ];
    }

    private function fotoPng(string $nama): UploadedFile
    {
        return UploadedFile::fake()->createWithContent(
            $nama,
            base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAIAAACQd1PeAAAADUlEQVR4nGP4z8AAAAMBAQDJ/pLvAAAAAElFTkSuQmCC')
        );
    }
}
