# Roadmap Pengembangan â€” Beli.

> Status saat ini: seluruh **cakupan wajib F-01 s.d. F-09** selesai dan 9 skenario uji lulus.
> Dokumen ini memuat tahap selanjutnya, diurutkan berdasarkan prioritas & nilai tambah.

---

## Ringkasan Status

| Area | Status |
|---|---|
| Rancangan (ERD, route, aturan bisnis) | âœ… Selesai |
| Basis data + seeder | âœ… Selesai |
| Katalog & detail (F-02) | âœ… Selesai |
| Keranjang (F-03) | âœ… Selesai |
| Checkout + transaksi + snapshot + ongkir (F-04) | âœ… Selesai |
| Riwayat + Policy (F-05) | âœ… Selesai |
| Pembayaran manual (F-06) | âœ… Selesai |
| Pembatalan (F-07) | âœ… Selesai |
| CRUD produk admin (F-08) | âœ… Selesai |
| Kelola pesanan admin (F-09) | âœ… Selesai |

---

## Tahap 1 â€” Stabilisasi & Kualitas (PRIORITAS TINGGI)

Fondasi sebelum menambah fitur. Wajib sebelum demo/penyerahan.

- [x] **Test suite otomatis** (`tests/Feature`) untuk 9 skenario wajib â€” 11 test lulus (25 assertions), pakai SQLite in-memory + `RefreshDatabase`.
- [x] **Factories** (`database/factories`) untuk Kategori, Produk, Pesanan, DetailPesanan + state role di UserFactory.
- [ ] **Validasi upload gambar**: uji file rusak/oversize; pastikan `Storage::delete` tidak error bila file hilang.
- [x] **Rate limiting** pada login (5/menit per email+IP) & registrasi (10/menit per IP) â€” `AppServiceProvider` + middleware `throttle:login|daftar`.
- [x] **Pesan error 404/403 kustom** â€” `resources/views/errors/{404,403}.blade.php`.
- [x] **Ganti branding placeholder** â€” rekening via `config/beli.php` + `.env` (`BELI_BANK`, `BELI_REKENING`, `BELI_ATAS_NAMA`).
- [ ] **Cek N+1** dengan `DB::listen`/Telescope di halaman katalog, keranjang, admin pesanan.
- [ ] **Gambar produk seeder**: sediakan placeholder gambar agar katalog tidak kosong visual.

---

## Tahap 2 â€” Tantangan Tambahan Jobsheet (NILAI TAMBAH)

Fitur opsional dari bagian **L Tantangan Tambahan** jobsheet.

- [ ] **Dashboard admin dengan agregasi** (`sum`, `count`, `groupBy`):
  - jumlah pesanan hari ini
  - omzet bulan ini
  - pesanan menunggu verifikasi
  - 5 produk terlaris
  - produk dengan stok â‰¤ 5
- [ ] **Konfirmasi terima oleh pelanggan** (status `dikirim` â†’ `selesai`) â€” tambah transisi di enum + tombol di `pesanan/show`.
- [ ] **Filter & pengurutan katalog**: rentang harga, urut (terbaru, termurah, termahal).
- [ ] **CRUD kategori** (admin) â€” nama, slug, cegah hapus bila punya produk.
- [ ] **Kupon diskon**: kode, persentase, kuota, tanggal kedaluwarsa; validasi saat checkout; snapshot diskon di pesanan.
- [ ] **Ulasan & rating produk** â€” hanya pelanggan dengan pesanan `selesai`.
- [ ] **Notifikasi email perubahan status** (driver `log` cukup) via Notification/Event.

---

## Tahap 3 â€” Peningkatan E-Commerce Profesional (PENGEMBANGAN)

Melampaui cakupan jobsheet untuk kualitas produksi.

- [ ] **Pencarian produk lanjutan**: full-text, sinonim, pencarian kategori.
- [ ] **Wishlist / favorit** pelanggan.
- [ ] **Alamat tersimpan** (multi-alamat) + pilih saat checkout.
- [ ] **Metode pembayaran**: transfer manual (ada) + opsi payment gateway (Midtrans/Xendit) â€” bila diizinkan.
- [ ] **Manajemen stok lanjutan**: riwayat pergerakan stok (masuk/keluar), peringatan stok rendah.
- [ ] **Laporan penjualan** (export CSV/PDF) untuk admin.
- [ ] **Profil pelanggan**: ubah data, ganti kata sandi, riwayat.
- [ ] **SEO**: meta tag dinamis, sitemap, slug kanonik.

