<?php

namespace App\Http\Controllers;

use App\Models\Produk;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function index(): Response
    {
        $urls = collect([url('/')])->merge(
            Produk::where('aktif', true)->get()->map(fn ($produk) => route('produk.show', $produk))
        );

        $xml = view('seo.sitemap', ['urls' => $urls])->render();

        return response($xml, 200)->header('Content-Type', 'application/xml');
    }
}
