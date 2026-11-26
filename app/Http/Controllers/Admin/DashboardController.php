<?php

namespace App\Http\Controllers\Admin;

use App\Enums\StatusPesanan;
use App\Http\Controllers\Controller;
use App\Models\DetailPesanan;
use App\Models\Pesanan;
use App\Models\Produk;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $statusPenjualan = [
            StatusPesanan::Diproses,
            StatusPesanan::Dikirim,
            StatusPesanan::Selesai,
        ];

        $pesananHariIni = Pesanan::whereDate('created_at', today())->count();
        $omzetBulanIni = Pesanan::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->whereIn('status', $statusPenjualan)
            ->sum('total');
        $menungguVerifikasi = Pesanan::where('status', StatusPesanan::MenungguVerifikasi)->count();
        $produkTerlaris = DetailPesanan::select('nama_produk', DB::raw('SUM(jumlah) as terjual'))
            ->whereHas('pesanan', fn ($query) => $query->whereIn('status', $statusPenjualan))
            ->groupBy('nama_produk')
            ->orderByDesc('terjual')
            ->limit(5)
            ->get();
        $stokRendah = Produk::where('aktif', true)->where('stok', '<=', 5)->orderBy('stok')->get();

        return view('admin.dashboard', compact(
            'pesananHariIni',
            'omzetBulanIni',
            'menungguVerifikasi',
            'produkTerlaris',
            'stokRendah'
        ));
    }
}
