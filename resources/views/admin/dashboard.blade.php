@extends('layouts.admin')

@section('judul', 'Dashboard Admin')

@section('konten')
    <p class="mb-6 text-sm text-muted">Selamat datang, {{ auth()->user()->name }}.</p>

    <div class="mb-6 grid gap-4 sm:grid-cols-3">
        <div class="rounded-lg border border-line bg-white p-5">
            <p class="text-sm text-muted">Pesanan Hari Ini</p>
            <p class="mt-1 text-2xl font-bold">{{ $pesananHariIni }}</p>
        </div>
        <div class="rounded-lg border border-line bg-white p-5">
            <p class="text-sm text-muted">Omzet Bulan Ini</p>
            <p class="mt-1 text-2xl font-bold">{{ \App\Support\Rupiah::format($omzetBulanIni) }}</p>
        </div>
        <div class="rounded-lg border border-line bg-white p-5">
            <p class="text-sm text-muted">Menunggu Verifikasi</p>
            <p class="mt-1 text-2xl font-bold">{{ $menungguVerifikasi }}</p>
        </div>
    </div>

    <div class="grid gap-4 md:grid-cols-3">
        <a href="{{ route('admin.produk.index') }}" class="rounded-lg border border-line bg-white p-5 transition hover:shadow-float">
            <p class="text-sm text-muted">Kelola</p>
            <p class="mt-1 text-lg font-bold text-ink">Produk</p>
        </a>
        <a href="{{ route('admin.pesanan.index') }}" class="rounded-lg border border-line bg-white p-5 transition hover:shadow-float">
            <p class="text-sm text-muted">Kelola</p>
            <p class="mt-1 text-lg font-bold text-ink">Pesanan</p>
        </a>
        <a href="{{ route('katalog') }}" class="rounded-lg border border-line bg-white p-5 transition hover:shadow-float">
            <p class="text-sm text-muted">Lihat</p>
            <p class="mt-1 text-lg font-bold text-ink">Katalog Publik</p>
        </a>
    </div>

    <div class="mt-6 grid gap-6 md:grid-cols-2">
        <div class="rounded-lg border border-line bg-white p-5">
            <h2 class="mb-3 font-bold">5 Produk Terlaris</h2>
            @forelse($produkTerlaris as $produk)
                <div class="flex justify-between border-t border-line py-2 text-sm">
                    <span>{{ $produk->nama_produk }}</span>
                    <span class="font-semibold">{{ $produk->terjual }} terjual</span>
                </div>
            @empty
                <p class="text-sm text-muted">Belum ada penjualan.</p>
            @endforelse
        </div>
        <div class="rounded-lg border border-line bg-white p-5">
            <h2 class="mb-3 font-bold">Stok ≤ 5</h2>
            @forelse($stokRendah as $produk)
                <div class="flex justify-between border-t border-line py-2 text-sm">
                    <span>{{ $produk->nama }}</span>
                    <span class="font-semibold {{ $produk->stok === 0 ? 'text-rose-600' : '' }}">{{ $produk->stok }}</span>
                </div>
            @empty
                <p class="text-sm text-muted">Tidak ada stok rendah.</p>
            @endforelse
        </div>
    </div>
@endsection
