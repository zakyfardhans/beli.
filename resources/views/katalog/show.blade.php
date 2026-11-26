    @extends('layouts.app')

    @section('judul', $produk->nama)

    @section('konten')
        @php
            $gambarProduk = $produk->gambarGaleris();
            $variasiProduk = $produk->variasi;
            $ukuranPilihan = $variasiProduk->pluck('ukuran')->unique()->values();
            $warnaPilihan = $variasiProduk->pluck('warna')->unique()->values();
            $dataVariasi = $variasiProduk->map(fn ($item) => [
                'id' => $item->id,
                'ukuran' => $item->ukuran,
                'warna' => $item->warna,
                'stok' => $item->stok,
            ])->values();
            $panduanUkuran = $produk->panduan_ukuran ?? [];
        @endphp

        <nav aria-label="Breadcrumb" class="mb-4 flex flex-wrap items-center gap-1.5 text-sm text-muted">
            <a href="{{ route('katalog') }}" class="hover:text-brand">Katalog</a>
            <span aria-hidden="true">/</span>
            <a href="{{ route('katalog', ['kategori' => $produk->kategori->slug]) }}"
                class="hover:text-brand">{{ $produk->kategori->nama }}</a>
            <span aria-hidden="true">/</span>
            <span aria-current="page" class="text-ink">{{ $produk->nama }}</span>
        </nav>

        <section class="grid gap-6 rounded-lg border border-line bg-white p-4 md:grid-cols-2 md:gap-8 md:p-6">
            <div data-product-gallery class="min-w-0">
                <div class="flex aspect-[4/3] items-center justify-center overflow-hidden rounded-lg bg-canvas">
                    @if ($gambarProduk !== [])
                        <button type="button" data-gallery-open aria-haspopup="dialog"
                            aria-controls="galeri-produk-{{ $produk->id }}"
                            aria-label="Perbesar foto produk {{ $produk->nama }}"
                            class="group relative flex h-full w-full items-center justify-center focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand">
                            <img data-gallery-main src="{{ asset('storage/' . $gambarProduk[0]) }}"
                                alt="{{ $produk->nama }}" class="h-full w-full object-contain">
                            <span
                                class="absolute bottom-3 rounded-md bg-white/90 px-3 py-1.5 text-xs font-semibold text-brand opacity-0 shadow-soft transition-opacity group-hover:opacity-100 group-focus-visible:opacity-100">
                                Lihat foto lebih besar
                            </span>
                        </button>
                    @else
                        <img src="{{ asset('placeholder-produk.svg') }}" alt="Gambar placeholder {{ $produk->nama }}"
                            class="h-full w-full object-contain">
                    @endif
                </div>

                @if (count($gambarProduk) > 1)
                    <div class="mt-3">
                        <div class="mb-2 flex items-center justify-between gap-3">
                            <p class="text-xs font-semibold text-muted">Foto Produk</p>
                            <p data-gallery-counter aria-live="polite" class="text-xs text-muted">1 dari {{ count($gambarProduk) }}</p>
                        </div>
                        <div class="relative">
                            <button type="button" data-gallery-strip-scroll="prev" hidden aria-label="Gulir ke foto sebelumnya"
                                class="absolute left-0 top-1/2 z-10 inline-flex size-9 -translate-y-1/2 items-center justify-center rounded-md border border-line bg-white text-brand shadow-soft transition hover:bg-brand hover:text-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand disabled:cursor-not-allowed disabled:opacity-40">
                                <svg viewBox="0 0 20 20" class="size-5" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd"
                                        d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z"
                                        clip-rule="evenodd" />
                                </svg>
                            </button>

                            <div data-gallery-strip tabindex="0" aria-label="Foto produk {{ $produk->nama }}"
                                class="flex gap-2 overflow-x-auto px-11 py-1 scroll-smooth snap-x snap-mandatory">
                                @foreach ($gambarProduk as $gambar)
                                    <button type="button" data-gallery-thumb data-gallery-index="{{ $loop->index }}"
                                        aria-label="Tampilkan foto {{ $loop->iteration }} dari {{ count($gambarProduk) }}"
                                        aria-pressed="{{ $loop->first ? 'true' : 'false' }}"
                                        class="size-16 shrink-0 snap-start overflow-hidden rounded-md border-2 {{ $loop->first ? 'border-brand ring-2 ring-brand/20' : 'border-line hover:border-brand' }} focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand">
                                        <img src="{{ asset('storage/' . $gambar) }}"
                                            alt="Foto {{ $loop->iteration }} {{ $produk->nama }}" loading="lazy"
                                            class="h-full w-full bg-canvas object-cover">
                                    </button>
                                @endforeach
                            </div>

                            <button type="button" data-gallery-strip-scroll="next" hidden aria-label="Gulir ke foto berikutnya"
                                class="absolute right-0 top-1/2 z-10 inline-flex size-9 -translate-y-1/2 items-center justify-center rounded-md border border-line bg-white text-brand shadow-soft transition hover:bg-brand hover:text-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand disabled:cursor-not-allowed disabled:opacity-40">
                                <svg viewBox="0 0 20 20" class="size-5" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd"
                                        d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                                        clip-rule="evenodd" />
                                </svg>
                            </button>
                        </div>
                    </div>
                @endif
            </div>

            <div class="flex flex-col">
                <a href="{{ route('katalog', ['kategori' => $produk->kategori->slug]) }}"
                    class="w-fit rounded-md bg-canvas px-2.5 py-1 text-xs font-semibold text-muted hover:text-brand">
                    {{ $produk->kategori->nama }}
                </a>
                <h1 class="mt-3 text-2xl font-bold tracking-tight text-ink md:text-3xl">{{ $produk->nama }}</h1>

                <div class="mt-5 rounded-lg bg-canvas px-4 py-3">
                    <p class="text-3xl font-extrabold tracking-tight text-brand">
                        {{ \App\Support\Rupiah::format($produk->harga) }}
                    </p>
                </div>

                <div class="mt-4 flex flex-wrap items-center justify-between gap-2 text-sm">
                    <span class="text-muted">Ketersediaan</span>
                    @if ($produk->stok > 0)
                        <span class="font-semibold text-emerald-800">Tersedia · {{ $produk->stok }}</span>
                    @else
                        <span class="font-semibold text-rose-700">Stok habis</span>
                    @endif
                </div>

                @if (! $pelanggan && $variasiProduk->isNotEmpty())
                    <div class="mt-4 grid gap-4 sm:grid-cols-2">
                        <div>
                            <p class="mb-2 text-sm font-semibold">Ukuran tersedia</p>
                            <div class="flex flex-wrap gap-2">
                                @foreach ($ukuranPilihan as $ukuran)
                                    <span class="inline-flex min-h-9 items-center rounded-md border border-line px-3 text-sm {{ $variasiProduk->where('ukuran', $ukuran)->sum('stok') < 1 ? 'text-muted line-through' : 'text-ink' }}">
                                        {{ $ukuran }}
                                    </span>
                                @endforeach
                            </div>
                            @if ($panduanUkuran !== [])
                                <details class="group mt-2">
                                    <summary class="flex min-h-8 w-fit cursor-pointer list-none items-center gap-1 text-sm font-medium text-brand underline-offset-4 hover:underline focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand">
                                        Tabel Ukuran
                                        <svg viewBox="0 0 20 20" fill="currentColor"
                                            class="size-4 transition-transform group-open:rotate-90" aria-hidden="true">
                                            <path fill-rule="evenodd"
                                                d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </summary>
                                    <div class="mt-2 max-w-full overflow-x-auto rounded-md border border-line">
                                        <table class="w-full min-w-64 text-left text-sm">
                                            <thead class="bg-canvas text-xs text-muted">
                                                <tr>
                                                    <th scope="col" class="px-3 py-2 font-semibold">Ukuran</th>
                                                    <th scope="col" class="px-3 py-2 font-semibold">Panduan ukuran</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-line">
                                                @foreach ($panduanUkuran as $baris)
                                                    <tr>
                                                        <th scope="row" class="px-3 py-2 font-medium">{{ $baris['ukuran'] }}</th>
                                                        <td class="px-3 py-2 text-muted">{{ $baris['panduan'] }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </details>
                            @endif
                        </div>
                        <div>
                            <p class="mb-2 text-sm font-semibold">Warna tersedia</p>
                            <div class="flex flex-wrap gap-2">
                                @foreach ($warnaPilihan as $warna)
                                    <span class="inline-flex min-h-9 items-center rounded-md border border-line px-3 text-sm {{ $variasiProduk->where('warna', $warna)->sum('stok') < 1 ? 'text-muted line-through' : 'text-ink' }}">
                                        {{ $warna }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif

                @if ($produk->stok > 0)
                    <div class="mt-6">
                        @if ($pelanggan)
                            <form action="{{ route('keranjang.store') }}" method="POST"
                                class="flex flex-col gap-4">
                                @csrf
                                <input type="hidden" name="produk_id" value="{{ $produk->id }}">

                                @if ($variasiProduk->isNotEmpty())
                                    <div data-variant-picker class="space-y-3">
                                        <div data-variant-choices hidden class="grid gap-4 sm:grid-cols-2">
                                            <fieldset class="min-w-0">
                                                <legend class="mb-2 text-sm font-semibold">Ukuran</legend>
                                                <div class="flex flex-wrap gap-2">
                                                    @foreach ($ukuranPilihan as $ukuran)
                                                        @php
                                                            $stokUkuran = $variasiProduk->where('ukuran', $ukuran)->sum('stok');
                                                            $aktif = (string) old('ukuran', '') === (string) $ukuran;
                                                        @endphp
                                                        <button type="button" data-variant-choice="ukuran"
                                                            data-value="{{ $ukuran }}" aria-pressed="{{ $aktif ? 'true' : 'false' }}"
                                                            @disabled($stokUkuran < 1)
                                                            class="min-h-10 min-w-14 rounded-md border px-3 text-sm font-medium transition focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand disabled:cursor-not-allowed disabled:opacity-40 {{ $aktif ? 'border-brand bg-brand text-white' : 'border-line bg-white text-ink hover:border-brand' }}">
                                                            {{ $ukuran }}
                                                        </button>
                                                    @endforeach
                                                </div>
                                                @if ($panduanUkuran !== [])
                                                    <details class="group mt-2">
                                                        <summary class="flex min-h-8 w-fit cursor-pointer list-none items-center gap-1 text-sm font-medium text-brand underline-offset-4 hover:underline focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand">
                                                            Tabel Ukuran
                                                            <svg viewBox="0 0 20 20" fill="currentColor"
                                                                class="size-4 transition-transform group-open:rotate-90"
                                                                aria-hidden="true">
                                                                <path fill-rule="evenodd"
                                                                    d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                                                                    clip-rule="evenodd" />
                                                            </svg>
                                                        </summary>
                                                        <div class="mt-2 max-w-full overflow-x-auto rounded-md border border-line">
                                                            <table class="w-full min-w-64 text-left text-sm">
                                                                <thead class="bg-canvas text-xs text-muted">
                                                                    <tr>
                                                                        <th scope="col" class="px-3 py-2 font-semibold">Ukuran</th>
                                                                        <th scope="col" class="px-3 py-2 font-semibold">Panduan ukuran</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody class="divide-y divide-line">
                                                                    @foreach ($panduanUkuran as $baris)
                                                                        <tr>
                                                                            <th scope="row" class="px-3 py-2 font-medium">{{ $baris['ukuran'] }}</th>
                                                                            <td class="px-3 py-2 text-muted">{{ $baris['panduan'] }}</td>
                                                                        </tr>
                                                                    @endforeach
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </details>
                                                @endif
                                            </fieldset>

                                            <fieldset class="min-w-0">
                                                <legend class="mb-2 text-sm font-semibold">Warna</legend>
                                                <div class="flex flex-wrap gap-2">
                                                    @foreach ($warnaPilihan as $warna)
                                                        @php
                                                            $stokWarna = $variasiProduk->where('warna', $warna)->sum('stok');
                                                            $aktif = (string) old('warna', '') === (string) $warna;
                                                        @endphp
                                                        <button type="button" data-variant-choice="warna"
                                                            data-value="{{ $warna }}" aria-pressed="{{ $aktif ? 'true' : 'false' }}"
                                                            @disabled($stokWarna < 1)
                                                            class="min-h-10 rounded-md border px-3 text-sm font-medium transition focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand disabled:cursor-not-allowed disabled:opacity-40 {{ $aktif ? 'border-brand bg-brand text-white' : 'border-line bg-white text-ink hover:border-brand' }}">
                                                            {{ $warna }}
                                                        </button>
                                                    @endforeach
                                                </div>
                                            </fieldset>
                                        </div>

                                        <div data-variant-select-fallback class="grid gap-3 sm:grid-cols-2">
                                            <div>
                                                <label for="ukuran" class="mb-1 block text-sm font-semibold">Ukuran</label>
                                                <select id="ukuran" name="ukuran" data-variant-select="ukuran" required
                                                    class="h-11 w-full rounded-md border border-line bg-white px-3 text-sm focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/20">
                                                    <option value="">Pilih ukuran</option>
                                                    @foreach ($ukuranPilihan as $ukuran)
                                                        <option value="{{ $ukuran }}"
                                                            @selected((string) old('ukuran', '') === (string) $ukuran)
                                                            @disabled($variasiProduk->where('ukuran', $ukuran)->sum('stok') < 1)>
                                                            {{ $ukuran }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div>
                                                <label for="warna" class="mb-1 block text-sm font-semibold">Warna</label>
                                                <select id="warna" name="warna" data-variant-select="warna" required
                                                    class="h-11 w-full rounded-md border border-line bg-white px-3 text-sm focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/20">
                                                    <option value="">Pilih warna</option>
                                                    @foreach ($warnaPilihan as $warna)
                                                        <option value="{{ $warna }}"
                                                            @selected((string) old('warna', '') === (string) $warna)
                                                            @disabled($variasiProduk->where('warna', $warna)->sum('stok') < 1)>
                                                            {{ $warna }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        @error('ukuran')
                                            <p role="alert" class="text-sm text-rose-700">{{ $message }}</p>
                                        @enderror
                                        @error('warna')
                                            <p role="alert" class="text-sm text-rose-700">{{ $message }}</p>
                                        @enderror
                                        <p data-variant-stock role="status" aria-live="polite" class="text-xs text-muted"></p>
                                        <p data-variant-error role="alert" hidden class="text-sm text-rose-700">
                                            Pilih ukuran dan warna yang tersedia sebelum melanjutkan.
                                        </p>
                                        <script type="application/json" data-variant-data>{!! json_encode($dataVariasi, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!}</script>
                                    </div>
                                @endif

                                <div class="flex flex-col gap-3 sm:flex-row sm:items-end">
                                    <div class="w-full sm:w-24 sm:shrink-0">
                                        <label for="jumlah" class="mb-1 block text-xs font-semibold text-muted">Jumlah</label>
                                        <input id="jumlah" name="jumlah" type="number" min="1" max="{{ $produk->stok }}"
                                            data-product-stock="{{ $produk->stok }}"
                                            value="1"
                                            class="h-11 w-full rounded-lg border border-line px-3 text-sm focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/20">
                                    </div>
                                    <div class="grid flex-1 gap-2 sm:grid-cols-2">
                                        <button type="submit" formaction="{{ route('checkout.direct') }}"
                                            class="min-h-11 rounded-lg bg-brand px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-brand-dark active:scale-[0.98]">
                                            Beli Sekarang
                                        </button>
                                        <button type="submit"
                                            class="min-h-11 rounded-lg border border-brand bg-white px-5 py-2.5 text-sm font-semibold text-brand transition hover:bg-brand-light active:scale-[0.98]">
                                            Tambah ke Keranjang
                                        </button>
                                    </div>
                                </div>
                            </form>
                        @elseif (auth()->guest())
                            <a href="{{ route('login') }}"
                                class="inline-flex min-h-11 w-full items-center justify-center rounded-lg bg-brand px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-brand-dark sm:w-fit">
                                Masuk untuk Membeli
                            </a>
                        @endif
                        @error('jumlah')
                            <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>
                @else
                    <button disabled
                        class="mt-6 min-h-11 cursor-not-allowed rounded-lg bg-slate-200 px-5 py-2.5 text-sm font-semibold text-slate-500 sm:w-fit">
                        Stok Habis
                    </button>
                @endif

                @if ($pelanggan)
                    <form action="{{ route('produk.favorit', $produk) }}" method="POST"
                        class="mt-3 {{ $produk->stok > 0 ? 'sm:text-right' : '' }}">
                        @csrf
                        <button type="submit"
                            class="min-h-11 rounded-lg border border-line px-4 py-2 text-sm font-semibold transition hover:border-brand hover:text-brand {{ $favorit ? 'text-rose-600' : 'text-ink' }}">
                            {{ $favorit ? 'Hapus dari Favorit' : 'Tambah ke Favorit' }}
                        </button>
                    </form>
                @endif
            </div>
        </section>

        @if ($gambarProduk !== [])
            <div data-gallery-modal id="galeri-produk-{{ $produk->id }}"
                class="fixed inset-0 z-[60] hidden items-center justify-center bg-ink/70 p-2 sm:p-4"
                aria-hidden="true">
                <div data-gallery-dialog role="dialog" aria-modal="true"
                    aria-labelledby="judul-galeri-produk-{{ $produk->id }}" tabindex="-1"
                    class="flex h-[calc(100dvh-1rem)] max-h-[56rem] w-full max-w-[1280px] flex-col overflow-hidden rounded-lg bg-white shadow-float sm:h-[calc(100dvh-2rem)]">
                    <header class="flex items-start justify-between gap-4 border-b border-line px-4 py-3 sm:px-5">
                        <div class="min-w-0">
                            <h2 id="judul-galeri-produk-{{ $produk->id }}"
                                class="line-clamp-2 text-sm font-bold text-ink sm:text-base">
                                {{ $produk->nama }}
                            </h2>
                            <p data-gallery-modal-counter aria-live="polite" class="mt-1 text-xs text-muted">
                                Foto 1 dari {{ count($gambarProduk) }}
                            </p>
                        </div>
                        <button type="button" data-gallery-close aria-label="Tutup galeri foto"
                            class="inline-flex min-h-11 min-w-11 shrink-0 items-center justify-center rounded-md border border-line text-ink transition hover:bg-canvas focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand">
                            <svg viewBox="0 0 20 20" class="size-5" fill="currentColor" aria-hidden="true">
                                <path
                                    d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" />
                            </svg>
                        </button>
                    </header>

                    <div class="grid min-h-0 flex-1 grid-rows-[minmax(0,1fr)_auto] lg:grid-cols-[minmax(0,1fr)_17rem] lg:grid-rows-1">
                        <div class="relative flex min-h-0 min-w-0 items-center justify-center bg-canvas p-3 sm:p-5">
                            @if (count($gambarProduk) > 1)
                                <button type="button" data-gallery-prev aria-label="Tampilkan foto sebelumnya"
                                    class="absolute left-2 top-1/2 z-10 inline-flex size-11 -translate-y-1/2 items-center justify-center rounded-md border border-line bg-white text-brand shadow-soft transition hover:bg-brand hover:text-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand sm:left-4">
                                    <svg viewBox="0 0 20 20" class="size-6" fill="currentColor" aria-hidden="true">
                                        <path fill-rule="evenodd"
                                            d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </button>
                            @endif

                            <img data-gallery-large-image src="{{ asset('storage/' . $gambarProduk[0]) }}"
                                alt="{{ $produk->nama }}" class="h-full w-full object-contain">

                            @if (count($gambarProduk) > 1)
                                <button type="button" data-gallery-next aria-label="Tampilkan foto berikutnya"
                                    class="absolute right-2 top-1/2 z-10 inline-flex size-11 -translate-y-1/2 items-center justify-center rounded-md border border-line bg-white text-brand shadow-soft transition hover:bg-brand hover:text-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand sm:right-4">
                                    <svg viewBox="0 0 20 20" class="size-6" fill="currentColor" aria-hidden="true">
                                        <path fill-rule="evenodd"
                                            d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </button>
                            @endif
                        </div>

                        <aside class="min-h-0 max-h-32 overflow-x-auto border-t border-line p-3 lg:max-h-none lg:overflow-y-auto lg:border-l lg:border-t-0">
                            <p class="mb-2 text-xs font-semibold text-muted">Semua Foto ({{ count($gambarProduk) }})</p>
                            <div data-gallery-modal-strip
                                class="flex gap-2 overflow-x-auto pb-1 lg:grid lg:grid-cols-3 lg:content-start lg:overflow-x-visible lg:overflow-y-auto">
                                @foreach ($gambarProduk as $gambar)
                                    <button type="button" data-gallery-modal-thumb data-gallery-index="{{ $loop->index }}"
                                        aria-label="Tampilkan foto {{ $loop->iteration }} dari {{ count($gambarProduk) }}"
                                        aria-pressed="{{ $loop->first ? 'true' : 'false' }}"
                                        class="h-16 w-16 shrink-0 overflow-hidden rounded-md border-2 {{ $loop->first ? 'border-brand ring-2 ring-brand/20' : 'border-line hover:border-brand' }} focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand lg:h-auto lg:w-auto lg:aspect-square">
                                        <img src="{{ asset('storage/' . $gambar) }}"
                                            alt="Foto {{ $loop->iteration }} {{ $produk->nama }}" loading="lazy"
                                            class="h-full w-full bg-canvas object-cover">
                                    </button>
                                @endforeach
                            </div>
                        </aside>
                    </div>
                </div>
            </div>
        @endif

        <section class="mt-6 rounded-lg border border-line bg-white p-5 md:p-6">
            <h2 class="mb-4 text-lg font-bold text-ink">Spesifikasi Produk</h2>
            <dl class="divide-y divide-line text-sm sm:max-w-2xl">
                <div class="grid grid-cols-[7rem_1fr] gap-4 py-3 sm:grid-cols-[10rem_1fr]">
                    <dt class="text-muted">Kategori</dt>
                    <dd class="font-medium text-ink">{{ $produk->kategori->nama }}</dd>
                </div>
                <div class="grid grid-cols-[7rem_1fr] gap-4 py-3 sm:grid-cols-[10rem_1fr]">
                    <dt class="text-muted">Ketersediaan</dt>
                    <dd class="font-medium text-ink">
                        {{ $produk->stok > 0 ? $produk->stok . ' tersedia' : 'Stok habis' }}
                    </dd>
                </div>
            </dl>
        </section>

        <section class="mt-5 rounded-lg border border-line bg-white p-5 md:p-6">
            <h2 class="mb-4 text-lg font-bold text-ink">Deskripsi Produk</h2>
            @if ($produk->deskripsi)
                <p class="whitespace-pre-line text-sm leading-7 text-muted">{{ $produk->deskripsi }}</p>
            @else
                <p class="text-sm text-muted">Deskripsi produk belum tersedia.</p>
            @endif
        </section>

        <section id="ulasan-produk" class="mt-6 rounded-lg border border-line bg-white p-5 md:p-6">
            <h2 class="mb-3 text-lg font-bold">Ulasan Produk</h2>
            @forelse($ulasan as $item)
                <article class="border-t border-line py-4 text-sm">
                    <div class="flex flex-wrap items-start justify-between gap-2">
                        <div>
                            <p class="font-semibold">{{ $item->user->name }}</p>
                            <time datetime="{{ $item->created_at->toDateString() }}" class="mt-0.5 block text-xs text-muted">
                                Diulas {{ $item->created_at->format('d/m/Y') }}
                            </time>
                        </div>
                        <p class="font-semibold">
                            <span class="sr-only">Penilaian {{ $item->rating }} dari 5</span>
                            <span class="text-amber-500"
                                aria-hidden="true">{{ str_repeat('★', $item->rating) }}{{ str_repeat('☆', 5 - $item->rating) }}</span>
                        </p>
                    </div>
                    @if ($item->komentar)
                        <p class="mt-1 text-muted">{{ $item->komentar }}</p>
                    @endif
                    @if ($item->foto)
                        <div class="mt-3 flex flex-wrap gap-2">
                            @foreach ($item->foto as $index => $foto)
                                <button type="button" 
                                    onclick="openReviewLightbox({{ $loop->parent->index }}, {{ $index }})"
                                    class="h-24 w-24 overflow-hidden rounded-md border border-line object-cover transition hover:opacity-90 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand"
                                    aria-label="Lihat foto ulasan oleh {{ $item->user->name }}">
                                    <img src="{{ asset('storage/' . $foto) }}" alt="Foto ulasan oleh {{ $item->user->name }}"
                                        class="h-full w-full object-cover">
                                </button>
                            @endforeach
                        </div>
                    @endif
                </article>
            @empty
                <p class="text-sm text-muted">Belum ada ulasan.</p>
            @endforelse
        </section>

        @if ($pelanggan)
            <section class="mt-5 rounded-lg border border-line bg-white p-5">
                <h2 class="mb-3 text-lg font-bold">Bagikan Pengalaman Anda</h2>
                @if ($pesananSelesai->isEmpty())
                    <p class="text-sm text-muted">Ulasan dapat diberikan setelah pesanan produk ini selesai.</p>
                @else
                    <div class="space-y-5">
                        @foreach ($pesananSelesai as $pesananSelesaiItem)
                            @php
                                $ulasanSebelumnya = $ulasanSaya->get($pesananSelesaiItem->id);
                                $formIni = (string) old('pesanan_id') === (string) $pesananSelesaiItem->id;
                            @endphp
                            <form action="{{ route('produk.ulasan', $produk) }}" method="POST" enctype="multipart/form-data"
                                class="border-t border-line pt-4 first:border-t-0 first:pt-0">
                                @csrf
                                <input type="hidden" name="pesanan_id" value="{{ $pesananSelesaiItem->id }}">
                                <p class="mb-3 text-sm font-semibold">
                                    Pesanan {{ $pesananSelesaiItem->kode_pesanan }}
                                    @if ($ulasanSebelumnya)
                                        <span class="font-normal text-muted"> ulasan Anda dapat diperbarui</span>
                                    @endif
                                </p>
                                <div class="grid gap-3 sm:grid-cols-2">
                                    <label for="rating-{{ $pesananSelesaiItem->id }}" class="block text-sm font-semibold">
                                        Penilaian
                                        <select id="rating-{{ $pesananSelesaiItem->id }}" name="rating" required
                                            class="mt-1 w-full rounded-lg border border-line px-3 py-2.5">
                                            @for ($rating = 5; $rating >= 1; $rating--)
                                                <option value="{{ $rating }}" @selected(($formIni ? old('rating') : $ulasanSebelumnya?->rating ?? 5) == $rating)>
                                                    {{ $rating }} dari 5
                                                </option>
                                            @endfor
                                        </select>
                                        @if ($formIni)
                                            @error('rating')
                                                <span class="mt-1 block text-sm text-rose-700">{{ $message }}</span>
                                            @enderror
                                        @endif
                                    </label>
                                    <label for="komentar-{{ $pesananSelesaiItem->id }}" class="block text-sm font-semibold">
                                        Komentar (opsional)
                                        <textarea id="komentar-{{ $pesananSelesaiItem->id }}" name="komentar" rows="2" maxlength="1000"
                                            class="mt-1 w-full rounded-lg border border-line px-3 py-2.5 font-normal">{{ $formIni ? old('komentar') : $ulasanSebelumnya?->komentar }}</textarea>
                                        @if ($formIni)
                                            @error('komentar')
                                                <span class="mt-1 block text-sm text-rose-700">{{ $message }}</span>
                                            @enderror
                                        @endif
                                    </label>
                                </div>
                                @if ($ulasanSebelumnya?->foto)
                                    <div class="mt-3">
                                        <p class="mb-2 text-xs font-semibold text-muted">Foto ulasan saat ini</p>
                                        <div class="flex flex-wrap gap-2">
                                            @foreach ($ulasanSebelumnya->foto as $foto)
                                                <img src="{{ asset('storage/' . $foto) }}" alt="Foto ulasan saat ini"
                                                    class="h-16 w-16 rounded-md border border-line object-cover">
                                            @endforeach
                                        </div>
                                        <p class="mt-2 text-xs text-muted">Mengunggah foto baru akan mengganti foto saat ini.
                                        </p>
                                    </div>
                                @endif
                                <div class="mt-3">
                                    <label for="foto-{{ $pesananSelesaiItem->id }}" class="block text-sm font-semibold">
                                        Foto produk (opsional)
                                    </label>
                                    <input id="foto-{{ $pesananSelesaiItem->id }}" name="foto[]" type="file"
                                        accept="image/jpeg,image/png,image/webp" multiple
                                        class="mt-1 w-full rounded-lg border border-line px-3 py-2 text-sm file:mr-3 file:rounded-md file:border-0 file:bg-brand file:px-3 file:py-1.5 file:font-semibold file:text-white">
                                    <p class="mt-1 text-xs text-muted">Maksimal 3 foto JPG, PNG, atau WebP, masing-masing
                                        hingga 2 MB.</p>
                                    @if ($formIni)
                                        @error('foto')
                                            <p class="mt-1 text-sm text-rose-700">{{ $message }}</p>
                                        @enderror
                                        @error('foto.0')
                                            <p class="mt-1 text-sm text-rose-700">{{ $message }}</p>
                                        @enderror
                                        @error('foto.1')
                                            <p class="mt-1 text-sm text-rose-700">{{ $message }}</p>
                                        @enderror
                                        @error('foto.2')
                                            <p class="mt-1 text-sm text-rose-700">{{ $message }}</p>
                                        @enderror
                                    @endif
                                </div>
                                <button type="submit"
                                    class="mt-3 min-h-11 rounded-lg bg-brand px-4 py-2 text-sm font-semibold text-white hover:bg-brand-dark">
                                    {{ $ulasanSebelumnya ? 'Perbarui Ulasan' : 'Kirim Ulasan' }}
                                </button>
                            </form>
                        @endforeach
                    </div>
                @endif
            </section>
        @endif

        @if ($terkait->isNotEmpty())
            <section class="mt-12">
                <h2 class="mb-4 text-lg font-bold">Produk Terkait</h2>
                <ul class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5">
                    @foreach ($terkait as $item)
                        <li class="min-w-0">
                            <a href="{{ route('produk.show', $item) }}"
                                class="group flex h-full min-w-0 flex-col overflow-hidden rounded-lg border border-line bg-white transition duration-200 hover:border-brand/40 hover:shadow-soft focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand">
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
                                                <span class="truncate" aria-hidden="true">({{ $item->ulasan_produk_count }})</span>
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
        @endif

        <!-- Review Lightbox -->
        <div id="review-lightbox" class="fixed inset-0 z-[70] hidden items-center justify-center bg-black/80 p-4 backdrop-blur-sm transition-opacity" aria-hidden="true">
            <div class="relative flex h-full max-h-[90vh] w-full max-w-4xl flex-col items-center justify-center">
                <!-- Close Button -->
                <button type="button" onclick="closeReviewLightbox()" class="absolute -top-10 right-0 text-white hover:text-gray-300 focus:outline-none" aria-label="Tutup lightbox">
                    <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>

                <!-- Navigation Prev -->
                <button type="button" onclick="changeReviewImage(-1)" class="absolute left-0 top-1/2 -translate-y-1/2 rounded-full bg-white/10 p-2 text-white hover:bg-white/20 focus:outline-none" aria-label="Foto sebelumnya">
                    <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </button>

                <!-- Image Container -->
                <div class="relative flex h-full w-full items-center justify-center p-4">
                    <img id="review-lightbox-img" src="" alt="Foto ulasan" class="max-h-full max-w-full rounded-lg object-contain shadow-2xl">
                    <p id="review-lightbox-counter" class="absolute bottom-0 left-1/2 -translate-x-1/2 rounded-full bg-black/50 px-3 py-1 text-xs text-white"></p>
                </div>

                <!-- Navigation Next -->
                <button type="button" onclick="changeReviewImage(1)" class="absolute right-0 top-1/2 -translate-y-1/2 rounded-full bg-white/10 p-2 text-white hover:bg-white/20 focus:outline-none" aria-label="Foto berikutnya">
                    <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </button>
            </div>
        </div>

        <script>
            // Data foto ulasan dari backend
            const reviewPhotos = @json($ulasan->map(fn($u) => collect($u->foto ?? [])->map(fn($f) => asset('storage/' . $f))->values())->toArray());
            let currentReviewIndex = 0;
            let currentPhotoIndex = 0;

            function openReviewLightbox(reviewIndex, photoIndex) {
                currentReviewIndex = reviewIndex;
                currentPhotoIndex = photoIndex;
                updateLightboxImage();
                
                const lightbox = document.getElementById('review-lightbox');
                lightbox.classList.remove('hidden');
                lightbox.classList.add('flex');
                document.body.style.overflow = 'hidden'; // Prevent background scrolling
            }

            function closeReviewLightbox() {
                const lightbox = document.getElementById('review-lightbox');
                lightbox.classList.add('hidden');
                lightbox.classList.remove('flex');
                document.body.style.overflow = ''; // Restore background scrolling
            }

            function changeReviewImage(direction) {
                const photos = reviewPhotos[currentReviewIndex] || [];
                if (photos.length <= 1) return;

                currentPhotoIndex += direction;
                if (currentPhotoIndex < 0) {
                    currentPhotoIndex = photos.length - 1;
                } else if (currentPhotoIndex >= photos.length) {
                    currentPhotoIndex = 0;
                }
                updateLightboxImage();
            }

            function updateLightboxImage() {
                const photos = reviewPhotos[currentReviewIndex] || [];
                const img = document.getElementById('review-lightbox-img');
                const counter = document.getElementById('review-lightbox-counter');
                
                if (photos[currentPhotoIndex]) {
                    img.src = photos[currentPhotoIndex];
                    counter.textContent = `${currentPhotoIndex + 1} / ${photos.length}`;
                }
            }

            // Close on background click
            document.getElementById('review-lightbox').addEventListener('click', function(e) {
                if (e.target === this) {
                    closeReviewLightbox();
                }
            });

            // Close on Escape key
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    closeReviewLightbox();
                } else if (e.key === 'ArrowLeft') {
                    changeReviewImage(-1);
                } else if (e.key === 'ArrowRight') {
                    changeReviewImage(1);
                }
            });
        </script>
    @endsection
