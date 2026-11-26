@extends('layouts.app')

@section('judul', 'Favorit Saya')

@section('konten')
    <div class="mb-6 flex flex-wrap items-end justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold tracking-tight md:text-3xl">Favorit Saya</h1>
            <p class="mt-1 text-sm text-muted">Produk yang Anda simpan untuk dilihat kembali.</p>
        </div>
        <a href="{{ route('katalog') }}" class="text-sm font-semibold text-brand hover:underline">Jelajahi katalog</a>
    </div>

    @if ($favorit->isEmpty())
        <div class="rounded-lg border border-line bg-white px-5 py-12 text-center">
            <h2 class="text-lg font-bold">Belum ada produk favorit</h2>
            <p class="mt-2 text-sm text-muted">Simpan produk dari halaman detail untuk menemukannya dengan mudah nanti.</p>
            <a href="{{ route('katalog') }}"
                class="mt-5 inline-flex min-h-11 items-center rounded-lg bg-brand px-4 py-2 text-sm font-semibold text-white hover:bg-brand-dark">
                Lihat Katalog
            </a>
        </div>
    @else
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">
            @foreach ($favorit as $item)
                @if ($item->produk)
                    <article class="flex flex-col overflow-hidden rounded-lg border border-line bg-white">
                        @if ($item->produk->aktif)
                        <a href="{{ route('produk.show', $item->produk) }}" class="block">
                        @else
                        <div class="block">
                        @endif
                            <div class="aspect-square bg-canvas">
                                <img src="{{ $item->produk->gambar ? asset('storage/' . $item->produk->gambar) : asset('placeholder-produk.svg') }}"
                                    alt="{{ $item->produk->nama }}" class="h-full w-full object-cover">
                            </div>
                            <div class="p-3">
                                <p class="line-clamp-2 min-h-10 text-sm font-medium text-ink">{{ $item->produk->nama }}</p>
                                <p class="mt-1 font-bold text-brand">{{ \App\Support\Rupiah::format($item->produk->harga) }}</p>
                                @unless ($item->produk->aktif)
                                    <p class="mt-1 text-xs font-semibold text-rose-700">Produk tidak tersedia</p>
                                @endunless
                            </div>
                        @if ($item->produk->aktif)
                        </a>
                        @else
                        </div>
                        @endif
                        <form action="{{ route('produk.favorit', $item->produk) }}" method="POST" class="mt-auto px-3 pb-3">
                            @csrf
                            <button type="submit"
                                class="min-h-10 w-full rounded-lg border border-line px-3 py-2 text-sm font-semibold text-ink hover:border-rose-300 hover:text-rose-700">
                                Hapus dari Favorit
                            </button>
                        </form>
                    </article>
                @endif
            @endforeach
        </div>
        <div class="mt-6">{{ $favorit->links() }}</div>
    @endif
@endsection
