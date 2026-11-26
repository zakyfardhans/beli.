@extends('layouts.app')

@section('judul', 'Katalog Produk')

@section('konten')
    {{-- Hero katalog / Banner --}}
    <section class="mb-7 overflow-hidden rounded-lg shadow-soft">
        <img src="{{ asset('banner.jpg') }}" alt="Banner Katalog Produk" class="h-56 w-full object-cover sm:h-72 lg:h-80">
    </section>

    {{-- Tab kategori --}}
    <nav aria-label="Kategori produk" class="mb-7 overflow-x-auto border-b border-line">
        <div class="flex w-max min-w-full gap-6">
            <a href="{{ route(
                'katalog',
                array_filter([
                    'q' => $q ?: null,
                    'harga_min' => $hargaMin ?: null,
                    'harga_max' => $hargaMax ?: null,
                    'urut' => $urut !== 'terbaru' ? $urut : null,
                ]),
            ) }}"
                @if (!$kategoriSlug) aria-current="page" @endif
                class="inline-flex min-h-12 shrink-0 items-center border-b-2 px-1 text-sm font-semibold transition-colors focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand
                {{ !$kategoriSlug
                    ? 'border-brand text-brand'
                    : 'border-transparent text-muted hover:border-line hover:text-ink' }}">
                Semua Produk
            </a>

            @foreach ($kategori as $kat)
                <a href="{{ route(
                    'katalog',
                    array_filter([
                        'q' => $q ?: null,
                        'kategori' => $kat->slug,
                        'harga_min' => $hargaMin ?: null,
                        'harga_max' => $hargaMax ?: null,
                        'urut' => $urut !== 'terbaru' ? $urut : null,
                    ]),
                ) }}"
                    @if ($kategoriSlug === $kat->slug) aria-current="page" @endif
                    class="inline-flex min-h-12 shrink-0 items-center border-b-2 px-1 text-sm font-semibold transition-colors focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand
                    {{ $kategoriSlug === $kat->slug
                        ? 'border-brand text-brand'
                        : 'border-transparent text-muted hover:border-line hover:text-ink' }}">
                    {{ $kat->nama }}
                </a>
            @endforeach
        </div>
    </nav>

    @if ($q || $kategoriSlug || $hargaMin || $hargaMax || $urut !== 'terbaru')
        <section class="mb-5 flex flex-wrap items-center gap-2" aria-label="Filter aktif">
            <span class="text-xs font-semibold text-muted">Filter aktif:</span>

            @if ($q)
                <a href="{{ route(
                    'katalog',
                    array_filter([
                        'kategori' => $kategoriSlug ?: null,
                        'harga_min' => $hargaMin ?: null,
                        'harga_max' => $hargaMax ?: null,
                        'urut' => $urut !== 'terbaru' ? $urut : null,
                    ]),
                ) }}"
                    class="inline-flex min-h-11 items-center gap-1.5 rounded-full border border-line bg-white px-3 py-2 text-xs font-semibold text-ink hover:border-brand hover:text-brand"
                    aria-label="Hapus pencarian {{ $q }}">
                    Pencarian: "{{ $q }}"
                    <span aria-hidden="true">&times;</span>
                </a>
            @endif

            @if ($kategoriSlug)
                @php
                    $kategoriAktif = $kategori->firstWhere('slug', $kategoriSlug);
                @endphp
                <a href="{{ route(
                    'katalog',
                    array_filter([
                        'q' => $q ?: null,
                        'harga_min' => $hargaMin ?: null,
                        'harga_max' => $hargaMax ?: null,
                        'urut' => $urut !== 'terbaru' ? $urut : null,
                    ]),
                ) }}"
                    class="inline-flex min-h-11 items-center gap-1.5 rounded-full border border-line bg-white px-3 py-2 text-xs font-semibold text-ink hover:border-brand hover:text-brand"
                    aria-label="Hapus filter kategori {{ $kategoriAktif?->nama }}">
                    {{ $kategoriAktif?->nama ?? $kategoriSlug }}
                    <span aria-hidden="true">&times;</span>
                </a>
            @endif

            @if ($hargaMin)
                <a href="{{ route(
                    'katalog',
                    array_filter([
                        'q' => $q ?: null,
                        'kategori' => $kategoriSlug ?: null,
                        'harga_max' => $hargaMax ?: null,
                        'urut' => $urut !== 'terbaru' ? $urut : null,
                    ]),
                ) }}"
                    class="inline-flex min-h-11 items-center gap-1.5 rounded-full border border-line bg-white px-3 py-2 text-xs font-semibold text-ink hover:border-brand hover:text-brand"
                    aria-label="Hapus batas harga minimum">
                    Dari {{ \App\Support\Rupiah::format($hargaMin) }}
                    <span aria-hidden="true">&times;</span>
                </a>
            @endif

            @if ($hargaMax)
                <a href="{{ route(
                    'katalog',
                    array_filter([
                        'q' => $q ?: null,
                        'kategori' => $kategoriSlug ?: null,
                        'harga_min' => $hargaMin ?: null,
                        'urut' => $urut !== 'terbaru' ? $urut : null,
                    ]),
                ) }}"
                    class="inline-flex min-h-11 items-center gap-1.5 rounded-full border border-line bg-white px-3 py-2 text-xs font-semibold text-ink hover:border-brand hover:text-brand"
                    aria-label="Hapus batas harga maksimum">
                    Sampai {{ \App\Support\Rupiah::format($hargaMax) }}
                    <span aria-hidden="true">&times;</span>
                </a>
            @endif

            @if ($urut !== 'terbaru')
                <a href="{{ route(
                    'katalog',
                    array_filter([
                        'q' => $q ?: null,
                        'kategori' => $kategoriSlug ?: null,
                        'harga_min' => $hargaMin ?: null,
                        'harga_max' => $hargaMax ?: null,
                    ]),
                ) }}"
                    class="inline-flex min-h-11 items-center gap-1.5 rounded-full border border-line bg-white px-3 py-2 text-xs font-semibold text-ink hover:border-brand hover:text-brand"
                    aria-label="Hapus pengurutan">
                    {{ $urut === 'termurah' ? 'Harga Terendah' : 'Harga Tertinggi' }}
                    <span aria-hidden="true">&times;</span>
                </a>
            @endif

            <a href="{{ route('katalog') }}"
                class="inline-flex min-h-11 items-center rounded-full px-3 py-2 text-xs font-bold text-brand hover:bg-brand-light">
                Hapus semua
            </a>
        </section>
    @endif


    {{-- Filter dan sorting --}}
    <section class="mb-6 border border-line bg-white shadow-sm">

        {{-- Header dropdown --}}
        <details class="group">
            <summary
                class="flex cursor-pointer list-none items-center justify-between p-4
                   [&::-webkit-details-marker]:hidden">

                <div class="flex items-center gap-2">
                    <svg class="h-5 w-5 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4h18M6 12h12m-9 8h6" />
                    </svg>

                    <h2 class="text-sm font-bold text-ink">
                        Filter & Pengurutan
                    </h2>
                </div>

                <svg class="h-5 w-5 text-muted transition-transform duration-200
                        group-open:rotate-180"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 9-7 7-7-7" />
                </svg>
            </summary>

            {{-- Isi filter --}}
            <div class="border-t border-line p-4">
                <form id="catalog-filter-form" method="GET" action="{{ route('katalog') }}">

                    <input type="hidden" name="q" value="{{ $q }}">
                    <input type="hidden" name="kategori" value="{{ $kategoriSlug }}">

                    <div class="mb-4 flex justify-end">
                        <a href="{{ route(
                            'katalog',
                            array_filter([
                                'q' => $q ?: null,
                                'kategori' => $kategoriSlug ?: null,
                            ]),
                        ) }}"
                            class="text-xs font-semibold text-brand hover:underline">
                            Reset filter
                        </a>
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">

                        {{-- Harga Minimum --}}
                        <label class="block">
                            <span class="mb-1.5 block text-xs font-semibold text-ink">
                                Harga Minimum
                            </span>

                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-xs text-muted">
                                    Rp
                                </span>

                                <input id="harga-min" name="harga_min" type="number" min="0"
                                    value="{{ $hargaMin ?: '' }}" placeholder="Harga minimum"
                                    class="w-full rounded-lg border border-line bg-white py-2.5 pl-9 pr-3 text-sm text-ink outline-none transition placeholder:text-muted focus:border-brand focus:ring-2 focus:ring-brand/10">
                                <p id="harga-range-error" class="mt-1 hidden text-xs font-semibold text-rose-600"
                                    role="alert">
                                    Harga minimum tidak boleh lebih besar dari maksimum.
                                </p>
                            </div>
                        </label>

                        {{-- Harga Maksimum --}}
                        <label class="block">
                            <span class="mb-1.5 block text-xs font-semibold text-ink">
                                Harga Maksimum
                            </span>

                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-xs text-muted">
                                    Rp
                                </span>

                                <input id="harga-max" name="harga_max" type="number" min="0"
                                    value="{{ $hargaMax ?: '' }}" placeholder="Harga maksimum"
                                    class="w-full rounded-lg border border-line bg-white py-2.5 pl-9 pr-3 text-sm text-ink outline-none transition placeholder:text-muted focus:border-brand focus:ring-2 focus:ring-brand/10">
                            </div>
                        </label>

                        {{-- Pengurutan --}}
                        <label class="block">
                            <span class="mb-1.5 block text-xs font-semibold text-ink">
                                Urutkan Produk
                            </span>

                            <select name="urut"
                                class="w-full rounded-lg border border-line bg-white px-3 py-2.5 text-sm text-ink outline-none transition focus:border-brand focus:ring-2 focus:ring-brand/10">

                                <option value="terbaru" @selected($urut === 'terbaru')>
                                    Terbaru
                                </option>

                                <option value="termurah" @selected($urut === 'termurah')>
                                    Harga Terendah
                                </option>

                                <option value="termahal" @selected($urut === 'termahal')>
                                    Harga Tertinggi
                                </option>
                            </select>
                        </label>

                        {{-- Tombol Terapkan --}}
                        <div class="flex items-end">
                            <button type="submit"
                                class="w-full rounded-lg bg-brand px-4 py-2.5 text-sm font-bold text-white shadow-sm transition hover:brightness-95 active:scale-[0.98]">
                                Terapkan Filter
                            </button>
                        </div>

                    </div>
                </form>
            </div>
        </details>
    </section>


    {{-- Informasi pencarian --}}
    @if ($q)
        <div class="mb-5 flex flex-wrap items-center gap-2 text-sm text-muted">
            <span>Hasil pencarian untuk:</span>
            <span class="rounded-md bg-white px-3 py-1 font-semibold text-ink">
                "{{ $q }}"
            </span>
        </div>
    @endif

    {{-- Daftar produk --}}
    @if ($produk->isEmpty())
        <div id="produk-pilihan" class="border border-line bg-white px-5 py-16 text-center">
            <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-canvas">
                <svg class="h-8 w-8 text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0H4m5-5h6" />
                </svg>
            </div>

            <h3 class="text-lg font-bold text-ink">
                Produk Tidak Ditemukan
            </h3>

            <p class="mx-auto mt-2 max-w-sm text-sm text-muted">
                @if ($q)
                    Tidak ada produk yang cocok dengan pencarian "{{ $q }}"
                    @if ($kategoriSlug || $hargaMin || $hargaMax)
                        dan filter aktif saat ini.
                    @else
                        .
                    @endif
                @elseif ($kategoriSlug || $hargaMin || $hargaMax)
                    Tidak ada produk yang cocok dengan filter yang dipilih saat ini.
                @else
                    Maaf, produk yang kamu cari belum tersedia.
                    Coba gunakan kata kunci atau filter yang berbeda.
                @endif
            </p>

            <a href="{{ route('katalog') }}"
                class="mt-5 inline-flex rounded-lg bg-brand px-5 py-2.5 text-sm font-bold text-white transition hover:brightness-95">
                Lihat Semua Produk
            </a>
        </div>
    @else
        <section id="produk-pilihan">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-base font-bold text-ink">
                    Produk Pilihan
                </h2>
                <span class="text-sm text-muted">
                    {{ $produk->total() }} produk
                </span>
            </div>

            <ul class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5">
                @foreach ($produk as $item)
                    <li class="min-w-0">
                        <a href="{{ route('produk.show', $item) }}"
                            class="group flex h-full min-w-0 flex-col overflow-hidden rounded-lg border border-line bg-white transition duration-200 hover:border-brand/40 hover:shadow-soft focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand">

                            {{-- Gambar produk --}}
                            <div class="relative w-full aspect-square overflow-hidden bg-white">
                                <img src="{{ $item->gambar ? asset('storage/' . $item->gambar) : asset('placeholder-produk.svg') }}"
                                    alt="{{ $item->nama }}" loading="lazy"
                                    class="absolute inset-0 block w-full h-full object-cover">

                                @if ($item->stok == 0)
                                    <span
                                        class="absolute left-2 top-2 rounded-md bg-rose-100 px-2 py-1 text-xs font-bold text-rose-800">
                                        Stok Habis
                                    </span>
                                @endif
                            </div>

                            {{-- Informasi produk --}}
                            <div class="flex flex-1 flex-col p-3">
                                <h3 class="line-clamp-2 min-h-10 text-xs font-medium leading-relaxed text-ink sm:text-sm">
                                    {{ $item->nama }}
                                </h3>

                                <p class="mt-3 text-sm font-extrabold leading-tight text-brand sm:text-base">
                                    {{ \App\Support\Rupiah::format($item->harga) }}
                                </p>

                                <div class="mt-2 flex min-w-0 items-center justify-between gap-2 text-xs">
                                    @if ($item->ulasan_produk_count > 0)
                                        @php($ratingRataRata = number_format((float) $item->ulasan_produk_avg_rating, 1, ',', '.'))
                                        <span class="inline-flex min-w-0 items-center gap-1 text-muted"
                                            aria-label="Rating rata-rata {{ $ratingRataRata }} dari 5 berdasarkan {{ $item->ulasan_produk_count }} ulasan">
                                            <svg viewBox="0 0 20 20" class="size-3.5 shrink-0 fill-amber-500"
                                                aria-hidden="true">
                                                <path
                                                    d="m10 1.5 2.6 5.27 5.82.85-4.21 4.1 1 5.8L10 14.78l-5.21 2.74 1-5.8-4.21-4.1 5.82-.85L10 1.5Z" />
                                            </svg>
                                            <span class="font-semibold text-ink">{{ $ratingRataRata }}</span>
                                            <span class="truncate"
                                                aria-hidden="true">({{ $item->ulasan_produk_count }})</span>
                                        </span>
                                    @else
                                        <span class="truncate text-muted">Belum ada ulasan</span>
                                    @endif
                                    <span class="shrink-0 text-muted">
                                        Terjual {{ number_format((int) $item->total_terjual, 0, ',', '.') }}
                                    </span>
                                </div>

                                <div class="mt-3 flex items-center justify-between border-t border-line pt-2">
                                    <span class="text-xs text-muted">Stok</span>
                                    <span
                                        class="text-xs font-semibold {{ $item->stok > 0 ? 'text-emerald-800' : 'text-rose-700' }}">
                                        {{ $item->stok > 0 ? $item->stok . ' tersedia' : 'Habis' }}
                                    </span>
                                </div>
                            </div>
                        </a>
                    </li>
                @endforeach
            </ul>
        </section>

        {{-- Pagination --}}
        <div class="mt-5 border-t border-line pt-5">
            <p class="mb-3 text-center text-xs text-muted">
                Halaman {{ $produk->currentPage() }} dari {{ $produk->lastPage() }}
            </p>
            {{ $produk->links() }}
        </div>
    @endif

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('catalog-filter-form');
            const minimum = document.getElementById('harga-min');
            const maximum = document.getElementById('harga-max');
            const error = document.getElementById('harga-range-error');

            if (!form || !minimum || !maximum || !error) return;

            const validateRange = function() {
                const minimumValue = Number(minimum.value);
                const maximumValue = Number(maximum.value);
                const isReversed = minimum.value !== '' && maximum.value !== '' &&
                    minimumValue > maximumValue;

                error.classList.toggle('hidden', !isReversed);
                minimum.setAttribute('aria-invalid', String(isReversed));
                maximum.setAttribute('aria-invalid', String(isReversed));

                if (!isReversed) {
                    minimum.removeAttribute('aria-describedby');
                    maximum.removeAttribute('aria-describedby');
                } else {
                    minimum.setAttribute('aria-describedby', 'harga-range-error');
                    maximum.setAttribute('aria-describedby', 'harga-range-error');
                }

                return !isReversed;
            };

            minimum.addEventListener('input', validateRange);
            maximum.addEventListener('input', validateRange);
            form.addEventListener('submit', function(event) {
                if (!validateRange()) {
                    event.preventDefault();
                    minimum.focus();
                }
            });
        });
    </script>
@endsection
