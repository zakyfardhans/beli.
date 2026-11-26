@extends('layouts.app')

@section('judul', 'Konfirmasi Pembayaran QRIS')

@section('konten')
    <div class="mx-auto max-w-2xl">
        <div class="rounded-lg border border-line bg-white p-6 text-center">
            <div class="mb-4 inline-flex h-16 w-16 items-center justify-center rounded-full bg-brand/10">
                <svg class="h-8 w-8 text-brand" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>

            <h1 class="text-2xl font-bold tracking-tight">Konfirmasi Pembayaran</h1>
            <p class="mt-2 text-sm text-muted">Anda akan mengonfirmasi pembayaran untuk pesanan berikut:</p>

            <div class="mt-6 rounded-lg bg-canvas p-4 text-left text-sm">
                <div class="space-y-2">
                    <div class="flex justify-between">
                        <span class="text-muted">Kode Pesanan</span>
                        <span class="font-semibold">{{ $pesanan->kode_pesanan }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-muted">Penerima</span>
                        <span class="font-semibold">{{ $pesanan->nama_penerima }}</span>
                    </div>
                    <div class="flex justify-between border-t border-line pt-2">
                        <span class="font-bold">Total Pembayaran</span>
                        <span class="text-xl font-bold text-brand">{{ \App\Support\Rupiah::format($pesanan->total) }}</span>
                    </div>
                </div>
            </div>

            <form action="{{ route('pesanan.qris.konfirmasi', ['pesanan' => $pesanan->kode_pesanan, 'token' => $pesanan->qr_token]) }}" method="POST" class="mt-6">
                @csrf
                <button type="submit" class="w-full rounded-lg bg-brand px-6 py-3 text-base font-semibold text-white transition hover:bg-brand-dark active:scale-[0.98]" onclick="return confirm('Konfirmasi pembayaran untuk pesanan ini?')">
                    Konfirmasi Pembayaran
                </button>
            </form>

            <p class="mt-4 text-xs text-muted">Pastikan Anda telah melakukan pembayaran sebelum menekan tombol konfirmasi.</p>
        </div>
    </div>
@endsection
