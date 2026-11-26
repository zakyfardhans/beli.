@extends('layouts.admin')

@section('judul', 'Detail Pesanan')

@section('konten')
    <div class="mb-4">
        <a href="{{ route('admin.pesanan.index') }}" class="text-sm font-semibold text-brand hover:underline">&larr;
            Kembali</a>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2 space-y-4">
            <div class="rounded-lg border border-line bg-white p-5">
                <div class="flex items-center justify-between">
                    <h1 class="text-lg font-bold">{{ $pesanan->kode_pesanan }}</h1>
                    @include('partials.status-badge', ['status' => $pesanan->status])
                </div>
                <p class="text-sm text-muted">{{ $pesanan->created_at->format('d M Y H:i') }} · {{ $pesanan->user->name }}
                </p>
                <div class="mt-4 rounded-lg bg-canvas p-3 text-sm">
                    <p class="font-semibold">{{ $pesanan->nama_penerima }} · {{ $pesanan->no_hp }}</p>
                    <p class="text-muted">{{ $pesanan->alamat_pengiriman }}</p>
                </div>
                @if ($pesanan->no_resi)
                    <p class="mt-3 text-sm">No. Resi: <span class="font-semibold">{{ $pesanan->no_resi }}</span></p>
                @endif
            </div>

            <div class="rounded-lg border border-line bg-white p-5">
                <h2 class="mb-3 font-bold">Detail Produk</h2>
                <div class="divide-y divide-line">
                    @foreach ($pesanan->detailPesanan as $d)
                        <div class="flex items-center justify-between gap-3 py-2.5 text-sm">
                            <div>
                                <p class="font-medium">{{ $d->nama_produk }}</p>
                                @if ($d->ukuran || $d->warna)
                                    <p class="text-muted">Ukuran {{ $d->ukuran ?: '—' }} · {{ $d->warna ?: '—' }}</p>
                                @endif
                                <p class="text-muted">{{ \App\Support\Rupiah::format($d->harga_satuan) }} -
                                    {{ $d->jumlah }}</p>
                            </div>
                            <span class="font-semibold">{{ \App\Support\Rupiah::format($d->subtotal) }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            @if ($pesanan->bukti_bayar)
                <div class="rounded-lg border border-line bg-white p-5">
                    <h2 class="mb-2 font-bold">Bukti Bayar</h2>
                    <img src="{{ route('pesanan.bukti', $pesanan) }}" alt="Bukti bayar"
                        class="max-h-80 rounded-lg border border-line object-contain">
                </div>
            @endif
        </div>

        <div class="lg:col-span-1 space-y-4">
            <div class="rounded-lg border border-line bg-white p-5">
                <h2 class="font-bold">Ringkasan</h2>
                <dl class="mt-3 space-y-2 text-sm">
                    <div class="flex justify-between">
                        <dt class="text-muted">Subtotal</dt>
                        <dd class="font-semibold">{{ \App\Support\Rupiah::format($pesanan->subtotal) }}</dd>
                    </div>
                    @if ($pesanan->diskon > 0)
                        <div class="flex justify-between">
                            <dt class="text-muted">Diskon kupon{{ $pesanan->kode_kupon ? ' (' . $pesanan->kode_kupon . ')' : '' }}</dt>
                            <dd class="font-semibold text-emerald-800">−{{ \App\Support\Rupiah::format($pesanan->diskon) }}</dd>
                        </div>
                    @endif
                    <div class="flex justify-between">
                        <dt class="text-muted">Ongkir</dt>
                        <dd class="font-semibold">
                            {{ $pesanan->ongkir === 0 ? 'Gratis' : \App\Support\Rupiah::format($pesanan->ongkir) }}</dd>
                    </div>
                    <div class="flex justify-between border-t border-line pt-2 text-base">
                        <dt class="font-bold">Total</dt>
                        <dd class="font-extrabold text-brand">{{ \App\Support\Rupiah::format($pesanan->total) }}</dd>
                    </div>
                </dl>
                <div class="mt-3 border-t border-line pt-3">
                    <p class="text-xs text-muted">Metode Pembayaran</p>
                    <p class="mt-1 text-sm font-semibold">
                        @if ($pesanan->metode_pembayaran === 'cod')
                            <span class="inline-flex items-center gap-1 rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-semibold text-amber-700">
                                COD (Bayar di Tempat)
                            </span>
                        @elseif ($pesanan->metode_pembayaran === 'qris')
                            <span class="inline-flex items-center gap-1 rounded-full bg-violet-100 px-2.5 py-0.5 text-xs font-semibold text-violet-700">
                                QRIS
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 rounded-full bg-sky-100 px-2.5 py-0.5 text-xs font-semibold text-sky-700">
                                Transfer Bank
                            </span>
                        @endif
                    </p>
                </div>
            </div>

            @if (count($transisi) > 0)
                <div class="rounded-lg border border-line bg-white p-5">
                    <h2 class="font-bold">Ubah Status</h2>
                    <p class="mt-1 text-xs text-muted">Hanya transisi valid yang ditampilkan.</p>

                    <div class="mt-3 space-y-2">
                        @foreach ($transisi as $tujuan)
                            <form action="{{ route('admin.pesanan.status', $pesanan) }}" method="POST"
                                class="space-y-2 rounded-lg border border-line p-3">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="{{ $tujuan->value }}">

                                @if ($tujuan === \App\Enums\StatusPesanan::Dikirim)
                                    <input name="no_resi" type="text" placeholder="No. resi (wajib)" required
                                        class="w-full rounded-lg border border-line px-3 py-2 text-sm focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/20">
                                    @error('no_resi')
                                        <p class="text-xs text-rose-600">{{ $message }}</p>
                                    @enderror
                                @endif

                                <button type="submit"
                                    class="w-full rounded-lg bg-brand px-3 py-2 text-sm font-semibold text-white transition hover:bg-brand-dark">
                                    Ubah ke {{ $tujuan->label() }}
                                </button>
                            </form>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection
