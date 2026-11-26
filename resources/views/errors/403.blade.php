@extends('layouts.app')

@section('judul', 'Akses Ditolak')

@section('konten')
    <div class="mx-auto max-w-md py-16 text-center">
        <p class="text-6xl font-extrabold text-rose-500">403</p>
        <h1 class="mt-4 text-2xl font-bold tracking-tight">Akses ditolak</h1>
        <p class="mt-2 text-sm text-muted">{{ $exception->getMessage() ?: 'Anda tidak memiliki izin untuk mengakses halaman ini.' }}</p>
        <a href="{{ route('katalog') }}" class="mt-6 inline-block rounded-lg bg-brand px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-brand-dark">Kembali ke Katalog</a>
    </div>
@endsection
