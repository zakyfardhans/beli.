<?php

namespace App\Http\Controllers;

use App\Models\Keranjang;
use App\Models\Produk;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class KeranjangController extends Controller
{
    public function index(): View
    {
        $items = Keranjang::with(['produk', 'variasi'])
            ->where('user_id', auth()->id())
            ->latest()
            ->get();

        $total = $items->sum(fn ($i) => $i->produk->harga * $i->jumlah);

        return view('keranjang.index', compact('items', 'total'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'produk_id' => ['required', 'exists:produk,id'],
            'jumlah' => ['required', 'integer', 'min:1'],
            'ukuran' => ['nullable', 'string', 'max:40'],
            'warna' => ['nullable', 'string', 'max:60'],
        ], [
            'produk_id.required' => 'Produk tidak valid.',
            'produk_id.exists' => 'Produk tidak ditemukan.',
            'jumlah.required' => 'Jumlah wajib diisi.',
            'jumlah.integer' => 'Jumlah harus berupa angka.',
            'jumlah.min' => 'Jumlah minimal 1.',
            'ukuran.string' => 'Pilih ukuran yang tersedia.',
            'warna.string' => 'Pilih warna yang tersedia.',
        ]);

        return DB::transaction(function () use ($data) {
            Keranjang::where('user_id', auth()->id())->lockForUpdate()->get();

            $produk = Produk::query()
                ->aktif()
                ->whereKey($data['produk_id'])
                ->lockForUpdate()
                ->firstOrFail();
            $jumlah = (int) $data['jumlah'];
            $punyaVariasi = $produk->variasi()->exists();
            $variasi = null;

            if ($punyaVariasi) {
                if (empty($data['ukuran']) || empty($data['warna'])) {
                    return back()->withInput()->withErrors([
                        'ukuran' => 'Pilih ukuran dan warna terlebih dahulu.',
                    ]);
                }

                $variasi = $produk->variasi()
                    ->where('ukuran', trim($data['ukuran']))
                    ->where('warna', trim($data['warna']))
                    ->lockForUpdate()
                    ->first();

                if (! $variasi) {
                    return back()->withInput()->withErrors([
                        'warna' => 'Kombinasi ukuran dan warna tidak tersedia.',
                    ]);
                }
            } elseif (! empty($data['ukuran']) || ! empty($data['warna'])) {
                return back()->withInput()->withErrors([
                    'ukuran' => 'Pilihan variasi produk tidak valid.',
                ]);
            }

            $stokTersedia = $variasi?->stok ?? $produk->stok;

            if ($stokTersedia < 1) {
                return back()->with('galat', 'Produk ini sedang habis.');
            }

            $variasiKey = $variasi ? 'variasi-' . $variasi->id : 'utama';
            $item = Keranjang::where('user_id', auth()->id())
                ->where('produk_id', $produk->id)
                ->where('variasi_key', $variasiKey)
                ->lockForUpdate()
                ->first() ?? new Keranjang([
                    'user_id' => auth()->id(),
                    'produk_id' => $produk->id,
                    'variasi_key' => $variasiKey,
                ]);

            $jumlahBaru = $item->jumlah + $jumlah;

            if ($jumlahBaru > $stokTersedia) {
                return back()->with('galat', "Jumlah melebihi stok tersedia ({$stokTersedia}).");
            }

            $item->produk_variasi_id = $variasi?->id;
            $item->jumlah = $jumlahBaru;
            $item->save();

            return back()->with('sukses', 'Produk ditambahkan ke keranjang.');
        });
    }

    public function update(Request $request, Keranjang $item): RedirectResponse
    {
        abort_unless($item->user_id === auth()->id(), 403);

        $data = $request->validate([
            'jumlah' => ['required', 'integer', 'min:1'],
        ], [
            'jumlah.required' => 'Jumlah wajib diisi.',
            'jumlah.integer' => 'Jumlah harus berupa angka.',
            'jumlah.min' => 'Jumlah minimal 1.',
        ]);

        return DB::transaction(function () use ($item, $data) {
            Keranjang::where('user_id', auth()->id())->lockForUpdate()->get();

            $produk = Produk::whereKey($item->produk_id)->lockForUpdate()->firstOrFail();
            $itemTerkunci = Keranjang::whereKey($item->id)
                ->where('user_id', auth()->id())
                ->lockForUpdate()
                ->firstOrFail();
            $variasi = $itemTerkunci->produk_variasi_id
                ? $produk->variasi()->whereKey($itemTerkunci->produk_variasi_id)->lockForUpdate()->first()
                : null;

            if ($produk->variasi()->exists() && ! $variasi) {
                return back()->with('galat', 'Pilihan produk ini sudah tidak tersedia. Hapus item lalu pilih variasi kembali.');
            }

            $stokTersedia = $variasi?->stok ?? $produk->stok;

            if ((int) $data['jumlah'] > $stokTersedia) {
                return back()->with('galat', "Jumlah melebihi stok tersedia ({$stokTersedia}).");
            }

            $itemTerkunci->update(['jumlah' => (int) $data['jumlah']]);

            return back()->with('sukses', 'Jumlah keranjang diperbarui.');
        });
    }

    public function destroy(Keranjang $item): RedirectResponse
    {
        abort_unless($item->user_id === auth()->id(), 403);

        $item->delete();

        return back()->with('sukses', 'Item dihapus dari keranjang.');
    }
}
