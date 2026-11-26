@extends('layouts.admin')

@section('judul', 'Kelola Pesanan')

@section('konten')
    <div class="mb-4 flex flex-wrap items-center gap-3">
        <form action="{{ route('admin.pesanan.index') }}" method="GET"
            class="grid w-full gap-2 sm:flex sm:items-center">
            <input name="q" value="{{ $q }}" type="search" placeholder="Cari kode pesanan..."
                class="min-h-11 w-full rounded-lg border border-line px-3 py-2 text-sm focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/20 sm:w-56">
            <select name="status" class="min-h-11 w-full rounded-lg border border-line px-3 py-2 text-sm sm:w-auto">
                <option value="">Semua Status</option>
                @foreach ($semuaStatus as $s)
                    <option value="{{ $s->value }}" @selected($status === $s->value)>{{ $s->label() }}</option>
                @endforeach
            </select>
            <button type="submit" class="min-h-11 rounded-lg bg-brand px-4 py-2 text-sm font-semibold text-white hover:bg-brand-dark sm:w-auto">Filter</button>
        </form>
    </div>

    @if ($pesanan->isEmpty())
        <div class="rounded-lg border border-line bg-white p-10 text-center text-muted">Belum ada pesanan.</div>
    @else
        <div class="overflow-x-auto rounded-lg border border-line bg-white">
            <table class="w-full text-left text-sm">
                <thead class="border-b border-line bg-canvas text-xs uppercase text-muted">
                    <tr>
                        <th class="px-4 py-3">Kode</th>
                        <th class="px-4 py-3">Pelanggan</th>
                        <th class="px-4 py-3">Total</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Tanggal</th>
                        <th class="px-4 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($pesanan as $p)
                        <tr class="border-b border-line last:border-0 hover:bg-canvas/50">
                            <td class="px-4 py-3 font-semibold">{{ $p->kode_pesanan }}</td>
                            <td class="px-4 py-3 text-muted">{{ $p->user->name }}</td>
                            <td class="px-4 py-3 font-semibold">{{ \App\Support\Rupiah::format($p->total) }}</td>
                            <td class="px-4 py-3">@include('partials.status-badge', ['status' => $p->status])</td>
                            <td class="px-4 py-3 text-muted">{{ $p->created_at->format('d M Y') }}</td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('admin.pesanan.show', $p) }}" class="text-xs font-semibold text-brand hover:underline">Detail</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-6">{{ $pesanan->links() }}</div>
    @endif
@endsection
