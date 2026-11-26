@extends('layouts.admin')

@section('judul', 'Kelola Produk')

@section('konten')
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <form action="{{ route('admin.produk.index') }}" method="GET" class="flex items-center gap-2">
            <input name="q" value="{{ $q }}" type="search" placeholder="Cari nama produk..."
                class="w-56 rounded-lg border border-line px-3 py-2 text-sm focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/20">
            <button type="submit" class="rounded-lg bg-brand px-4 py-2 text-sm font-semibold text-white hover:bg-brand-dark">Cari</button>
        </form>
        <a href="{{ route('admin.produk.create') }}" class="rounded-lg bg-brand px-4 py-2 text-sm font-semibold text-white hover:bg-brand-dark">+ Tambah Produk</a>
    </div>

    @if ($produk->isEmpty())
        <div class="rounded-lg border border-line bg-white p-10 text-center text-muted">Belum ada produk.</div>
    @else
        <div class="overflow-x-auto rounded-lg border border-line bg-white">
            <table class="w-full text-left text-sm">
                <thead class="border-b border-line bg-canvas text-xs uppercase text-muted">
                    <tr>
                        <th class="px-4 py-3">Produk</th>
                        <th class="px-4 py-3">Kategori</th>
                        <th class="px-4 py-3">Harga</th>
                        <th class="px-4 py-3">Stok</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($produk as $item)
                        <tr class="border-b border-line last:border-0 hover:bg-canvas/50">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-10 w-10 shrink-0 items-center justify-center overflow-hidden rounded-lg bg-canvas">
                                        @if ($item->gambar)
                                            <img src="{{ asset('storage/' . $item->gambar) }}" alt="" class="h-full w-full object-cover">
                                        @else
                                            <span class="text-line">&#128230;</span>
                                        @endif
                                    </div>
                                    <span class="font-medium">{{ $item->nama }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-muted">{{ $item->kategori->nama }}</td>
                            <td class="px-4 py-3 font-semibold">{{ \App\Support\Rupiah::format($item->harga) }}</td>
                            <td class="px-4 py-3">{{ $item->stok }}</td>
                            <td class="px-4 py-3">
                                @if ($item->aktif)
                                    <span class="rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-semibold text-emerald-700">Aktif</span>
                                @else
                                    <span class="rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-semibold text-slate-500">Nonaktif</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.produk.edit', $item) }}" class="text-xs font-semibold text-brand hover:underline">Edit</a>
                                    <form action="{{ route('admin.produk.destroy', $item) }}" method="POST" onsubmit="return confirm('Hapus produk ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-xs font-semibold text-rose-600 hover:underline">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-6">{{ $produk->links() }}</div>
    @endif
@endsection
