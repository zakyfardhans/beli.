<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProdukRequest;
use App\Models\Kategori;
use App\Models\Keranjang;
use App\Models\DetailPesanan;
use App\Models\Produk;
use App\Models\ProdukVariasi;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class ProdukController extends Controller
{
    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q', ''));

        $produk = Produk::with('kategori')
            ->when($q !== '', fn($query) => $query->where('nama', 'like', "%{$q}%"))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.produk.index', compact('produk', 'q'));
    }

    public function create(): View
    {
        return view('admin.produk.create', [
            'kategori' => Kategori::orderBy('nama')->get(),
        ]);
    }

    public function store(ProdukRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $gambarBaru = $data['gambar'] ?? [];
        $variasi = $data['variasi'] ?? [];
        unset($data['gambar'], $data['hapus_gambar'], $data['variasi']);

        $data['slug'] = $this->uniqueSlug($data['nama']);
        $data['aktif'] = $request->boolean('aktif');
        $data['stok'] = $variasi !== []
            ? array_sum(array_column($variasi, 'stok'))
            : $data['stok'];
        $data['panduan_ukuran'] = $data['panduan_ukuran'] ?: null;

        $gambarBaru = $this->simpanGambarProduk($gambarBaru);
        $data['gambar'] = $gambarBaru[0] ?? null;
        $data['gambar_tambahan'] = array_slice($gambarBaru, 1) ?: null;

        try {
            $produk = DB::transaction(function () use ($data, $variasi) {
                $produk = new Produk($data);

                if (! $produk->save()) {
                    return null;
                }

                $this->simpanVariasi($produk, $variasi);

                return $produk;
            });
        } catch (Throwable $exception) {
            $this->hapusGambarProduk($gambarBaru);
            throw $exception;
        }

        if (! $produk) {
            $this->hapusGambarProduk($gambarBaru);

            return back()->withInput()->withErrors([
                'gambar' => 'Produk gagal disimpan. Silakan coba kembali.',
            ]);
        }

        Cache::forget('kategori-katalog');

        return redirect()->route('admin.produk.index')->with('sukses', 'Produk berhasil ditambahkan.');
    }

    public function edit(Produk $produk): View
    {
        $produk->load('variasi');

        return view('admin.produk.edit', [
            'produk' => $produk,
            'kategori' => Kategori::orderBy('nama')->get(),
        ]);
    }

    public function update(ProdukRequest $request, Produk $produk): RedirectResponse
    {
        $data = $request->validated();
        $gambarUpload = $data['gambar'] ?? [];
        $gambarDihapus = $data['hapus_gambar'] ?? [];
        $variasi = $data['variasi'] ?? [];
        unset($data['gambar'], $data['hapus_gambar'], $data['variasi']);

        $data['aktif'] = $request->boolean('aktif');
        $data['stok'] = $variasi !== []
            ? array_sum(array_column($variasi, 'stok'))
            : $data['stok'];
        $data['panduan_ukuran'] = $data['panduan_ukuran'] ?: null;

        if ($produk->nama !== $data['nama']) {
            $data['slug'] = $this->uniqueSlug($data['nama'], $produk->id);
        }

        $gambarLama = $produk->gambarGaleris();
        $gambarDihapus = array_values(array_intersect($gambarLama, $gambarDihapus));
        $gambarBaru = $this->simpanGambarProduk($gambarUpload);
        $gambarTersisa = array_values(array_diff($gambarLama, $gambarDihapus));
        $galeriAkhir = array_merge($gambarTersisa, $gambarBaru);
        $data['gambar'] = array_shift($galeriAkhir);
        $data['gambar_tambahan'] = $galeriAkhir ?: null;

        try {
            $tersimpan = DB::transaction(function () use ($produk, $data, $variasi) {
                $terkunci = Produk::whereKey($produk->id)->lockForUpdate()->firstOrFail();

                if (! $terkunci->update($data)) {
                    return false;
                }

                $this->simpanVariasi($terkunci, $variasi);

                return true;
            });
        } catch (Throwable $exception) {
            $this->hapusGambarProduk($gambarBaru);
            throw $exception;
        }

        if (! $tersimpan) {
            $this->hapusGambarProduk($gambarBaru);

            return back()->withInput()->withErrors([
                'gambar' => 'Produk gagal diperbarui. Silakan coba kembali.',
            ]);
        }

        $this->hapusGambarProduk($gambarDihapus);
        Cache::forget('kategori-katalog');

        return redirect()->route('admin.produk.index')->with('sukses', 'Produk berhasil diperbarui.');
    }

    /**
     * @param  array<int, array{id?: int, ukuran: string, warna: string, stok: int}>  $data
     */
    private function simpanVariasi(Produk $produk, array $data): void
    {
        $variasiLama = $produk->variasi()->get();
        $variasiBaru = collect($data)->keyBy(fn ($baris) => (int) ($baris['id'] ?? 0));

        if (
            $variasiLama->isEmpty()
            && $data !== []
            && Keranjang::where('produk_id', $produk->id)->whereNull('produk_variasi_id')->exists()
        ) {
            throw ValidationException::withMessages([
                'variasi' => 'Produk belum dapat memakai variasi karena masih ada item tanpa pilihan di keranjang pelanggan.',
            ]);
        }

        foreach ($variasiLama as $variasi) {
            $baris = $variasiBaru->get($variasi->id);
            $berubah = ! $baris
                || trim($baris['ukuran']) !== $variasi->ukuran
                || trim($baris['warna']) !== $variasi->warna;
            $sudahDipakai = Keranjang::where('produk_variasi_id', $variasi->id)->exists()
                || DetailPesanan::where('produk_variasi_id', $variasi->id)->exists();

            if ($berubah && $sudahDipakai) {
                throw ValidationException::withMessages([
                    'variasi' => "Variasi {$variasi->ukuran} · {$variasi->warna} tidak dapat dihapus atau diubah karena sudah dipakai dalam keranjang atau pesanan.",
                ]);
            }
        }

        $tersimpan = [];

        foreach ($data as $baris) {
            $variasi = isset($baris['id'])
                ? $produk->variasi()->whereKey($baris['id'])->firstOrFail()
                : new ProdukVariasi(['produk_id' => $produk->id]);

            $variasi->fill([
                'ukuran' => trim($baris['ukuran']),
                'warna' => trim($baris['warna']),
                'stok' => (int) $baris['stok'],
            ])->save();

            $tersimpan[] = $variasi->id;
        }

        $query = $produk->variasi();

        if ($tersimpan !== []) {
            $query->whereNotIn('id', $tersimpan);
        }

        $query->delete();
    }

    public function destroy(Produk $produk): RedirectResponse
    {
        // Produk yang pernah dipesan tidak boleh dihapus permanen.
        if ($produk->detailPesanan()->exists()) {
            return back()->with('galat', 'Produk pernah dipesan dan tidak boleh dihapus. Nonaktifkan saja.');
        }

        if (! $produk->delete()) {
            return back()->with('galat', 'Produk gagal dihapus. Silakan coba kembali.');
        }

        $this->hapusGambarProduk($produk->gambarGaleris());

        return redirect()->route('admin.produk.index')->with('sukses', 'Produk berhasil dihapus.');
    }

    private function simpanGambarProduk(array $files): array
    {
        $paths = [];

        foreach ($files as $file) {
            $path = $file->store('produk', 'public');

            if (! is_string($path) || $path === '') {
                $this->hapusGambarProduk($paths);

                throw ValidationException::withMessages([
                    'gambar' => 'Gambar produk gagal disimpan. Silakan coba kembali.',
                ]);
            }

            $paths[] = $path;
        }

        return $paths;
    }

    private function hapusGambarProduk(array $paths): void
    {
        if ($paths !== [] && ! Storage::disk('public')->delete($paths)) {
            Log::warning('Gagal membersihkan satu atau lebih gambar produk.', [
                'paths' => $paths,
            ]);
        }
    }

    private function uniqueSlug(string $nama, ?int $abaikanId = null): string
    {
        $base = Str::slug($nama);
        $slug = $base;
        $i = 1;

        while (
            Produk::where('slug', $slug)
                ->when($abaikanId, fn($q) => $q->where('id', '!=', $abaikanId))
                ->exists()
        ) {
            $slug = $base . '-' . (++$i);
        }

        return $slug;
    }
}
