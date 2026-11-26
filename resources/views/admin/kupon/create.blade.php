@extends('layouts.admin')

@section('judul', 'Tambah Kupon')

@section('konten')
    <form action="{{ route('admin.kupon.store') }}" method="POST"
        class="max-w-2xl space-y-5 rounded-lg border border-line bg-white p-5">
        @csrf
        @include('admin.kupon._form')
        <div class="flex flex-wrap gap-2">
            <button type="submit" class="min-h-11 rounded-lg bg-brand px-4 py-2 text-sm font-semibold text-white hover:bg-brand-dark">
                Simpan Kupon
            </button>
            <a href="{{ route('admin.kupon.index') }}"
                class="inline-flex min-h-11 items-center rounded-lg border border-line px-4 py-2 text-sm font-semibold">
                Batal
            </a>
        </div>
    </form>
@endsection
