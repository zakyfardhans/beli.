@extends('layouts.admin')

@section('judul', 'Kelola Kupon')

@section('konten')
    <div class="mb-4 flex justify-end">
        <a href="{{ route('admin.kupon.create') }}"
            class="inline-flex min-h-11 items-center rounded-lg bg-brand px-4 py-2 text-sm font-semibold text-white hover:bg-brand-dark">
            Tambah Kupon
        </a>
    </div>

    @if ($kupon->isEmpty())
        <div class="rounded-lg border border-line bg-white p-10 text-center text-muted">
            Belum ada kupon. Tambahkan kupon agar pelanggan dapat menggunakannya di checkout.
        </div>
    @else
        <div class="overflow-x-auto rounded-lg border border-line bg-white">
            <table class="w-full min-w-[640px] text-left text-sm">
                <thead class="border-b border-line bg-canvas text-xs uppercase text-muted">
                    <tr>
                        <th class="px-4 py-3">Kode</th>
                        <th class="px-4 py-3">Diskon</th>
                        <th class="px-4 py-3">Penggunaan</th>
                        <th class="px-4 py-3">Kedaluwarsa</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($kupon as $item)
                        <tr class="border-b border-line last:border-0">
                            <td class="px-4 py-3 font-semibold">{{ $item->kode }}</td>
                            <td class="px-4 py-3">{{ $item->persentase }}%</td>
                            <td class="px-4 py-3">{{ $item->terpakai }} / {{ $item->kuota }}</td>
                            <td class="px-4 py-3">{{ $item->kedaluwarsa->format('d M Y H:i') }}</td>
                            <td class="px-4 py-3">
                                @if ($item->aktif && $item->kedaluwarsa->isFuture() && $item->terpakai < $item->kuota)
                                    <span class="rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-800">Aktif</span>
                                @else
                                    <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-700">Tidak tersedia</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-3">
                                    <a href="{{ route('admin.kupon.edit', $item) }}"
                                        class="font-semibold text-brand hover:underline">Edit</a>
                                    <form action="{{ route('admin.kupon.destroy', $item) }}" method="POST"
                                        onsubmit="return confirm('Hapus kupon ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="font-semibold text-rose-700 hover:underline">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-5">{{ $kupon->links() }}</div>
    @endif
@endsection
