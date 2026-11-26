@extends('layouts.app')

@section('judul', 'Detail Pesanan')

@section('konten')
    <div class="mb-4 flex items-center justify-between">
        <a href="{{ route('pesanan.index') }}" class="text-sm font-semibold text-brand hover:underline">&larr; Kembali</a>
        @include('partials.status-badge', ['status' => $pesanan->status])
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2 space-y-4">
            <div class="rounded-lg border border-line bg-white p-5">
                <h1 class="text-lg font-bold">{{ $pesanan->kode_pesanan }}</h1>
                <p class="text-sm text-muted">{{ $pesanan->created_at->format('d M Y H:i') }}</p>

                <div class="mt-4 rounded-lg bg-canvas p-3 text-sm">
                    <p class="font-semibold">{{ $pesanan->nama_penerima }} &middot; {{ $pesanan->no_hp }}</p>
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
                            <div class="min-w-0">
                                <p class="font-medium">{{ $d->nama_produk }}</p>
                                @if ($d->ukuran || $d->warna)
                                    <p class="text-muted">Ukuran {{ $d->ukuran ?: '—' }} · {{ $d->warna ?: '—' }}</p>
                                @endif
                                <p class="text-muted">{{ \App\Support\Rupiah::format($d->harga_satuan) }} &times; {{ $d->jumlah }}</p>
                            </div>
                            <span class="shrink-0 font-semibold">{{ \App\Support\Rupiah::format($d->subtotal) }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
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
            </div>

            @if ($pesanan->status === \App\Enums\StatusPesanan::MenungguPembayaran)
                @if ($pesanan->metode_pembayaran === 'qris')
                    <div class="rounded-lg border border-line bg-white p-5">
                        <h2 class="font-bold">Pembayaran QRIS</h2>
                        <p class="mt-1 text-sm text-muted">Silakan scan QR Code menggunakan perangkat lain yang telah login dengan akun yang sama.</p>
                        <a href="{{ route('pesanan.qris', $pesanan) }}" class="mt-4 block w-full rounded-lg bg-brand px-4 py-2.5 text-center text-sm font-semibold text-white transition hover:bg-brand-dark">
                            Buka Halaman QRIS
                        </a>
                    </div>
                @else
                    <div class="rounded-lg border border-line bg-white p-5">
                        <h2 class="font-bold">Pembayaran Transfer</h2>
                        <p class="mt-1 text-sm text-muted">Transfer ke rekening resmi Beli., lalu unggah bukti pembayaran.</p>
                        <div class="mt-3 rounded-lg bg-canvas p-3 text-sm">
                            <p class="font-semibold">{{ config('beli.rekening.bank') }} &middot; {{ config('beli.rekening.nomor') }}</p>
                            <p class="text-muted">a.n. {{ config('beli.rekening.atas_nama') }}</p>
                        </div>
                        <form action="{{ route('pesanan.bayar', $pesanan) }}" method="POST" enctype="multipart/form-data" class="mt-4 space-y-3">
                            @csrf
                            <div>
                                <label for="bukti_bayar" class="mb-1 block text-xs font-semibold">Unggah Bukti (JPG/PNG, maks 2 MB)</label>
                                <input id="bukti_bayar" name="bukti_bayar" type="file" accept="image/png,image/jpeg" required
                                    class="w-full rounded-lg border border-line px-3 py-2 text-sm file:mr-3 file:rounded-md file:border-0 file:bg-brand file:px-3 file:py-1.5 file:text-white">
                                @error('bukti_bayar')
                                    <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <button type="submit" class="w-full rounded-lg bg-brand px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-brand-dark">Unggah Bukti</button>
                        </form>
                    </div>
                @endif

                <form action="{{ route('pesanan.batal', $pesanan) }}" method="POST" onsubmit="return confirm('Batalkan pesanan ini?')">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="w-full rounded-lg border border-rose-200 px-4 py-2.5 text-sm font-semibold text-rose-600 transition hover:bg-rose-50">Batalkan Pesanan</button>
                </form>
            @endif

            @if ($pesanan->status === \App\Enums\StatusPesanan::MenungguVerifikasi && $pesanan->metode_pembayaran === 'cod')
                <div class="rounded-lg border border-amber-200 bg-amber-50 p-5">
                    <h2 class="font-bold text-amber-900">Pembayaran COD (Bayar di Tempat)</h2>
                    <p class="mt-1 text-sm text-amber-800">Pesanan Anda akan diproses dan dikirim. Siapkan pembayaran sebesar <strong>{{ \App\Support\Rupiah::format($pesanan->total) }}</strong> saat pesanan tiba.</p>
                    <div class="mt-3 rounded-lg bg-white p-3 text-sm">
                        <p class="font-semibold text-amber-900">Metode: Cash on Delivery</p>
                        <p class="text-amber-700">Bayar tunai kepada kurir saat pesanan diterima</p>
                    </div>
                </div>
            @endif

            @if ($pesanan->status === \App\Enums\StatusPesanan::Dikirim)
                <form action="{{ route('pesanan.terima', $pesanan) }}" method="POST" onsubmit="return confirm('Konfirmasi pesanan sudah diterima?')">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="w-full rounded-lg bg-brand px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-brand-dark">Pesanan Sudah Diterima</button>
                </form>
            @endif

            @if ($pesanan->bukti_bayar)
                <div class="rounded-lg border border-line bg-white p-5">
                    <h2 class="font-bold">Bukti Bayar</h2>
                    <img src="{{ route('pesanan.bukti', $pesanan) }}" alt="Bukti bayar" class="mt-2 w-full rounded-lg border border-line object-cover">
                </div>
            @endif
        </div>
    </div>
@endsection
