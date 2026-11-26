@extends('layouts.app')

@section('judul', 'Pesanan Saya')

@section('konten')
    <h1 class="mb-6 text-2xl font-bold tracking-tight md:text-3xl">Pesanan Saya</h1>

    @if ($pesanan->isEmpty())
        <div class="rounded-lg border border-line bg-white p-10 text-center">
            <p class="text-muted">Belum ada pesanan.</p>
            <a href="{{ route('katalog') }}"
                class="mt-4 inline-block rounded-lg bg-brand px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-brand-dark">Mulai
                Belanja</a>
        </div>
    @else
        <div class="space-y-4">
            @foreach ($pesanan as $p)
                @php
                    $produkPertama = $p->detailPesanan->first();
                    $gambarProduk = $produkPertama?->produk?->gambar
                        ? asset('storage/' . $produkPertama->produk->gambar)
                        : asset('placeholder-produk.svg');
                    $jumlahProdukLainnya = $p->detailPesanan->count() - 1;

                    // Definisikan tahapan pesanan
                    $tahapan = [
                        'menunggu_pembayaran' => 1,
                        'menunggu_verifikasi' => 1,
                        'diproses' => 2,
                        'dikirim' => 3,
                        'selesai' => 4,
                        'dibatalkan' => 0,
                    ];
                    $tahapAktif = $tahapan[$p->status->value] ?? 0;
                @endphp
                <div class="rounded-lg border border-line bg-white shadow-sm">
                    <!-- Header: Kode, Tanggal, Status -->
                    <div class="flex flex-wrap items-center justify-between gap-2 border-b border-line bg-gray-50 px-5 py-3">
                        <div>
                            <span class="font-bold text-gray-900">#{{ $p->kode_pesanan }}</span>
                            <span class="ml-2 text-sm text-muted">{{ $p->created_at->format('d M Y, H:i') }} WIB</span>
                        </div>
                        @include('partials.status-badge', ['status' => $p->status])
                    </div>

                    <!-- Progress Stepper -->
                    {{-- @if ($p->status !== \App\Enums\StatusPesanan::Dibatalkan)
                        <div class="border-b border-line px-5 py-4">
                            <div class="flex items-center justify-between">
                                @php
                                    $tahapLabels = ['Pending', 'Processing', 'Shipped', 'Done'];
                                @endphp
                                @foreach ($tahapLabels as $index => $label)
                                    @php
                                        $tahapNum = $index + 1;
                                        $isActive = $tahapAktif >= $tahapNum;
                                        $isCurrent = $tahapAktif === $tahapNum;
                                    @endphp
                                    <div class="flex flex-1 items-center">
                                        <div class="flex flex-col items-center">
                                            <div
                                                class="flex h-8 w-8 items-center justify-center rounded-full border-2 {{ $isActive ? 'border-brand bg-brand text-white' : 'border-gray-300 bg-white text-gray-400' }}">
                                                @if ($isActive)
                                                    <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd"
                                                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                                            clip-rule="evenodd" />
                                                    </svg>
                                                @else
                                                    <span class="text-xs font-semibold">{{ $tahapNum }}</span>
                                                @endif
                                            </div>
                                            <span
                                                class="mt-1 text-xs font-semibold {{ $isActive ? 'text-brand' : 'text-gray-400' }}">{{ strtoupper($label) }}</span>
                                        </div>
                                        @if ($index < 3)
                                            <div
                                                class="mx-2 h-0.5 flex-1 {{ $tahapAktif > $tahapNum ? 'bg-brand' : 'bg-gray-300' }}">
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif --}}

                    <!-- Body: Gambar Produk, Nama, Alamat, Harga -->
                    <div class="p-5">
                        <div class="flex flex-col gap-4 sm:flex-row sm:items-start">
                            <div class="relative h-28 w-28 shrink-0 overflow-hidden rounded-lg bg-canvas">
                                <img src="{{ $gambarProduk }}" alt="{{ $produkPertama?->nama_produk }}"
                                    class="h-full w-full object-cover">
                                @if ($jumlahProdukLainnya > 0)
                                    <div
                                        class="absolute inset-0 flex items-center justify-center bg-black/60 text-xs font-bold text-white">
                                        +{{ $jumlahProdukLainnya }}
                                    </div>
                                @endif
                            </div>
                            <div class="flex-1">
                                <h3 class="font-semibold text-gray-900">
                                    {{ $produkPertama?->nama_produk ?? 'Produk tidak tersedia' }}</h3>
                                @if ($produkPertama && ($produkPertama->ukuran || $produkPertama->warna))
                                    <p class="text-sm text-muted">Variasi:
                                        {{ $produkPertama->ukuran ? 'Ukuran ' . $produkPertama->ukuran : '' }}
                                        {{ $produkPertama->warna ? ' ' . $produkPertama->warna : '' }}</p>
                                @endif
                                <div class="mt-2 rounded-lg bg-canvas p-2 text-sm">
                                    <p class="font-medium text-gray-700">{{ $p->nama_penerima }} · {{ $p->no_hp }}
                                    </p>
                                    <p class="text-muted">{{ Str::limit($p->alamat_pengiriman, 80) }}</p>
                                </div>
                                <p class="mt-3 text-xl font-bold text-brand">{{ \App\Support\Rupiah::format($p->total) }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Footer: Aksi -->
                    <div class="flex flex-wrap items-center justify-end gap-2 border-t border-line bg-gray-50 px-5 py-3">
                        @if (in_array($p->status, [\App\Enums\StatusPesanan::MenungguPembayaran, \App\Enums\StatusPesanan::MenungguVerifikasi]))
                            <form action="{{ route('pesanan.batal', $p) }}" method="POST"
                                onsubmit="return confirm('Batalkan pesanan ini?')">
                                @csrf
                                @method('PATCH')
                                <button type="submit"
                                    class="rounded-lg border border-rose-200 bg-white px-4 py-2 text-sm font-semibold text-rose-600 transition hover:bg-rose-50">
                                    Batalkan
                                </button>
                            </form>
                        @endif
                        <a href="{{ route('pesanan.show', $p) }}"
                            class="rounded-lg border border-line bg-white px-4 py-2 text-sm font-semibold text-gray-700 transition hover:bg-canvas">
                            Lihat Detail
                        </a>
                        @if ($p->status === \App\Enums\StatusPesanan::Dikirim)
                            <form action="{{ route('pesanan.terima', $p) }}" method="POST"
                                onsubmit="return confirm('Konfirmasi pesanan sudah diterima?')">
                                @csrf
                                @method('PATCH')
                                <button type="submit"
                                    class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-700">
                                    PESANAN DITERIMA
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-6">{{ $pesanan->links() }}</div>
    @endif
@endsection
