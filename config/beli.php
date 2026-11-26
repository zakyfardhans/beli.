<?php

return [
    /*
     | Informasi rekening pembayaran (ditampilkan di halaman pesanan).
     | Sesuaikan via .env bila perlu.
     */
    'rekening' => [
        'bank' => env('BELI_BANK', 'BCA'),
        'nomor' => env('BELI_REKENING', '1234567890'),
        'atas_nama' => env('BELI_ATAS_NAMA', 'PT Beli Indonesia'),
    ],
];
