@extends('layouts.admin')
@section('judul', 'Tambah Kategori')
@section('konten')
    <form action="{{ route('admin.kategori.store') }}" method="POST" class="max-w-xl rounded-lg border border-line bg-white p-5">
        @csrf
        <label class="block text-sm font-semibold">Nama Kategori
            <input name="nama" value="{{ old('nama') }}" required class="mt-2 w-full rounded-lg border border-line px-3 py-2">
        </label>
        @error('nama')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
        <div class="mt-4 flex gap-2">
            <button type="submit" class="rounded-lg bg-brand px-4 py-2 text-sm font-semibold text-white">Simpan</button>
            <a href="{{ route('admin.kategori.index') }}" class="rounded-lg border border-line px-4 py-2 text-sm font-semibold">Batal</a>
        </div>
    </form>
@endsection
