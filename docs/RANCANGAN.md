# Rancangan Sistem ” Beli. (Toko Online)

> Jobsheet Tugas 3 ” E-Commerce Kelompok 3 Laravel 12 DB `db_toko_online_zaky`

Dokumen ini menjadi acuan implementasi (ERD, route, aturan bisnis, alur status) sebelum penulisan kode, sesuai bagian **B. Merancang Sebelum Mengoding** pada jobsheet.

---

## 1. Ringkasan

Website e-commerce menjual produk secara daring. Pelanggan memilih produk, memasukkan ke keranjang, checkout, lalu mengunggah bukti transfer. Admin memverifikasi pembayaran, memproses, dan mengirim pesanan.

**Tantangan utama:** konsistensi data ” stok tidak negatif walau checkout hampir bersamaan, harga pada pesanan lama tidak berubah saat harga produk diubah (snapshot), dan status pesanan hanya berpindah melalui alur valid.

---

## 2. Aktor dan Hak Akses

| Aktor             | Hak akses dan kegiatan                                                                                                                    |
| ----------------- | ----------------------------------------------------------------------------------------------------------------------------------------- |
| Pengunjung        | Melihat katalog dan detail produk, mendaftar, login.                                                                                      |
| Pelanggan (login) | Mengelola keranjang, checkout, melihat riwayat & detail pesanan miliknya, mengunggah bukti bayar, membatalkan pesanan yang belum dibayar. |
| Admin (login)     | Mengelola produk (CRUD), memverifikasi pembayaran, mengubah status pesanan (diproses, dikirim, selesai), mengisi nomor resi.              |

---

## 3. ERD (Rancangan Basis Data)

Enam tabel. Seluruh foreign key dideklarasikan sebagai constraint pada migration. Nama tabel berbahasa Indonesia (wajib `protected $table` di model).

### 3.1 `users`

| Kolom      | Tipe                                           | Keterangan                            |
| ---------- | ---------------------------------------------- | ------------------------------------- |
| id         | bigint PK                                      |                                       |
| name       | string                                         |                                       |
| email      | string, **unik**                               |                                       |
| password   | string                                         | hash                                  |
| no_hp      | string                                         |                                       |
| alamat     | text, nullable                                 |                                       |
| role       | enum(`admin`,`pelanggan`), default `pelanggan` | **tidak** boleh mass-assign dari form |
| timestamps |                                                |                                       |

Relasi: `hasMany` keranjang, `hasMany` pesanan.

### 3.2 `kategori`

| Kolom      | Tipe             | Keterangan |
| ---------- | ---------------- | ---------- |
| id         | bigint PK        |            |
| nama       | string           |            |
| slug       | string, **unik** |            |
| timestamps |                  |            |

Relasi: `hasMany` produk.

### 3.3 `produk`

| Kolom       | Tipe                       | Keterangan         |
| ----------- | -------------------------- | ------------------ |
| id          | bigint PK                  |                    |
| kategori_id | FK â†’ kategori (restrict) |                    |
| nama        | string                     |                    |
| slug        | string, **unik**           | otomatis dari nama |
| deskripsi   | text                       |                    |
| harga       | unsigned int               | Rupiah             |
| stok        | unsigned int               |                    |
| gambar      | string, nullable           | storage/app/public |
| aktif       | boolean, default true      |                    |
| timestamps  |                            |                    |

Relasi: `belongsTo` kategori; `hasMany` detail_pesanan.

### 3.4 `keranjang`

| Kolom                        | Tipe                    | Keterangan                     |
| ---------------------------- | ----------------------- | ------------------------------ |
| id                           | bigint PK               |                                |
| user_id                      | FK â†’ users (cascade)  |                                |
| produk_id                    | FK â†’ produk (cascade) |                                |
| jumlah                       | unsigned smallint       |                                |
| **unik(user_id, produk_id)** |                         | satu baris per produk per user |

Relasi: `belongsTo` user, `belongsTo` produk.

### 3.5 `pesanan`

