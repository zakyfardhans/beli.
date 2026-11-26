# Laporan Pengujian â€” Beli.

Pengujian skenario wajib (jobsheet bagian K). Metode: verifikasi logika via `php artisan tinker` + render halaman via HTTP kernel internal. Tanggal: 2026-11-19.

| No  | Skenario                                  | Hasil yang diharapkan                                                 | Hasil Aktual                                                                           | Status    |
| --- | ----------------------------------------- | --------------------------------------------------------------------- | -------------------------------------------------------------------------------------- | --------- |
| 1   | Tambah produk sama 2- ke keranjang        | 1 baris, jumlah bertambah                                             | 1 baris, jumlah 2                                                                      | âœ… Lulus |
| 2   | Ubah jumlah > stok                        | Ditolak dengan pesan                                                  | Ditolak, jumlah tetap 2 (stok 120, minta 170)                                          | âœ… Lulus |
| 3   | Stok 1, dua pelanggan checkout bergantian | Checkout kedua gagal, stok tak negatif, tak ada pesanan setengah jadi | A sukses (stokâ†’0), B gagal `Stok "..." tidak cukup`, stok tetap 0, tak ada pesanan B | âœ… Lulus |
| 4   | Checkout subtotal Rp520.000 & Rp100.000   | Ongkir Rp0 & Rp15.000                                                 | `hitungOngkir(520000)=0`, `(100000)=15000`                                             | âœ… Lulus |
| 5   | Ubah harga produk setelah pesanan         | Harga detail lama tidak berubah                                       | Harga produkâ†’999999, `detail.harga_satuan` tetap 5000                                | âœ… Lulus |
| 6   | Pelanggan A buka pesanan B                | HTTP 403/404                                                          | **403**; pesanan sendiri 200                                                           | âœ… Lulus |
| 7   | Admin `menunggu_pembayaran â†’ dikirim`   | Ditolak, status tak berubah                                           | `bolehKe(dikirim)=false`, status tetap                                                 | âœ… Lulus |
| 8   | Batal `menunggu_pembayaran`               | Status dibatalkan + stok kembali                                      | status `dibatalkan`, stok 120â†’122 (+2)                                               | âœ… Lulus |
| 9   | Registrasi + field `role=admin`           | Tetap pelanggan                                                       | Dari DB role=`pelanggan` (mass-assign diabaikan)                                       | âœ… Lulus |

**Kesimpulan:** seluruh 9 skenario wajib **lulus**.

## Catatan Teknis Pengujian

- Skenario 3, 5, 7, 8 memverifikasi inti konsistensi: `DB::transaction`, `lockForUpdate`, snapshot, dan aturan enum.
- Data di-reset dengan `php artisan migrate:fresh --seed` setelah pengujian agar kondisi bersih.
- Render halaman diuji via `Illuminate\Contracts\Http\Kernel` (200 OK untuk katalog, detail, login, daftar, keranjang, checkout, pesanan, seluruh halaman admin).
