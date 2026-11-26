@extends('layouts.admin')

@section('judul', 'Kelola Kategori')

@section('konten')
    <div class="mb-4 flex justify-end">
        <a href="{{ route('admin.kategori.create') }}" class="rounded-lg bg-brand px-4 py-2 text-sm font-semibold text-white">Tambah Kategori</a>
    </div>
    <div class="overflow-hidden rounded-lg border border-line bg-white">
        <table class="w-full text-left text-sm">
            <thead class="bg-canvas"><tr><th class="px-4 py-3">Nama</th><th class="px-4 py-3">Slug</th><th class="px-4 py-3">Produk</th><th class="px-4 py-3 text-right">Aksi</th></tr></thead>
            <tbody>
            @foreach($kategori as $item)
                <tr class="border-t border-line">
                    <td class="px-4 py-3 font-semibold">{{ $item->nama }}</td>
                    <td class="px-4 py-3 text-muted">{{ $item->slug }}</td>
                    <td class="px-4 py-3">{{ $item->produk_count }}</td>
                    <td class="px-4 py-3 text-right">
                        <a href="{{ route('admin.kategori.edit', $item) }}" class="font-semibold text-brand">Edit</a>
                        <form action="{{ route('admin.kategori.destroy', $item) }}" method="POST" class="inline" onsubmit="return confirm('Hapus kategori ini?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="ml-3 font-semibold text-rose-600">Hapus</button>
                        </form>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $kategori->links() }}</div>
@endsection
