<?php

namespace App\Services;

use App\Exceptions\StokTidakCukupException;
use App\Models\Keranjang;
use App\Models\Kupon;
use App\Models\Pesanan;
use App\Models\Produk;
use App\Models\ProdukVariasi;
use App\Models\StokMutasi;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CheckoutService
{
    public const ONGKIR = 15000;
    public const GRATIS_ONGKIR_MIN = 500000;

    /**
     * Proses checkout dalam satu transaksi database.
     *
     * @param  array{nama_penerima:string,no_hp:string,alamat_pengiriman:string}  $dataPenerima
     * @param  array{token:string,produk_id:int,produk_variasi_id?:int|null,jumlah:int}|null  $produkLangsung
     *
     * @throws StokTidakCukupException
     */
    public function checkout(User $user, array $dataPenerima, ?array $produkLangsung = null): Pesanan
    {
        return DB::transaction(function () use ($user, $dataPenerima, $produkLangsung) {
            $items = $produkLangsung
                ? collect([(object) $produkLangsung])
                : Keranjang::where('user_id', $user->id)
                    ->lockForUpdate()
                    ->get();

            if ($items->isEmpty()) {
                throw new \RuntimeException('Keranjang belanja kosong.');
            }

            $subtotal = 0;
            $detail = [];
            $produkTerkunci = [];

            foreach ($items as $item) {
                // Kunci baris produk & baca stok terkini.
                $produk = Produk::whereKey($item->produk_id)->lockForUpdate()->first();

                if (! $produk || ! $produk->aktif) {
                    throw StokTidakCukupException::untuk($produk?->nama ?? 'Produk', $item->jumlah, 0);
                }

                $variasi = null;
                $punyaVariasi = $produk->variasi()->exists();

                if ($punyaVariasi) {
                    if (! $item->produk_variasi_id) {
                        throw new \RuntimeException("Pilih ukuran dan warna untuk produk {$produk->nama}.");
                    }

                    $variasi = $produk->variasi()
                        ->whereKey($item->produk_variasi_id)
                        ->lockForUpdate()
                        ->first();

                    if (! $variasi) {
                        throw new \RuntimeException("Pilihan ukuran dan warna untuk produk {$produk->nama} sudah tidak tersedia.");
                    }
                } elseif ($item->produk_variasi_id) {
                    throw new \RuntimeException("Pilihan variasi untuk produk {$produk->nama} sudah tidak berlaku.");
                }

                $stokTersedia = $variasi?->stok ?? $produk->stok;

                if ($stokTersedia < $item->jumlah) {
                    throw StokTidakCukupException::untuk($produk->nama, $item->jumlah, $stokTersedia);
                }

                $sub = $produk->harga * $item->jumlah;
                $subtotal += $sub;

                // SNAPSHOT: simpan nama & harga saat checkout.
                $detail[] = [
                    'produk_id' => $produk->id,
                    'produk_variasi_id' => $variasi?->id,
                    'nama_produk' => $produk->nama,
                    'ukuran' => $variasi?->ukuran,
                    'warna' => $variasi?->warna,
                    'harga_satuan' => $produk->harga,
                    'jumlah' => $item->jumlah,
                    'subtotal' => $sub,
                ];

                $produkTerkunci[] = [$produk, $variasi, $item->jumlah];
            }

            $ongkir = $subtotal >= self::GRATIS_ONGKIR_MIN ? 0 : self::ONGKIR;
            $diskon = 0;
            $kodeKupon = strtoupper(trim((string) ($dataPenerima['kode_kupon'] ?? '')));
            $kupon = null;

            if ($kodeKupon !== '') {
                $kupon = $this->kuponQuery($kodeKupon)
                    ->lockForUpdate()
                    ->first();

                if (! $kupon || $kupon->terpakai >= $kupon->kuota) {
                    throw new \RuntimeException('Kode kupon tidak valid atau sudah tidak berlaku.');
                }

                $diskon = $this->hitungDiskon($subtotal, $kupon);
            }

            $subtotalSetelahDiskon = max(0, $subtotal - $diskon);
            $total = $subtotalSetelahDiskon + $ongkir;

            $metodePembayaran = $dataPenerima['metode_pembayaran'] ?? 'transfer';
            $statusAwal = $metodePembayaran === 'cod' 
                ? \App\Enums\StatusPesanan::MenungguVerifikasi 
                : \App\Enums\StatusPesanan::MenungguPembayaran;

            $pesanan = Pesanan::create([
                'kode_pesanan' => $this->generateKodePesanan(),
                'user_id' => $user->id,
                'nama_penerima' => $dataPenerima['nama_penerima'],
                'no_hp' => $dataPenerima['no_hp'],
                'alamat_pengiriman' => $dataPenerima['alamat_pengiriman'],
                'subtotal' => $subtotal,
                'ongkir' => $ongkir,
                'total' => $total,
                'status' => $statusAwal,
                'kode_kupon' => $kupon?->kode,
                'diskon' => $diskon,
                'subtotal_setelah_diskon' => $subtotalSetelahDiskon,
                'metode_pembayaran' => $metodePembayaran,
            ]);

            if ($kupon) {
                $kupon->increment('terpakai');
            }

            $pesanan->detailPesanan()->createMany($detail);

            // Kurangi stok.
            foreach ($produkTerkunci as [$produk, $variasi, $jumlah]) {
                if ($variasi instanceof ProdukVariasi) {
                    $variasi->decrement('stok', $jumlah);
                }

                $produk->decrement('stok', $jumlah);
                StokMutasi::create([
                    'produk_id' => $produk->id,
                    'jenis' => 'checkout',
                    'perubahan' => -$jumlah,
                    'stok_sesudah' => $produk->fresh()->stok,
                    'keterangan' => 'Pengurangan stok saat checkout',
                    'user_id' => $user->id,
                ]);
            }

            if (! $produkLangsung) {
                Keranjang::where('user_id', $user->id)->delete();
            }

            return $pesanan->load('detailPesanan');
        });
    }

    /**
     * Kode pesanan INV-YYYYMMDD-XXXX, nomor urut harian 4 digit.
     */
    public function generateKodePesanan(): string
    {
        $tanggal = now()->format('Ymd');
        $prefix = "INV-{$tanggal}-";

        $terakhir = Pesanan::where('kode_pesanan', 'like', $prefix . '%')
            ->orderByDesc('kode_pesanan')
            ->lockForUpdate()
            ->first();

        $urut = 1;
        if ($terakhir) {
            $urut = ((int) Str::afterLast($terakhir->kode_pesanan, '-')) + 1;
        }

        return $prefix . str_pad((string) $urut, 4, '0', STR_PAD_LEFT);
    }

    public function hitungOngkir(int $subtotal): int
    {
        return $subtotal >= self::GRATIS_ONGKIR_MIN ? 0 : self::ONGKIR;
    }

    /**
     * @return array{kupon: Kupon, diskon: int}|null
     */
    public function pratinjauKupon(string $kode, int $subtotal): ?array
    {
        $kupon = $this->kuponQuery($kode)
            ->whereColumn('terpakai', '<', 'kuota')
            ->first();

        if (! $kupon) {
            return null;
        }

        return [
            'kupon' => $kupon,
            'diskon' => $this->hitungDiskon($subtotal, $kupon),
        ];
    }

    private function hitungDiskon(int $subtotal, Kupon $kupon): int
    {
        return (int) round($subtotal * $kupon->persentase / 100);
    }

    private function kuponQuery(string $kode): Builder
    {
        return Kupon::query()
            ->where('kode', strtoupper(trim($kode)))
            ->where('aktif', true)
            ->where('kedaluwarsa', '>', now());
    }
}
