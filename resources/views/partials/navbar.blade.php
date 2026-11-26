<header class="sticky top-0 z-30 border-b border-line bg-white">

    <div class="mx-auto flex h-14 max-w-full items-center gap-3 px-4 md:gap-5 md:px-8 xl:px-12">

        {{-- Brand --}}
        <a href="{{ route('katalog') }}" class="flex shrink-0 items-center gap-1.5" aria-label="Beli beranda">

            <span class="flex h-9 w-9 items-center justify-center text-brand">
                <svg viewBox="0 0 48 48" class="h-7 w-7" fill="none" aria-hidden="true">
                    <path d="M13 17.5h22l2 23H11l2-23Z" stroke="currentColor" stroke-width="2.6"
                        stroke-linejoin="round" />
                    <path d="M18 18v-3.5a6 6 0 0 1 12 0V18" stroke="currentColor" stroke-width="2.6"
                        stroke-linecap="round" />
                </svg>
            </span>

            <span class="text-xl font-extrabold tracking-tight text-ink md:text-2xl">
                beli<span class="text-brand">.</span>
            </span>
        </a>

        {{-- Search --}}
        <form action="{{ route('katalog') }}" method="GET" class="min-w-0 flex-1">

            <div
                class="flex h-9 items-center overflow-hidden rounded-md border border-line bg-white transition focus-within:border-brand focus-within:ring-2 focus-within:ring-brand/20 md:h-10">

                <input name="q" value="{{ request('q') }}" type="search" placeholder="Cari produk..."
                    aria-label="Cari produk"
                    class="h-full min-w-0 flex-1 border-0 bg-transparent px-3 text-sm text-ink placeholder:text-muted focus:outline-none">

                <button type="submit"
                    class="mr-1 flex h-7 w-8 shrink-0 items-center justify-center rounded-md bg-brand text-white transition hover:bg-brand-dark md:h-8 md:w-9"
                    aria-label="Cari">

                    <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="7" />
                        <path d="m21 21-4.3-4.3" />
                    </svg>
                </button>

            </div>
        </form>

        {{-- Cart Desktop --}}
        @auth
            @if (auth()->user()->isPelanggan())
                <a href="{{ route('keranjang.index') }}"
                    class="relative hidden h-9 w-9 shrink-0 items-center justify-center rounded-lg text-ink transition hover:bg-brand-light hover:text-brand md:flex"
                    aria-label="Keranjang belanja">

                    <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="9" cy="21" r="1" />
                        <circle cx="20" cy="21" r="1" />
                        <path d="M1 1h4l2.7 13.4a2 2 0 0 0 2 1.6h9.7a2 2 0 0 0 2-1.6L23 6H6" />
                    </svg>

                    @if ($jumlahKeranjang > 0)
                        <span
                            class="absolute right-0 top-0 flex h-4 min-w-4 items-center justify-center rounded-full bg-brand px-1 text-[9px] font-bold text-white">
                            {{ $jumlahKeranjang }}
                        </span>
                    @endif
                </a>
            @endif
        @endauth

        {{-- Desktop Navigation --}}
        <nav class="hidden items-center gap-5 text-sm font-semibold md:flex">

            @guest
                <a href="{{ route('login') }}" class="text-ink transition hover:text-brand">
                    Masuk
                </a>

                <a href="{{ route('daftar') }}"
                    class="rounded-lg bg-brand px-4 py-2 text-white transition hover:bg-brand-dark">
                    Daftar
                </a>
            @else
                @if (auth()->user()->isAdmin())
                    <a href="{{ route('admin.dashboard') }}" class="text-ink transition hover:text-brand">
                        Dashboard
                    </a>
                @else
                    <a href="{{ route('pesanan.index') }}" class="text-ink transition hover:text-brand">
                        Pesanan
                    </a>

                    <a href="{{ route('favorit.index') }}" class="text-ink transition hover:text-brand">
                        Favorit
                    </a>

                    <a href="{{ route('profil.index') }}" class="text-ink transition hover:text-brand">
                        Profil
                    </a>
                @endif

                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="text-rose-600 hover:text-rose-700">
                        Keluar
                    </button>
                </form>
            @endguest

        </nav>

    </div>
</header>


{{-- ========================================= --}}
{{-- MOBILE BOTTOM NAVIGATION --}}
{{-- ========================================= --}}

