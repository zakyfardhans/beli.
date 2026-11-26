@extends('layouts.app')

@section('judul', 'Daftar')

@section('konten')
    <div class="mx-auto max-w-md">
        <div class="rounded-lg border border-line bg-white p-6 md:p-8">
            <h1 class="text-2xl font-bold tracking-tight">Buat Akun</h1>
            <p class="mt-1 text-sm text-muted">Daftar untuk mulai berbelanja di Beli.</p>

            @if ($errors->any())
                <div class="mt-4 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                    <ul class="list-inside list-disc space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('daftar') }}" method="POST" class="mt-5 space-y-4">
                @csrf
                <div>
                    <label for="name" class="mb-1 block text-sm font-semibold">Nama Lengkap</label>
                    <input id="name" name="name" type="text" value="{{ old('name') }}" required autofocus
                        class="w-full rounded-lg border border-line px-3.5 py-2.5 text-sm focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/20">
                </div>
                <div>
                    <label for="email" class="mb-1 block text-sm font-semibold">Email</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required
                        class="w-full rounded-lg border border-line px-3.5 py-2.5 text-sm focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/20">
                </div>
                <div>
                    <label for="no_hp" class="mb-1 block text-sm font-semibold">Nomor HP</label>
                    <input id="no_hp" name="no_hp" type="text" value="{{ old('no_hp') }}" placeholder="08xxxxxxxxxx" required
                        class="w-full rounded-lg border border-line px-3.5 py-2.5 text-sm focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/20">
                </div>
                <div>
                    <label for="alamat" class="mb-1 block text-sm font-semibold">Alamat</label>
                    <textarea id="alamat" name="alamat" rows="2" required
                        class="w-full rounded-lg border border-line px-3.5 py-2.5 text-sm focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/20">{{ old('alamat') }}</textarea>
                </div>
                <div>
                    <label for="password" class="mb-1 block text-sm font-semibold">Kata Sandi (min. 8 karakter)</label>
                    <input id="password" name="password" type="password" required
                        class="w-full rounded-lg border border-line px-3.5 py-2.5 text-sm focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/20">
                </div>
                <div>
                    <label for="password_confirmation" class="mb-1 block text-sm font-semibold">Konfirmasi Kata Sandi</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" required
                        class="w-full rounded-lg border border-line px-3.5 py-2.5 text-sm focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/20">
                </div>
                <button type="submit" class="w-full rounded-lg bg-brand px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-brand-dark active:scale-[0.98]">Daftar</button>
            </form>

            <p class="mt-4 text-center text-sm text-muted">Sudah punya akun? <a href="{{ route('login') }}" class="font-semibold text-brand hover:underline">Masuk</a></p>
        </div>
    </div>
@endsection
