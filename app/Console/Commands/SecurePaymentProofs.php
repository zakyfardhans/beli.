<?php

namespace App\Console\Commands;

use App\Models\Pesanan;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class SecurePaymentProofs extends Command
{
    protected $signature = 'beli:secure-payment-proofs';

    protected $description = 'Amankan bukti pembayaran lama dan bersihkan berkas publik yang tidak terhubung';

    public function handle(): int
    {
        $publik = Storage::disk('public');
        $privat = Storage::disk('local');
        $dipindahkan = 0;
        $sampahDihapus = 0;

        foreach (Pesanan::whereNotNull('bukti_bayar')->orderBy('id')->cursor() as $pesanan) {
            $path = $pesanan->bukti_bayar;

            if ($privat->exists($path)) {
                if ($publik->exists($path) && ! $publik->delete($path)) {
                    $this->error("Bukti pembayaran pesanan #{$pesanan->id} masih dapat diakses dari disk publik.");

                    return self::FAILURE;
                }

                continue;
            }

            if (! $publik->exists($path)) {
                $this->error("Bukti pembayaran pesanan #{$pesanan->id} tidak ditemukan.");

                return self::FAILURE;
            }

            $stream = $publik->readStream($path);
            if (! is_resource($stream)) {
                $this->error("Bukti pembayaran pesanan #{$pesanan->id} tidak dapat dibaca.");

                return self::FAILURE;
            }

            $tersimpan = $privat->put($path, $stream);
            fclose($stream);

            if (! $tersimpan) {
                $this->error("Bukti pembayaran pesanan #{$pesanan->id} gagal disalin ke penyimpanan privat.");

                return self::FAILURE;
            }

            if (! $publik->delete($path)) {
                $privat->delete($path);
                $this->error("Bukti pembayaran pesanan #{$pesanan->id} masih dapat diakses dari disk publik.");

                return self::FAILURE;
            }

            $dipindahkan++;
        }

        foreach ($publik->allFiles('bukti-bayar') as $path) {
            if (Pesanan::where('bukti_bayar', $path)->exists()) {
                $this->error('Bukti pembayaran yang masih terhubung ke pesanan tetap berada di disk publik.');

                return self::FAILURE;
            }

            if (! $publik->delete($path)) {
                $this->error('Berkas bukti pembayaran yang tidak terhubung ke pesanan gagal dihapus dari disk publik.');

                return self::FAILURE;
            }

            $sampahDihapus++;
        }

        $this->info("{$dipindahkan} bukti pembayaran dipindahkan ke penyimpanan privat.");
        $this->info("{$sampahDihapus} berkas bukti pembayaran tanpa pesanan dihapus dari disk publik.");

        return self::SUCCESS;
    }
}