<nav
    class="fixed bottom-0 left-0 right-0 z-50 border-t border-line bg-white shadow-[0_-4px_15px_rgba(0,0,0,0.06)] md:hidden">

    <div class="mx-auto grid h-16 max-w-lg grid-cols-5 items-center px-2">

        {{-- Beranda --}}
        <a href="{{ route('katalog') }}"
            class="flex flex-col items-center justify-center gap-1 py-2 text-xs transition
            {{ request()->routeIs('katalog') ? 'text-brand' : 'text-muted hover:text-brand' }}">

            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="m3 10 9-7 9 7v10a1 1 0 0 1-1 1h-5v-7H9v7H4a1 1 0 0 1-1-1V10Z" />
            </svg>

            <span class="font-semibold">Beranda</span>
        </a>


        @guest

            {{-- Masuk --}}
            <a href="{{ route('login') }}"
                class="flex flex-col items-center justify-center gap-1 py-2 text-xs text-muted transition hover:text-brand">

                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4M10 17l5-5-5-5m5 5H3" />
                </svg>

                <span class="font-semibold">Masuk</span>
            </a>

            {{-- Daftar --}}
            <a href="{{ route('daftar') }}"
                class="flex flex-col items-center justify-center gap-1 py-2 text-xs text-muted transition hover:text-brand">

                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                    <circle cx="12" cy="8" r="4" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 21a7 7 0 0 1 14 0M19 8h4m-2-2v4" />
                </svg>

                <span class="font-semibold">Daftar</span>
            </a>
        @else
            @if (auth()->user()->isAdmin())
                {{-- Dashboard --}}
                <a href="{{ route('admin.dashboard') }}"
                    class="flex flex-col items-center justify-center gap-1 py-2 text-xs transition
                    {{ request()->routeIs('admin.dashboard') ? 'text-brand' : 'text-muted hover:text-brand' }}">

                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                        <rect x="3" y="3" width="7" height="7" rx="1" />
                        <rect x="14" y="3" width="7" height="7" rx="1" />
                        <rect x="14" y="14" width="7" height="7" rx="1" />
                        <rect x="3" y="14" width="7" height="7" rx="1" />
                    </svg>

                    <span class="font-semibold">Dashboard</span>
                </a>
            @else
                {{-- Pesanan --}}
                <a href="{{ route('pesanan.index') }}"
                    class="flex flex-col items-center justify-center gap-1 py-2 text-xs transition
                    {{ request()->routeIs('pesanan.*') ? 'text-brand' : 'text-muted hover:text-brand' }}">

                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l2.5 12h11L21 7H6" />
                        <circle cx="9" cy="20" r="1" />
                        <circle cx="18" cy="20" r="1" />
                    </svg>

                    <span class="font-semibold">Pesanan</span>
                </a>

                {{-- Favorit --}}
                <a href="{{ route('favorit.index') }}"
                    class="flex flex-col items-center justify-center gap-1 py-2 text-xs transition
                    {{ request()->routeIs('favorit.*') ? 'text-brand' : 'text-muted hover:text-brand' }}">

                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.7l-1.1-1.1a5.5 5.5 0 0 0-7.8 7.8l1.1 1.1L12 21l7.8-7.5 1.1-1.1a5.5 5.5 0 0 0-.1-7.8Z" />
                    </svg>

                    <span class="font-semibold">Favorit</span>
                </a>

                {{-- Keranjang --}}
                <a href="{{ route('keranjang.index') }}"
                    class="relative flex flex-col items-center justify-center gap-1 py-2 text-xs transition
                    {{ request()->routeIs('keranjang.*') ? 'text-brand' : 'text-muted hover:text-brand' }}">

                    <span class="relative">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            stroke-width="1.8">
                            <circle cx="9" cy="21" r="1" />
                            <circle cx="20" cy="21" r="1" />
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M1 1h4l2.7 13.4a2 2 0 0 0 2 1.6h9.7a2 2 0 0 0 2-1.6L23 6H6" />
                        </svg>

                        @if ($jumlahKeranjang > 0)
                            <span
                                class="absolute -right-2 -top-2 flex h-4 min-w-4 items-center justify-center rounded-full bg-brand px-1 text-[9px] font-bold text-white">
                                {{ $jumlahKeranjang }}
                            </span>
                        @endif
                    </span>

                    <span class="font-semibold">Keranjang</span>
                </a>
            @endif

            {{-- Profil --}}
            <a href="{{ route('profil.index') }}"
                class="flex flex-col items-center justify-center gap-1 py-2 text-xs transition
                {{ request()->routeIs('profil.*') ? 'text-brand' : 'text-muted hover:text-brand' }}">

                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                    <circle cx="12" cy="8" r="4" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 21a7 7 0 0 1 14 0" />
                </svg>

                <span class="font-semibold">Profil</span>
            </a>

        @endguest

    </div>
</nav>