| Kolom             | Tipe                                        | Keterangan                 |
| ----------------- | ------------------------------------------- | -------------------------- |
| id                | bigint PK                                   |                            |
| kode_pesanan      | string, **unik**                            | format `INV-YYYYMMDD-XXXX` |
| user_id           | FK â†’ users                                |                            |
| nama_penerima     | string                                      |                            |
| no_hp             | string                                      |                            |
| alamat_pengiriman | text                                        |                            |
| subtotal          | unsigned int                                |                            |
| ongkir            | unsigned int                                | 0 atau 15000               |
| total             | unsigned int                                |                            |
| status            | enum 6 nilai, default `menunggu_pembayaran` | cast `StatusPesanan`       |
| bukti_bayar       | string, nullable                            |                            |
| no_resi           | string, nullable                            | wajib saat `dikirim`       |
| timestamps        |                                             |                            |

Relasi: `belongsTo` user; `hasMany` detail_pesanan.

### 3.6 `detail_pesanan`

| Kolom        | Tipe                     | Keterangan   |
| ------------ | ------------------------ | ------------ |
| id           | bigint PK                |              |
| pesanan_id   | FK â†’ pesanan (cascade) |              |
| produk_id    | FK â†’ produk (restrict) |              |
| nama_produk  | string                   | **snapshot** |
| harga_satuan | unsigned int             | **snapshot** |
| jumlah       | unsigned smallint        |              |
| subtotal     | unsigned int             |              |

Relasi: `belongsTo` pesanan, `belongsTo` produk.

---

## 4. Daftar Route

| Method                   | URI                               | Controller / aksi                                            | Akses              |
| ------------------------ | --------------------------------- | ------------------------------------------------------------ | ------------------ |
| GET                      | `/`                               | `KatalogController@index` (name: katalog)                    | Publik             |
| GET                      | `/produk/{produk:slug}`           | `KatalogController@show`                                     | Publik             |
| GET, POST                | `/daftar`                         | `AuthController@showDaftar`, `@daftar`                       | Tamu               |
| GET, POST                | `/login`                          | `AuthController@showLogin`, `@login`                         | Tamu               |
| POST                     | `/logout`                         | `AuthController@logout`                                      | Login              |
| GET, POST, PATCH, DELETE | `/keranjang`, `/keranjang/{item}` | `KeranjangController@index`, `@store`, `@update`, `@destroy` | Pelanggan          |
| GET, POST                | `/checkout`                       | `CheckoutController@create`, `@store`                        | Pelanggan          |
| GET                      | `/pesanan`                        | `PesananController@index`                                    | Pelanggan          |
| GET                      | `/pesanan/{pesanan:kode_pesanan}` | `PesananController@show`                                     | Pelanggan (Policy) |
| POST                     | `/pesanan/{pesanan}/bayar`        | `PesananController@bayar`                                    | Pelanggan (Policy) |
| PATCH                    | `/pesanan/{pesanan}/batal`        | `PesananController@batal`                                    | Pelanggan (Policy) |
| resource                 | `/admin/produk`                   | `Admin\ProdukController`                                     | Admin              |
| GET                      | `/admin/pesanan`                  | `Admin\PesananController@index`                              | Admin              |
| GET                      | `/admin/pesanan/{pesanan}`        | `Admin\PesananController@show`                               | Admin              |
| PATCH                    | `/admin/pesanan/{pesanan}/status` | `Admin\PesananController@ubahStatus`                         | Admin              |
| GET                      | `/admin/dashboard`                | `Admin\DashboardController@index`                            | Admin              |

Middleware: alias `role` di `bootstrap/app.php`; grup `auth`+`role:pelanggan` dan `auth`+`role:admin`.

---

## 5. Aturan Bisnis

