@extends('layouts.app')

@section('judul', 'Halaman Tidak Ditemukan')

@section('konten')
    <div class="mx-auto max-w-md py-16 text-center">
        <p class="text-6xl font-extrabold text-brand">404</p>
        <h1 class="mt-4 text-2xl font-bold tracking-tight">Halaman tidak ditemukan</h1>
        <p class="mt-2 text-sm text-muted">Halaman yang Anda cari mungkin telah dihapus atau alamatnya salah.</p>
        <a href="{{ route('katalog') }}" class="mt-6 inline-block rounded-lg bg-brand px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-brand-dark">Kembali ke Katalog</a>
    </div>
@endsection
