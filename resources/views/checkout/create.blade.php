@extends('layouts.app')

@section('judul', 'Checkout')

@section('konten')
    <h1 class="mb-6 text-2xl font-bold tracking-tight md:text-3xl">Checkout</h1>
    @if ($checkoutLangsung)
        <p class="-mt-4 mb-6 text-sm text-muted">Pembelian langsung. Keranjang Anda tidak akan berubah.</p>
    @endif

    <form id="form-kupon" action="{{ route('checkout.create') }}" method="GET">
        @if ($checkoutLangsung)
            <input type="hidden" name="mode" value="langsung">
            <input type="hidden" name="token" value="{{ $token }}">
        @endif
    </form>
    <form id="checkout-form" action="{{ route('checkout.store') }}" method="POST" class="grid gap-6 lg:grid-cols-3">
        @csrf
        <input type="hidden" name="mode" value="{{ $checkoutLangsung ? 'langsung' : 'keranjang' }}">
        @if ($checkoutLangsung)
            <input type="hidden" name="token" value="{{ $token }}">
        @endif

        <div class="lg:col-span-2">
            <div class="rounded-lg border border-line bg-white p-5">
                <h2 class="mb-4 text-lg font-bold">Alamat Pengiriman</h2>
                @if ($alamat->isNotEmpty())
                    <div class="mb-4">
                        <label for="alamat_id" class="mb-1 block text-sm font-semibold">Gunakan alamat tersimpan</label>
                        <select id="alamat_id" name="alamat_id"
                            class="w-full rounded-lg border border-line px-3.5 py-2.5 text-sm focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/20">
                            <option value="">Isi alamat baru</option>
                            @foreach ($alamat as $item)
                                <option value="{{ $item->id }}" data-nama="{{ $item->nama_penerima }}"
                                    data-no-hp="{{ $item->no_hp }}" data-alamat="{{ $item->alamat_pengiriman }}"
                                    @selected((string) old('alamat_id', $alamatUtama?->id) === (string) $item->id)>
                                    {{ $item->label }}{{ $item->utama ? ' (Utama)' : '' }} - {{ $item->nama_penerima }}
                                </option>
                            @endforeach
                        </select>
                        @error('alamat_id')
                            <p class="mt-1 text-sm text-rose-700">{{ $message }}</p>
                        @enderror
                    </div>
                @endif
                <div class="space-y-4">
                    <div>
                        <label for="nama_penerima" class="mb-1 block text-sm font-semibold">Nama Penerima</label>
                        <input id="nama_penerima" name="nama_penerima" type="text"
                            value="{{ old('nama_penerima', $alamatUtama?->nama_penerima ?? auth()->user()->name) }}"
                            required
                            class="w-full rounded-lg border border-line px-3.5 py-2.5 text-sm focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/20">
                        @error('nama_penerima')
                            <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="no_hp" class="mb-1 block text-sm font-semibold">Nomor HP</label>
                        <input id="no_hp" name="no_hp" type="text"
                            value="{{ old('no_hp', $alamatUtama?->no_hp ?? auth()->user()->no_hp) }}" required
                            class="w-full rounded-lg border border-line px-3.5 py-2.5 text-sm focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/20">
                        @error('no_hp')
                            <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="alamat_pengiriman" class="mb-1 block text-sm font-semibold">Alamat Lengkap</label>
                        <textarea id="alamat_pengiriman" name="alamat_pengiriman" rows="3" required
                            class="w-full rounded-lg border border-line px-3.5 py-2.5 text-sm focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/20">{{ old('alamat_pengiriman', $alamatUtama?->alamat_pengiriman ?? auth()->user()->alamat) }}</textarea>
                        @error('alamat_pengiriman')
                            <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="mt-4 rounded-lg border border-line bg-white p-5">
                <h2 class="mb-3 text-lg font-bold">Ringkasan Produk</h2>
                <div class="divide-y divide-line">
                    @foreach ($items as $item)
                        <div class="flex items-center justify-between gap-3 py-2.5 text-sm">
                            <div class="min-w-0">
                                <p class="font-medium">{{ $item->produk->nama }}</p>
                                @if ($item->variasi)
                                    <p class="text-muted">Ukuran {{ $item->variasi->ukuran }} · {{ $item->variasi->warna }}</p>
                                @endif
                                <p class="text-muted">{{ \App\Support\Rupiah::format($item->produk->harga) }} -
                                    {{ $item->jumlah }}</p>
                            </div>
                            <span
                                class="shrink-0 font-semibold">{{ \App\Support\Rupiah::format($item->produk->harga * $item->jumlah) }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="lg:col-span-1">
            <div class="rounded-lg border border-line bg-white p-5">
                <h2 class="text-lg font-bold">Total Pembayaran</h2>
                <div class="mt-3">
                    <label for="kode_kupon" class="mb-1 block text-xs font-semibold text-muted">Kode Kupon
                        (opsional)</label>
                    <div class="flex gap-2">
                        <input id="kode_kupon" name="kode_kupon" form="form-kupon" type="text"
                            value="{{ old('kode_kupon', $kodeKupon) }}" autocomplete="off"
                            class="min-w-0 flex-1 rounded-lg border border-line px-3 py-2 text-sm uppercase focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/20">
                        <button type="submit" form="form-kupon"
                            class="min-h-10 rounded-lg border border-brand px-3 text-sm font-semibold text-brand hover:bg-brand-light">Terapkan</button>
                    </div>
                    @if ($galatKupon)
                        <p role="alert" class="mt-2 text-sm text-rose-700">{{ $galatKupon }}</p>
                    @elseif ($pratinjauKupon)
                        <p class="mt-2 text-sm font-medium text-emerald-800">
                            Kupon {{ $pratinjauKupon['kupon']->kode }} memberi diskon
                            {{ $pratinjauKupon['kupon']->persentase }}%.
                        </p>
                    @endif
                    @error('kode_kupon')
                        <p class="mt-1 text-sm text-rose-700">{{ $message }}</p>
                    @enderror
                </div>
                <dl class="mt-3 space-y-2 text-sm">
                    <div class="flex justify-between">
                        <dt class="text-muted">Subtotal</dt>
                        <dd class="font-semibold">{{ \App\Support\Rupiah::format($subtotal) }}</dd>
                    </div>
                    @if ($diskon > 0)
                        <div class="flex justify-between">
                            <dt class="text-muted">Diskon kupon</dt>
                            <dd class="font-semibold text-emerald-800">−{{ \App\Support\Rupiah::format($diskon) }}</dd>
                        </div>
                    @endif
                    <div class="flex justify-between">
                        <dt class="text-muted">Ongkir</dt>
                        <dd class="font-semibold">{{ $ongkir === 0 ? 'Gratis' : \App\Support\Rupiah::format($ongkir) }}
                        </dd>
                    </div>
                    <div class="flex justify-between border-t border-line pt-2 text-base">
                        <dt class="font-bold">Total</dt>
                        <dd class="font-extrabold text-brand">{{ \App\Support\Rupiah::format($total) }}</dd>
                    </div>
                </dl>
                <div class="mt-4">
                    <label class="mb-2 block text-xs font-semibold text-muted">Metode Pembayaran</label>
                    <div class="space-y-2">
                        <label class="flex cursor-pointer items-center gap-3 rounded-lg border border-line p-3 transition hover:bg-canvas has-[:checked]:border-brand has-[:checked]:bg-brand/5">
                            <input type="radio" name="metode_pembayaran" value="transfer" checked
                                class="h-4 w-4 text-brand focus:ring-brand">
                            <div>
                                <span class="block text-sm font-semibold">Transfer Bank</span>
                                <span class="block text-xs text-muted">Unggah bukti transfer setelah checkout</span>
                            </div>
                        </label>
                        <label class="flex cursor-pointer items-center gap-3 rounded-lg border border-line p-3 transition hover:bg-canvas has-[:checked]:border-brand has-[:checked]:bg-brand/5">
                            <input type="radio" name="metode_pembayaran" value="qris"
                                class="h-4 w-4 text-brand focus:ring-brand">
                            <div>
                                <span class="block text-sm font-semibold">QRIS</span>
                                <span class="block text-xs text-muted">Scan QR Code dengan perangkat lain</span>
                            </div>
                        </label>
                        <label class="flex cursor-pointer items-center gap-3 rounded-lg border border-line p-3 transition hover:bg-canvas has-[:checked]:border-brand has-[:checked]:bg-brand/5">
                            <input type="radio" name="metode_pembayaran" value="cod"
                                class="h-4 w-4 text-brand focus:ring-brand">
                            <div>
                                <span class="block text-sm font-semibold">COD (Bayar di Tempat)</span>
                                <span class="block text-xs text-muted">Bayar tunai saat pesanan tiba</span>
                            </div>
                        </label>
                    </div>
                    @error('metode_pembayaran')
                        <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <input type="hidden" name="kode_kupon" value="{{ $kodeKupon }}">
                <button type="submit" form="checkout-form"
                    class="mt-5 w-full rounded-lg bg-brand px-5 py-3 text-sm font-semibold text-white transition hover:bg-brand-dark active:scale-[0.98]">Konfirmasi
                    Pesanan</button>
                @if ($checkoutLangsung)
                    <a href="{{ route('produk.show', $items->first()->produk) }}"
                        class="mt-2 block text-center text-sm text-muted hover:text-brand">Kembali ke produk</a>
                @else
                    <a href="{{ route('keranjang.index') }}"
                        class="mt-2 block text-center text-sm text-muted hover:text-brand">Kembali ke keranjang</a>
                @endif
            </div>
        </div>
    </form>

    @if ($alamat->isNotEmpty())
        <script>
            document.getElementById('alamat_id').addEventListener('change', function() {
                const selected = this.options[this.selectedIndex];
                document.getElementById('nama_penerima').value = selected.dataset.nama || '';
                document.getElementById('no_hp').value = selected.dataset.noHp || '';
                document.getElementById('alamat_pengiriman').value = selected.dataset.alamat || '';
            });
        </script>
    @endif
@endsection