---

## Tahap 4 â€” Skala & Operasional (LANJUTAN)

- [ ] **Antrean (queue)** untuk email/notifikasi agar request cepat.
- [ ] **Cache** katalog & kategori (invalidasi saat produk berubah).
- [ ] **Soft delete** produk (arsip) alih-alih nonaktif saja.
- [ ] **Audit log** perubahan status pesanan (siapa, kapan, dariâ†’ke).
- [ ] **Role tambahan**: staf gudang / customer service (opsional).
- [ ] **API** (REST) untuk aplikasi mobile â€” gunakan Sanctum.
- [ ] **CI/CD**: GitHub Actions (lint, test, build) + deploy.
- [ ] **Monitoring**: log terpusat, error tracking (Sentry), uptime.

---

## Tahap 5 â€” Hardening & Rilis

- [ ] **Uji beban** checkout bersamaan (validasi `lockForUpdate` di bawah konkurensi nyata).
- [ ] **Review keamanan**: mass assignment, upload, XSS (Blade aman default), CSRF, otorisasi tiap route.
- [ ] **Backup database** & strategi restore.
- [ ] **Dokumentasi pengguna** (panduan admin & pelanggan).
- [ ] **Rilis produksi**: `APP_ENV=production`, `APP_DEBUG=false`, `config:cache`, `route:cache`, `view:cache`, `optimize`.

---

## Catatan Teknis untuk Pengembang Berikutnya

- **Aturan transisi status** hanya di `app/Enums/StatusPesanan::bolehKe()` â€” jangan duplikasi di controller.
- **Checkout** selalu lewat `app/Services/CheckoutService.php` (transaksi + `lockForUpdate` + snapshot).
- **`role`** tidak ada di `User::$fillable` â€” set hanya via seeder/assignment eksplisit.
- **Format Rupiah** terpusat di `App\Support\Rupiah::format()`.
- **Policy** pesanan: `app/Policies/PesananPolicy.php` (admin bypass via `before`).
- **Dokumen terkait**: `docs/RANCANGAN.md`, `docs/PENGUJIAN.md`, `docs/URUTANPENGERJAAN/DETAILFITUR.md`.

---

## Implementasi lanjutan — 19 November 2026

### Hardening

- Pembatalan pelanggan dan admin mengunci pesanan serta baris produk dengan `lockForUpdate()` sebelum mengembalikan stok.
- Penghapusan gambar produk dan bukti pembayaran aman bila file fisik sudah tidak ada.
- Skrip `npm run test` memanggil `php artisan test`.
- Cache kategori berlaku lima menit dengan invalidasi saat kategori atau produk berubah.
- Mutasi stok dicatat saat checkout dan pembatalan.
- Perubahan status pesanan dicatat pada tabel `audit_logs`.
- Pipeline CI ditambahkan di `.github/workflows/ci.yml`.

### Fitur tambahan

- Dashboard admin menampilkan pesanan hari ini, omzet bulan ini, pesanan menunggu verifikasi, lima produk terlaris, dan stok rendah.
- Pelanggan dapat mengonfirmasi pesanan berstatus dikirim menjadi selesai.
- Katalog mendukung filter harga dan pengurutan terbaru, termurah, atau termahal.
- CRUD kategori ditambahkan untuk admin.
- Kupon persentase dengan kuota dan masa kedaluwarsa ditambahkan beserta kupon data awal `BELI10`.
- Wishlist/favorit ditambahkan untuk pelanggan.
- Alamat tersimpan dan profil pelanggan ditambahkan.
- Ulasan/rating dibatasi pelanggan yang memiliki pesanan dengan status selesai.
- Perubahan status dapat dikirim sebagai notifikasi berqueue.
- Export laporan penjualan CSV tersedia untuk admin.
- Sitemap XML, meta description, dan canonical ditambahkan.
- Produk memakai soft delete.

### Verifikasi

- `migrate:fresh --seed --force` berhasil.
- `php artisan test` menghasilkan 12 test lulus, 27 assertion.
- `npm run build` berhasil.
- `php artisan view:cache` berhasil.
- Laravel Sanctum sengaja tidak ditambahkan karena API/mobile tidak dipilih pada pelaksanaan lanjutan ini.
