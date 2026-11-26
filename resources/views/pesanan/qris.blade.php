@extends('layouts.app')

@section('judul', 'Pembayaran QRIS')

@section('konten')
    <div class="mx-auto max-w-2xl">
        <div class="mb-4">
            <a href="{{ route('pesanan.show', $pesanan) }}" class="text-sm font-semibold text-brand hover:underline">&larr; Kembali ke detail pesanan</a>
        </div>

        <div class="rounded-lg border border-line bg-white p-6 text-center">
            <h1 class="text-2xl font-bold tracking-tight">Scan QRIS untuk Pembayaran</h1>
            <p class="mt-2 text-sm text-muted">Pindai kode QR di bawah ini menggunakan perangkat lain yang telah login dengan akun yang sama.</p>

            <div class="mt-6 flex justify-center">
                <div class="rounded-lg border border-line bg-white p-4">
                    {!! $qrCodeSvg !!}
                </div>
            </div>

            <div class="mt-6 rounded-lg bg-canvas p-4 text-left text-sm">
                <p class="font-semibold">Kode Pesanan: <span class="text-brand">{{ $pesanan->kode_pesanan }}</span></p>
                <p class="mt-1 text-muted">Total Pembayaran:</p>
                <p class="text-xl font-bold text-brand">{{ \App\Support\Rupiah::format($pesanan->total) }}</p>
            </div>

            <div class="mt-6">
                <p class="text-sm text-muted">Menunggu konfirmasi pembayaran...</p>
                <div class="mt-2 flex items-center justify-center gap-2">
                    <svg class="h-5 w-5 animate-spin text-brand" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span id="status-text" class="text-sm font-medium text-muted">Memeriksa status...</span>
                </div>
            </div>
        </div>
    </div>

    <script>
        const checkStatus = () => {
            fetch('{{ route("pesanan.status", $pesanan) }}')
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'menunggu_verifikasi') {
                        document.getElementById('status-text').textContent = 'Pembayaran dikonfirmasi! Mengalihkan...';
                        document.getElementById('status-text').classList.remove('text-muted');
                        document.getElementById('status-text').classList.add('text-emerald-600');
                        setTimeout(() => {
                            window.location.href = '{{ route("pesanan.show", $pesanan) }}';
                        }, 1500);
                    }
                })
                .catch(error => console.error('Error checking status:', error));
        };

        const intervalId = setInterval(checkStatus, 3000);
        checkStatus();
    </script>
@endsection
