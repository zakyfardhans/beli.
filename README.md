# Beli. â€” Platform E-Commerce

Platform e-commerce profesional (Laravel 12): katalog, keranjang, checkout dengan transaksi & snapshot harga, pembayaran manual, pembatalan, dan kelola pesanan oleh admin.

> Proyek jobsheet **Tugas 3 â€” Toko Online (E-Commerce)**.

## Fitur

- **Pengunjung:** katalog (cari, filter kategori, pagination 12), detail produk.
- **Variasi produk:** pilih ukuran dan warna; stok dikelola per kombinasi, dengan tabel panduan ukuran yang diisi admin per produk.
- **Pelanggan:** registrasi/login, keranjang (merge item, validasi stok), checkout (transaksi, `lockForUpdate`, snapshot harga, ongkir dan kupon), riwayat & detail pesanan, bukti bayar privat, pembatalan, favorit, ulasan pesanan selesai dengan nama, tanggal, dan hingga 3 foto, serta CRUD alamat tersimpan.
- **Admin:** CRUD produk dan kategori, kelola kupon, kelola pesanan (filter status, bukti bayar privat, transisi status valid via enum, resi), dashboard penjualan, dan ekspor laporan CSV.

## Kebutuhan

- PHP â‰¥ 8.2, Composer
- MySQL/MariaDB (atau SQLite)
- Node.js + NPM (Tailwind v4 via Vite)

## Instalasi dari Nol

```bash
composer install
cp .env.example .env       # Windows: copy .env.example .env
php artisan key:generate
```

Atur koneksi database di `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=db_toko_online_zaky
DB_USERNAME=root
DB_PASSWORD=
```

Buat database kosong (mis. via phpMyAdmin) dengan nama `db_toko_online_zaky`, lalu:

```bash
php artisan migrate --seed
php artisan storage:link
php artisan beli:secure-payment-proofs  # amankan bukti lama dan bersihkan berkas publik yatim
npm install
npm run build          # atau: npm run dev
php artisan serve
```

Buka `http://127.0.0.1:8000`.

## Akun Seeder

Semua kata sandi: **`password`**

| Peran | Email |
|---|---|
| Admin | `admin@beli.test` |
| Pelanggan | `zaky@beli.test` |
| Pelanggan | `rina@beli.test` |
| Pelanggan | `budi@beli.test` |

Data awal: 4 kategori, 14 produk (beberapa stok 0/â‰¤5), 5 pesanan dengan status bervariasi.

## Struktur Penting

- `app/Enums/StatusPesanan.php` â€” aturan transisi status (`bolehKe`).
- `app/Services/CheckoutService.php` â€” transaksi checkout, ongkir, snapshot, kode pesanan.
- `app/Console/Commands/SecurePaymentProofs.php` â€” migrasi satu kali bukti bayar lama ke penyimpanan privat.
- `app/Policies/PesananPolicy.php` â€” kepemilikan pesanan.
- `app/Http/Middleware/CekRole.php` â€” otorisasi role.
- `docs/RANCANGAN.md` â€” ERD, route, aturan bisnis.
- `docs/URUTANPENGERJAAN/DETAILFITUR.md` â€” catatan progres implementasi.
- `docs/PENGUJIAN.md` â€” hasil 9 skenario pengujian.

## Pengujian

```bash
php artisan test
```

Lihat `docs/PENGUJIAN.md` untuk daftar skenario manual.
