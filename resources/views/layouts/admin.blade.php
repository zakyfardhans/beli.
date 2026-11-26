<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('judul', 'Admin') - Beli.</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-canvas text-ink font-sans antialiased">
    <div class="flex min-h-screen">
        {{-- Sidebar --}}
        <aside id="admin-sidebar"
            class="hidden fixed inset-x-0 top-16 z-40 max-h-[calc(100vh-4rem)] overflow-y-auto border-b border-line bg-white p-4 shadow-float md:static md:block md:max-h-none md:w-60 md:shrink-0 md:overflow-visible md:border-b-0 md:border-r md:shadow-none"
            aria-label="Navigasi admin">
            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2 px-2 py-3">
                <span
                    class="flex h-9 w-9 items-center justify-center rounded-lg bg-brand text-white text-sm font-extrabold">B</span>
                <span class="text-lg font-extrabold tracking-tight">beli<span class="text-brand">.</span> <span
                        class="text-xs font-medium text-muted">admin</span></span>
            </a>
            <nav class="mt-4 space-y-1 text-sm font-semibold">
                <a href="{{ route('admin.dashboard') }}"
                    class="flex items-center gap-2 rounded-lg px-3 py-2.5 {{ request()->routeIs('admin.dashboard') ? 'bg-brand-light text-brand' : 'text-ink hover:bg-canvas' }}">Dashboard</a>
                <a href="{{ route('admin.produk.index') }}"
                    class="flex items-center gap-2 rounded-lg px-3 py-2.5 {{ request()->routeIs('admin.produk.*') ? 'bg-brand-light text-brand' : 'text-ink hover:bg-canvas' }}">Produk</a>
                <a href="{{ route('admin.kategori.index') }}"
                    class="flex items-center gap-2 rounded-lg px-3 py-2.5 {{ request()->routeIs('admin.kategori.*') ? 'bg-brand-light text-brand' : 'text-ink hover:bg-canvas' }}">Kategori</a>
                <a href="{{ route('admin.kupon.index') }}"
                    class="flex items-center gap-2 rounded-lg px-3 py-2.5 {{ request()->routeIs('admin.kupon.*') ? 'bg-brand-light text-brand' : 'text-ink hover:bg-canvas' }}">Kupon</a>
                <a href="{{ route('admin.pesanan.index') }}"
                    class="flex items-center gap-2 rounded-lg px-3 py-2.5 {{ request()->routeIs('admin.pesanan.*') ? 'bg-brand-light text-brand' : 'text-ink hover:bg-canvas' }}">Pesanan</a>
                <a href="{{ route('admin.laporan.penjualan') }}"
                    class="flex items-center gap-2 rounded-lg px-3 py-2.5 text-ink hover:bg-canvas">Laporan CSV</a>
                <a href="{{ route('katalog') }}"
                    class="flex items-center gap-2 rounded-lg px-3 py-2.5 text-ink hover:bg-canvas">Katalog</a>
            </nav>
            <form action="{{ route('logout') }}" method="POST" class="mt-6 border-t border-line pt-4">
                @csrf
                <button type="submit"
                    class="w-full rounded-lg border border-line px-3 py-2 text-sm font-semibold hover:bg-canvas">Keluar</button>
            </form>
        </aside>

        <div class="flex min-w-0 flex-1 flex-col">
            <header class="flex h-16 items-center gap-3 border-b border-line bg-white px-4 md:justify-between md:px-8">
                <button id="admin-menu-button" type="button"
                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg border border-line text-ink transition hover:bg-canvas focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand md:hidden"
                    aria-label="Buka menu admin" aria-controls="admin-sidebar" aria-expanded="false">
                    <svg id="admin-menu-icon" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                    <svg id="admin-close-icon" class="hidden h-5 w-5" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m6 18 12-12M6 6l12 12" />
                    </svg>
                </button>
                <h1 class="min-w-0 flex-1 truncate text-base font-bold tracking-tight md:text-lg">
                    @yield('judul', 'Admin')
                </h1>
                <span class="hidden shrink-0 text-sm text-muted sm:inline">{{ auth()->user()->name }}</span>
            </header>

            <main class="flex-1 p-4 md:p-8">
                @if (session('sukses'))
                    <div
                        class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
                        {{ session('sukses') }}</div>
                @endif
                @if (session('galat'))
                    <div
                        class="mb-4 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-700">
                        {{ session('galat') }}</div>
                @endif

                @yield('konten')
            </main>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const button = document.getElementById('admin-menu-button');
            const sidebar = document.getElementById('admin-sidebar');
            const menuIcon = document.getElementById('admin-menu-icon');
            const closeIcon = document.getElementById('admin-close-icon');

            if (!button || !sidebar || !menuIcon || !closeIcon) return;

            function setMenuOpen(isOpen) {
                sidebar.classList.toggle('hidden', !isOpen);
                button.setAttribute('aria-expanded', String(isOpen));
                button.setAttribute('aria-label', isOpen ? 'Tutup menu admin' : 'Buka menu admin');
                menuIcon.classList.toggle('hidden', isOpen);
                closeIcon.classList.toggle('hidden', !isOpen);
            }

            button.addEventListener('click', function () {
                setMenuOpen(button.getAttribute('aria-expanded') !== 'true');
            });

            document.addEventListener('click', function (event) {
                if (button.getAttribute('aria-expanded') === 'true'
                    && !sidebar.contains(event.target)
                    && !button.contains(event.target)) {
                    setMenuOpen(false);
                }
            });

            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape' && button.getAttribute('aria-expanded') === 'true') {
                    setMenuOpen(false);
                    button.focus();
                }
            });
        });
    </script>
</body>

</html>
