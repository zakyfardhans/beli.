<?php

namespace Database\Seeders;

use App\Enums\StatusPesanan;
use App\Models\Pesanan;
use App\Models\Produk;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class PesananSeeder extends Seeder
{
    public function run(): void
    {
        $pelanggan = User::where('role', 'pelanggan')->orderBy('id')->get();
        $produk = Produk::orderBy('id')->get();

        if ($pelanggan->isEmpty() || $produk->isEmpty()) {
            return;
        }

        $tanggal = Carbon::now()->format('Ymd');

        // [pelanggan_index, status, [produk_index => jumlah]]
        $daftar = [
            [0, StatusPesanan::MenungguPembayaran, [0 => 2, 3 => 1]],
            [1, StatusPesanan::MenungguVerifikasi, [4 => 1]],
            [2, StatusPesanan::Diproses, [9 => 1, 11 => 2]],
            [0, StatusPesanan::Dikirim, [10 => 1]],
            [1, StatusPesanan::Selesai, [6 => 1, 7 => 1]],
        ];

        foreach ($daftar as $urut => [$pelangganIdx, $status, $items]) {
            $user = $pelanggan[$pelangganIdx];
            $subtotal = 0;
            $detail = [];

            foreach ($items as $produkIdx => $jumlah) {
                $p = $produk[$produkIdx];
                $sub = $p->harga * $jumlah;
                $subtotal += $sub;
                $detail[] = [
                    'produk_id' => $p->id,
                    'nama_produk' => $p->nama, // snapshot
                    'harga_satuan' => $p->harga, // snapshot
                    'jumlah' => $jumlah,
                    'subtotal' => $sub,
                ];
            }

            $ongkir = $subtotal >= 500000 ? 0 : 15000;

            $pesanan = Pesanan::create([
                'kode_pesanan' => sprintf('INV-%s-%04d', $tanggal, $urut + 1),
                'user_id' => $user->id,
                'nama_penerima' => $user->name,
                'no_hp' => $user->no_hp,
                'alamat_pengiriman' => $user->alamat ?? 'Alamat belum diisi',
                'subtotal' => $subtotal,
                'ongkir' => $ongkir,
                'total' => $subtotal + $ongkir,
                'status' => $status,
                'no_resi' => $status === StatusPesanan::Dikirim ? 'RESI-' . ($urut + 1) . '0001' : null,
            ]);

            $pesanan->detailPesanan()->createMany($detail);
        }
    }
}
