@extends('layouts.admin')

@section('judul', 'Edit Produk')

@section('konten')
    <div class="mx-auto max-w-2xl">
        <div class="rounded-lg border border-line bg-white p-6">
            <form action="{{ route('admin.produk.update', $produk) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                @include('admin.produk._form')

                <div class="mt-6 flex items-center gap-3">
                    <button type="submit" class="rounded-lg bg-brand px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-brand-dark active:scale-[0.98]">Perbarui</button>
                    <a href="{{ route('admin.produk.index') }}" class="rounded-lg border border-line px-5 py-2.5 text-sm font-semibold hover:bg-canvas">Batal</a>
                </div>
            </form>
        </div>
    </div>
@endsection
