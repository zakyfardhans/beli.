<?php

namespace App\Http\Controllers;

use App\Models\Produk;
use App\Models\Wishlist;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class WishlistController extends Controller
{
    public function index(): View
    {
        $favorit = Wishlist::with('produk.kategori')
            ->where('user_id', auth()->id())
            ->latest()
            ->paginate(12);

        return view('favorit.index', compact('favorit'));
    }

    public function toggle(Produk $produk): RedirectResponse
    {
        $wishlist = Wishlist::where('user_id', auth()->id())->where('produk_id', $produk->id);

        if ($wishlist->exists()) {
            $wishlist->delete();
            return back()->with('sukses', 'Produk dihapus dari favorit.');
        }

        abort_unless($produk->aktif, 404);

        Wishlist::create(['user_id' => auth()->id(), 'produk_id' => $produk->id]);
        return back()->with('sukses', 'Produk ditambahkan ke favorit.');
    }
}
