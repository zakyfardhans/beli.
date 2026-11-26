<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#0D9488">
    <title>@yield('judul', 'Beli.') - Beli.</title>
    <meta name="description" content="@yield('meta_deskripsi', 'Beli. - toko online kebutuhan elektronik, fashion, rumah tangga, dan kecantikan.')">
    <link rel="canonical" href="{{ url()->current() }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen flex flex-col bg-canvas text-ink font-sans antialiased">
    @include('partials.navbar')

    @if (session('sukses'))
        <div class="mx-auto mt-4 max-w-[1280px] px-4 md:px-8 xl:px-12">
            <div
                class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
                {{ session('sukses') }}
            </div>
        </div>
    @endif

    @if (session('galat'))
        <div class="mx-auto mt-4 max-w-[1280px] px-4 md:px-8 xl:px-12">
            <div class="rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-700">
                {{ session('galat') }}
            </div>
        </div>
    @endif

    <main class="mx-auto w-full max-w-[1280px] flex-1 px-4 py-8 md:px-8 md:py-12 xl:px-12 pb-20 md:pb-0">
        @yield('konten')
    </main>

    <footer class="border-t border-line bg-white py-8 mt-12">
        <div class="mx-auto max-w-[1280px] px-4 md:px-8 xl:px-12 text-center text-sm text-muted">
            &copy; {{ date('Y') }} PT. Beli. All Rights Reserved.
        </div>
    </footer>
</body>

</html>
