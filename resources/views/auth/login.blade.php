@extends('layouts.app')

@section('judul', 'Masuk')

@section('konten')
    <div class="mx-auto max-w-md">
        <div class="rounded-lg border border-line bg-white p-6 md:p-8">
            <h1 class="text-2xl font-bold tracking-tight">Masuk ke Beli.</h1>
            <p class="mt-1 text-sm text-muted">Silakan masuk untuk melanjutkan belanja.</p>

            @if ($errors->any())
                <div class="mt-4 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                    {{ $errors->first() }}
                </div>
            @endif

            <form action="{{ route('login') }}" method="POST" class="mt-5 space-y-4">
                @csrf
                <div>
                    <label for="email" class="mb-1 block text-sm font-semibold">Email</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus
                        class="w-full rounded-lg border border-line px-3.5 py-2.5 text-sm focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/20">
                </div>
                <div>
                    <label for="password" class="mb-1 block text-sm font-semibold">Kata Sandi</label>
                    <input id="password" name="password" type="password" required
                        class="w-full rounded-lg border border-line px-3.5 py-2.5 text-sm focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/20">
                </div>
                <label class="flex items-center gap-2 text-sm text-muted">
                    <input type="checkbox" name="ingat" value="1" class="rounded border-line text-brand focus:ring-brand">
                    Ingat saya
                </label>
                <button type="submit" class="w-full rounded-lg bg-brand px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-brand-dark active:scale-[0.98]">Masuk</button>
            </form>

            <p class="mt-4 text-center text-sm text-muted">Belum punya akun? <a href="{{ route('daftar') }}" class="font-semibold text-brand hover:underline">Daftar</a></p>
        </div>
    </div>
@endsection
