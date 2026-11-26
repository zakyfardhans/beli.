# Product

<!-- impeccable:product-schema 1 -->

## Platform

web

## Users

Pengunjung dapat mencari dan melihat produk. Pelanggan Indonesia adalah pengguna utama: mereka memilih produk, mengelola keranjang, melakukan checkout, mengunggah bukti pembayaran, melacak pesanan, dan membatalkan pesanan yang belum dibayar. Admin mengelola produk serta memproses dan mengirim pesanan.

## Product Purpose

Beli. adalah platform e-commerce Laravel yang memungkinkan pengguna menemukan produk, menyelesaikan pembelian, dan melacak status pesanan. Keberhasilan produk terlihat dari proses katalog hingga selesai yang dapat berjalan konsisten tanpa stok negatif, perubahan harga produk setelah checkout, atau transisi status yang tidak sah.

## Positioning

Beli. menonjol sebagai toko online pembelajaran yang mengutamakan integritas transaksi: snapshot nama dan harga produk, pengurangan serta pengembalian stok, ongkir yang konsisten, dan alur status pesanan yang tervalidasi.

## Operating Context

Sistem digunakan melalui situs web berbahasa Indonesia. Pelanggan berinteraksi dengan katalog, keranjang, checkout, riwayat pesanan, dan pembayaran manual. Admin bekerja melalui panel untuk mengelola katalog dan pesanan. Satu sesi dapat mencakup pencarian produk, checkout, unggah bukti pembayaran, verifikasi, pemrosesan, pengiriman, dan penyelesaian pesanan.

## Capabilities and Constraints

- Laravel 12 dengan PHP 8.2 atau lebih baru, Tailwind v4, dan Vite.
- MySQL/MariaDB atau SQLite.
- Peran pengguna: pengunjung, pelanggan, dan admin.
- Fitur mencakup katalog, pencarian dan filter, keranjang, checkout, riwayat pesanan, unggah bukti bayar, pembatalan, CRUD produk, serta pengelolaan pesanan.
- Checkout dan pembatalan harus tetap menggunakan transaksi serta penguncian stok.
- Harga dan nama produk dalam detail pesanan disimpan sebagai snapshot saat checkout.
- Ongkir Rp15.000 dan gratis untuk subtotal minimal Rp500.000.
- Status pesanan hanya mengikuti transisi yang didefinisikan oleh enum `StatusPesanan`.
- Nomor resi wajib saat pesanan berubah menjadi dikirim.
- Istilah antarmuka dan dokumentasi tetap berbahasa Indonesia.
- Mata uang ditampilkan dalam Rupiah.

## Brand Commitments

- Nama produk adalah **Beli.**.
- Pengalaman tetap berbahasa Indonesia dan memakai istilah seperti pelanggan, keranjang, checkout, bukti pembayaran, pesanan, dan katalog.
- Beli. adalah proyek e-commerce pembelajaran berbasis Laravel 12.

## Evidence on Hand

- `README.md` — ringkasan fitur, instalasi, akun seeder, dan pengujian.
- `docs/RANCANGAN.md` — ERD, route, aturan bisnis, peran, dan alur status.
- `DESIGN.md` — sistem visual incumbent, warna, tipografi, spacing, dan elevation.
- `docs/PENGUJIAN.md` — hasil dan skenario pengujian.
- `resources/views/` — implementasi antarmuka Laravel/Blade.
- `routes/web.php` dan `app/` — workflow, akses, transaksi, kebijakan, dan layanan.
- Tidak ada bukti penjualan, testimonial, atau klaim performa yang dikonfirmasi.

## Product Principles

- Lindungi konsistensi transaksi sebelum menambah kemudahan permukaan.
- Buat stok, harga, status, dan pembayaran mudah dipahami oleh pelanggan dan admin.
- Pertahankan alur peran yang jelas serta otorisasi pada setiap tindakan pesanan.
- Gunakan bahasa Indonesia yang langsung, tenang, dan dapat ditindaklanjuti.
- Pertahankan produk lama dan riwayat pesanan tetap akurat meski katalog berubah.

## Accessibility & Inclusion

Belum ada standar atau kebutuhan aksesibilitas khusus yang dikonfirmasi. Penggunaan situs tetap harus tetap dapat dibaca, dapat dinavigasi dengan keyboard, memiliki kontras yang memadai, dan memberi label yang jelas pada kontrol serta status pesanan.
