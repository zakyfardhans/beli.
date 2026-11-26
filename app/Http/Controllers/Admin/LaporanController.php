<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pesanan;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LaporanController extends Controller
{
    public function penjualan(): StreamedResponse
    {
        $header = ['Kode Pesanan', 'Tanggal', 'Pelanggan', 'Subtotal', 'Ongkir', 'Diskon', 'Total', 'Status'];

        return response()->streamDownload(function () use ($header) {
            $output = fopen('php://output', 'w');
            fputcsv($output, $header, ',', '"', '\\');

            Pesanan::with('user')->chunkById(500, function ($pesananList) use ($output) {
                foreach ($pesananList as $pesanan) {
                    $row = [
                        $pesanan->kode_pesanan,
                        $pesanan->created_at->format('Y-m-d H:i'),
                        $pesanan->user->name,
                        $pesanan->subtotal,
                        $pesanan->ongkir,
                        $pesanan->diskon,
                        $pesanan->total,
                        $pesanan->status->value,
                    ];

                    foreach ($row as &$value) {
                        if (is_string($value) && preg_match('/^[\x00-\x20]*[=+\-@]/', $value)) {
                            $value = "'" . $value;
                        }
                    }
                    unset($value);

                    fputcsv($output, $row, ',', '"', '\\');
                }
            });

            fclose($output);
        }, 'laporan-pesanan.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