- **Ongkir:** tetap Rp15.000; **gratis bila subtotal â‰¥ Rp500.000**.
- **Kode pesanan:** `INV-YYYYMMDD-XXXX`, XXXX nomor urut harian 4 digit.
- **Stok:** berkurang saat checkout berhasil; dikembalikan bila pesanan dibatalkan (dalam transaksi).
- **Snapshot:** `detail_pesanan.nama_produk` & `harga_satuan` disimpan saat checkout; perubahan produk tidak memengaruhi pesanan lama.
- **Konsistensi:** checkout & pembatalan dibungkus `DB::transaction()` + `lockForUpdate()` saat membaca stok. Jika stok kurang â†’ `StokTidakCukupException` â†’ rollback seluruh proses + pesan menyebut produk bermasalah.

---

## 6. Alur Status Pesanan

```
menunggu_pembayaran â†’ menunggu_verifikasi â†’ diproses â†’ dikirim â†’ selesai
       â”‚                     â”‚                â”‚
       â””â”€â”€â”€â”€ dibatalkan â”€â”€â”€â”€â”€â”´â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”˜
```

Aturan transisi (ditaruh **hanya** di `App\Enums\StatusPesanan::bolehKe()`):

| Dari                | Ke                  | Oleh                     |
| ------------------- | ------------------- | ------------------------ |
| menunggu_pembayaran | menunggu_verifikasi | pelanggan (unggah bukti) |
| menunggu_pembayaran | dibatalkan          | pelanggan **atau** admin |
| menunggu_verifikasi | diproses            | admin                    |
| menunggu_verifikasi | dibatalkan          | admin                    |
| diproses            | dikirim             | admin (wajib resi)       |
| diproses            | dibatalkan          | admin                    |
| dikirim             | selesai             | admin                    |

Transisi di luar tabel ini **ditolak**. Controller tidak boleh memiliki aturan alur sendiri ” memakai `bolehKe()`.

---

## 7. Peta Fitur Wajib

| Kode | Fitur                                                                     |
| ---- | ------------------------------------------------------------------------- |
| F-01 | Registrasi & login (+role aman dari mass assignment, redirect per role)   |
| F-02 | Katalog & detail produk (cari, filter kategori, paginate 12, label Habis) |
| F-03 | Keranjang (tambah/ubah/hapus, validasi stok, badge navbar)                |
| F-04 | Checkout + snapshot + ongkir + transaksi + lockForUpdate                  |
| F-05 | Riwayat & detail pesanan + Policy                                         |
| F-06 | Pembayaran manual (unggah bukti)                                          |
| F-07 | Pembatalan (kembalikan stok)                                              |
| F-08 | CRUD produk admin (gambar, slug, aktif/nonaktif)                          |
| F-09 | Kelola pesanan admin (filter, bukti, transisi valid, resi)                |

---

## 8. Urutan Pengerjaan

1. Konfigurasi `.env` (locale `id`, timezone `Asia/Jakarta`) & token Tailwind v4 dari `DESIGN.md`.
2. Migration (urut FK) â†’ Model + relasi + `$fillable`.
3. Enum `StatusPesanan` + `bolehKe()`.
4. Auth + middleware `role` + Policy + seeder.
5. Katalog, keranjang, CRUD produk admin.
6. `CheckoutService` + transaksi + snapshot + ongkir.
7. Riwayat, pembayaran, pembatalan, kelola pesanan admin.
8. Uji 9 skenario, README, laporan.

## 9. Variasi Ukuran dan Warna

- Admin mengatur stok untuk setiap kombinasi ukuran dan warna pada `produk_variasi`. Kolom `produk.stok` menyimpan jumlah seluruh stok variasi agar katalog tetap memakai stok total.
- `keranjang.produk_variasi_id` dan `variasi_key` memisahkan baris keranjang per kombinasi; produk tanpa variasi tetap memakai satu baris.
- Detail pesanan menyimpan ID variasi serta snapshot ukuran dan warna. Checkout mengurangi stok kombinasi dan stok total dalam transaksi; pembatalan pelanggan maupun admin mengembalikan keduanya.
- `produk.panduan_ukuran` menyimpan baris panduan yang dikelola admin per produk dan ditampilkan sebagai tabel di detail produk.
