<?php

namespace App\Http\Controllers;

use App\Enums\StatusPesanan;
use App\Models\Kategori;
use App\Models\Pesanan;
use App\Models\Produk;
use App\Models\UlasanProduk;
use App\Models\Wishlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class KatalogController extends Controller
{
    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q', ''));
        $kategoriSlug = $request->query('kategori');
        $hargaMin = $request->integer('harga_min');
        $hargaMax = $request->integer('harga_max');
        $urut = (string) $request->query('urut', 'terbaru');

        $produk = Produk::query()
            ->aktif()
            ->with('kategori')
            ->denganMetrikKatalog()
            ->when($q !== '', function ($query) use ($q) {
                $query->where('nama', 'like', "%{$q}%");
            })
            ->when($kategoriSlug, function ($query) use ($kategoriSlug) {
                $query->whereHas('kategori', fn ($k) => $k->where('slug', $kategoriSlug));
            })
            ->when($hargaMin > 0, fn ($query) => $query->where('harga', '>=', $hargaMin))
            ->when($hargaMax > 0, fn ($query) => $query->where('harga', '<=', $hargaMax))
            ->when($urut === 'termurah', fn ($query) => $query->orderBy('harga'))
            ->when($urut === 'termahal', fn ($query) => $query->orderByDesc('harga'))
            ->when(! in_array($urut, ['termurah', 'termahal'], true), fn ($query) => $query->latest())
            ->paginate(12)
            ->withQueryString();

        $kategori = Cache::remember('kategori-katalog', 300, fn () => Kategori::orderBy('nama')->get());

        return view('katalog.index', compact('produk', 'kategori', 'q', 'kategoriSlug', 'hargaMin', 'hargaMax', 'urut'));
    }

    public function show(Produk $produk): View
    {
        abort_unless($produk->aktif, 404);

        $produk->load(['kategori', 'variasi']);

        $terkait = Produk::aktif()
            ->denganMetrikKatalog()
            ->where('kategori_id', $produk->kategori_id)
            ->where('id', '!=', $produk->id)
            ->limit(4)
            ->get();

        $ulasan = UlasanProduk::with('user:id,name')->where('produk_id', $produk->id)->latest()->get();
        $pelanggan = auth()->check() && auth()->user()->isPelanggan();
        $favorit = $pelanggan
            ? Wishlist::where('user_id', auth()->id())->where('produk_id', $produk->id)->exists()
            : false;
        $pesananSelesai = $pelanggan
            ? Pesanan::where('user_id', auth()->id())
                ->where('status', StatusPesanan::Selesai)
                ->whereHas('detailPesanan', fn ($query) => $query->where('produk_id', $produk->id))
                ->latest()
                ->get(['id', 'kode_pesanan', 'created_at'])
            : collect();
        $ulasanSaya = $pelanggan
            ? UlasanProduk::where('user_id', auth()->id())
                ->where('produk_id', $produk->id)
                ->whereIn('pesanan_id', $pesananSelesai->pluck('id'))
                ->get()
                ->keyBy('pesanan_id')
            : collect();

        return view('katalog.show', compact(
            'produk',
            'terkait',
            'ulasan',
            'favorit',
            'pesananSelesai',
            'ulasanSaya',
            'pelanggan',
        ));
    }
}
