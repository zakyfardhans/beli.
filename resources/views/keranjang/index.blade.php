@extends('layouts.app')

@section('judul', 'Keranjang Belanja')

@section('konten')
    <h1 class="mb-6 text-2xl font-bold tracking-tight md:text-3xl">Keranjang Belanja</h1>

    @if ($items->isEmpty())
        <div class="rounded-lg border border-line bg-white p-10 text-center">
            <p class="text-muted">Keranjangmu masih kosong.</p>
            <a href="{{ route('katalog') }}" class="mt-4 inline-block rounded-lg bg-brand px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-brand-dark">Mulai Belanja</a>
        </div>
    @else
        <div class="grid gap-6 lg:grid-cols-3">
            <div class="lg:col-span-2 space-y-3">
                @foreach ($items as $item)
                    <div class="flex gap-4 rounded-lg border border-line bg-white p-4">
                        <div class="flex h-20 w-20 shrink-0 items-center justify-center overflow-hidden rounded-lg bg-canvas">
                            @if ($item->produk->gambar)
                                <img src="{{ asset('storage/' . $item->produk->gambar) }}" alt="{{ $item->produk->nama }}" class="h-full w-full object-cover">
                            @else
                                <span class="text-2xl text-line">&#128230;</span>
                            @endif
                        </div>
                        <div class="min-w-0 flex-1">
                            <a href="{{ route('produk.show', $item->produk) }}" class="text-sm font-semibold text-ink hover:text-brand">{{ $item->produk->nama }}</a>
                            <p class="text-sm font-bold text-brand">{{ \App\Support\Rupiah::format($item->produk->harga) }}</p>
                            @if ($item->variasi)
                                <p class="mt-1 text-xs text-muted">
                                    Ukuran {{ $item->variasi->ukuran }} · {{ $item->variasi->warna }}
                                </p>
                            @endif
                            <p class="text-xs text-muted">Stok: {{ $item->variasi?->stok ?? $item->produk->stok }}</p>

                            <div class="mt-2 flex items-center gap-3">
                                <form action="{{ route('keranjang.update', $item) }}" method="POST" class="flex items-center gap-2">
                                    @csrf
                                    @method('PATCH')
                                    <input name="jumlah" type="number" min="1" max="{{ $item->produk->stok }}" value="{{ $item->jumlah }}"
                                        class="w-16 rounded-lg border border-line px-2 py-1 text-sm focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/20">
                                    <button type="submit" class="text-xs font-semibold text-brand hover:underline">Ubah</button>
                                </form>
                                <form action="{{ route('keranjang.destroy', $item) }}" method="POST" onsubmit="return confirm('Hapus item ini dari keranjang?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-xs font-semibold text-rose-600 hover:underline">Hapus</button>
                                </form>
                            </div>
                        </div>
                        <div class="shrink-0 text-right text-sm font-bold text-ink">
                            {{ \App\Support\Rupiah::format($item->produk->harga * $item->jumlah) }}
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="lg:col-span-1">
                <div class="rounded-lg border border-line bg-white p-5">
                    <h2 class="text-lg font-bold">Ringkasan</h2>
                    <div class="mt-3 flex justify-between text-sm">
                        <span class="text-muted">Subtotal</span>
                        <span class="font-semibold">{{ \App\Support\Rupiah::format($total) }}</span>
                    </div>
                    <p class="mt-2 text-xs text-muted">Ongkir dihitung saat checkout.</p>
                    <a href="{{ route('checkout.create') }}" class="mt-4 block rounded-lg bg-brand px-5 py-2.5 text-center text-sm font-semibold text-white transition hover:bg-brand-dark">Checkout</a>
                </div>
            </div>
        </div>
    @endif
@endsection
