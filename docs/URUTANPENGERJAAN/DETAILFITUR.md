# Detail Fitur & Urutan Pengerjaan ” Beli.

> Catatan progres implementasi Tugas 3 (Toko Online). Setiap langkah selesai dicatat di sini.
> Acuan rancangan: [`docs/RANCANGAN.md`](../RANCANGAN.md)

---

## Langkah 1 ” Konfigurasi âœ…

**Tujuan:** menyiapkan locale/timezone Indonesia dan token design system Tailwind v4 dari `DESIGN.md`.

| File                    | Perubahan                                                                                                                                                                                                       |
| ----------------------- | --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `.env`                  | `APP_LOCALE=id`, `APP_FALLBACK_LOCALE=id`, `APP_FAKER_LOCALE=id_ID`, `APP_TIMEZONE=Asia/Jakarta`                                                                                                                |
| `.env.example`          | `APP_NAME="Beli."` + locale/timezone yang sama (agar setup dari nol konsisten)                                                                                                                                  |
| `resources/css/app.css` | `@theme` Tailwind v4: warna brand teal (`brand`, `brand-dark`, `brand-light`), netral (`ink`, `muted`, `canvas`, `line`), 6 pasang warna badge status, `shadow-soft`/`shadow-float`, font **Plus Jakarta Sans** |

**Catatan:**

- DB tetap `db_toko_online_zaky` (MySQL, terverifikasi terhubung).
- Tampilan memakai Tailwind v4 via Vite/NPM (bukan CDN).

---

## Langkah 2 ” Migration + Model + Relasi âœ…

**Migration** (urutan FK benar, semua berjalan via `migrate:fresh`):

| File                                   | Tabel            | Constraint penting                                                              |
| -------------------------------------- | ---------------- | ------------------------------------------------------------------------------- |
| `0001_01_01_000000_create_users_table` | `users`          | + `no_hp`, `alamat`, `role` enum(admin,pelanggan) default pelanggan             |
| `..._create_kategoris_table`           | `kategori`       | `slug` unik                                                                     |
| `..._create_produks_table`             | `produk`         | FK kategori restrict; `slug` unik                                               |
| `..._create_keranjangs_table`          | `keranjang`      | FK user/produk cascade; **unik(user_id, produk_id)**                            |
| `..._create_pesanans_table`            | `pesanan`        | `kode_pesanan` unik; status enum 6 nilai; user restrict                         |
| `..._create_detail_pesanans_table`     | `detail_pesanan` | FK pesanan cascade, produk restrict; **snapshot** `nama_produk`, `harga_satuan` |

**Model** (semua dengan `protected $table` + `$fillable` eksplisit):

| Model           | Relasi                                    | Catatan                                                              |
| --------------- | ----------------------------------------- | -------------------------------------------------------------------- |
| `User`          | hasMany keranjang, pesanan                | `role` **tidak** di `$fillable`; helper `isAdmin()`, `isPelanggan()` |
| `Kategori`      | hasMany produk                            |                                                                      |
| `Produk`        | belongsTo kategori, hasMany detailPesanan | cast int/bool; scope `aktif()`                                       |
| `Keranjang`     | belongsTo user, produk                    | accessor `subtotal`                                                  |
| `Pesanan`       | belongsTo user, hasMany detailPesanan     | cast `status` â†’ enum; `getRouteKeyName()` = `kode_pesanan`         |
| `DetailPesanan` | belongsTo pesanan, produk                 | cast int                                                             |

**Konfigurasi tambahan:** `config/app.php` timezone diubah dari hardcode `'UTC'` â†’ `env('APP_TIMEZONE')`. `storage:link` aktif (untuk gambar produk & bukti bayar).

---

## Langkah 3 ” Enum `StatusPesanan` âœ…

`app/Enums/StatusPesanan.php` (backed string, 6 case) dengan:

- `label()` ” label Indonesia untuk badge.
- **`bolehKe(self $tujuan): bool`** ” satu-satunya sumber aturan transisi (tidak ada di controller).
- `transisiValid(): array` ” status tujuan valid (untuk render tombol admin).
- `butuhResi(self $tujuan): bool` ” true bila tujuan `dikirim`.

**Verifikasi:** `menunggu_pembayaran â†’ menunggu_verifikasi` = valid; `menunggu_pembayaran â†’ dikirim` = ditolak (skenario uji #7). Timezone terverifikasi `Asia/Jakarta`.

---

## Langkah 4 ” Auth + Middleware Role + Policy + Seeder âœ…

**Auth (F-01):**

- `AuthController`: `showLogin`, `login` (Auth::attempt, redirect adminâ†’`/admin/dashboard`, pelangganâ†’`/`), `showDaftar`, `daftar` (validasi lengkap; **role tidak divalidasi/diisi** â†’ default pelanggan), `logout`.
- `routes/web.php`: grup `guest` untuk `/login` & `/daftar`; `POST /logout` dengan `auth`.
- Pesan validasi berbahasa Indonesia; login gagal mempertahankan input email (`onlyInput('email')`).

**Otorisasi:**

- `app/Http/Middleware/CekRole.php` ” tolak 403 bila role tidak cocok; alias `role` didaftarkan di `bootstrap/app.php`.
- `app/Policies/PesananPolicy.php` ” `view`/`bayar`/`batal` hanya pemilik; admin bypass via `before()`. Didaftarkan di `AppServiceProvider@boot` (`Gate::policy`).

**Seeder** (`migrate:fresh --seed` bersih):

| Seeder            | Isi                                                                    |
| ----------------- | ---------------------------------------------------------------------- |
| `AdminSeeder`     | 1 admin (`admin@beli.test`); role di-set eksplisit (tidak mass-assign) |
| `PelangganSeeder` | 3 pelanggan (`zaky@`, `rina@`, `budi@beli.test`)                       |
| `KategoriSeeder`  | 4 kategori + slug                                                      |
| `ProdukSeeder`    | 14 produk (beberapa stok 0 & â‰¤5)                                     |
| `PesananSeeder`   | 5 pesanan status bervariasi + 8 detail (snapshot)                      |

Semua password `password`. Terverifikasi: users=4 (1 admin, 3 pelanggan), kategori=4, produk=14, pesanan=5, detail=8; cast enum & snapshot bekerja.

---

## Langkah 5 â€” Layout, Navbar, Katalog, Keranjang, Auth View âœ…

**Helper & composer:**

- `app/Support/Rupiah.php` â€” `Rupiah::format()` satu kali, dipakai semua view.
- `AppServiceProvider@boot` â€” `View::composer('*')` bagikan `$jumlahKeranjang` (badge navbar) untuk pelanggan login.

**Layout & view:**

- `resources/views/layouts/app.blade.php` â€” Tailwind v4 (`@vite`), Plus Jakarta Sans, include navbar, flash `sukses`/`galat`, footer.
- `resources/views/partials/navbar.blade.php` â€” logo beli., search, badge keranjang, menu login/daftar atau logout + tautan role (admin â†’ dashboard, pelanggan â†’ pesanan).
- `auth/login.blade.php`, `auth/daftar.blade.php` â€” form + tampil error validasi + `old()`.
- `admin/dashboard.blade.php` â€” placeholder ringkas.

**F-02 Katalog & detail:**

- `KatalogController@index` â€” produk `aktif`, eager `with('kategori')`, cari `?q=`, filter `?kategori=`, `paginate(12)->withQueryString()`.
- `KatalogController@show` â€” `/produk/{slug}`, 404 bila nonaktif, produk terkait.
- View `katalog/index` (chip kategori, label HABIS, kartu grid) & `katalog/show` (harga Rupiah, input jumlah 1..stok, tombol tambah / masuk).

**F-03 Keranjang:**

- `KeranjangController@index/store/update/destroy` â€” validasi stok; produk sama menambah jumlah (bukan baris baru); kepemilikan dicek.
- View `keranjang/index` â€” ubah jumlah, hapus (konfirmasi), subtotal per baris + total.

**Stub (sementara, diisi Langkah 7â€“8):** `CheckoutController`, `PesananController`.

**Verifikasi:** `migrate:fresh --seed` bersih. Render HTTP: `/`=200, `/produk/{slug}`=200, `/login`=200, `/daftar`=200, `/keranjang` (pelanggan)=200, `/admin/dashboard` (admin)=200. `view:cache` sukses. `route:list` = 15 route.

---

## Langkah 6 â€” CRUD Produk Admin (F-08) âœ…

**Validasi:** `app/Http/Requests/ProdukRequest.php` â€” `nama`, `kategori_id` (exists), `deskripsi`, `harga`â‰¥0, `stok`â‰¥0, `gambar` (image, jpg/png, â‰¤2MB), `aktif`; semua pesan Indonesia. `authorize()` hanya admin.

**Controller:** `Admin\ProdukController` (resource tanpa `show`):

- `index` â€” search nama + paginate 10.
- `store`/`update` â€” slug unik otomatis (`uniqueSlug()`), upload gambar ke `storage/app/public/produk`, **hapus file lama** saat ganti gambar.
- `destroy` â€” **menolak** bila produk pernah dipesan (`detailPesanan()->exists()`) â†’ pesan sarankan nonaktifkan; hapus file gambar.

**Layout & view:** `layouts/admin.blade.php` (sidebar: Dashboard/Produk/Pesanan/Katalog + logout). View `admin/produk/{_form,index,create,edit}`.

**Route:** `Route::resource('produk', AdminProdukController::class)->except('show')` di grup admin. Stub route pesanan admin + `Admin\PesananController` + placeholder view.

**Verifikasi:** `/admin/produk`, `/create`, `/{id}/edit`, `/admin/pesanan`, `/admin/dashboard` = 200. Uji tinker: produk baru tersimpan dengan slug benar; produk yang pernah dipesan terdeteksi (guard hapus aktif). Data uji dibersihkan (14 produk).

---

## Langkah 7 â€” Checkout, Transaksi, Snapshot, Ongkir (F-04) âœ…

**Exception:** `app/Exceptions/StokTidakCukupException.php` â€” pesan menyebut nama produk, jumlah diminta & tersedia.

**Service:** `app/Services/CheckoutService.php`:

- `checkout()` di dalam `DB::transaction()`; item keranjang & produk dibaca `lockForUpdate()`.
- Cek ulang stok â†’ lempar exception (rollback).
- Hitung subtotal, `hitungOngkir()` (0 bila â‰¥ Rp500.000, else Rp15.000), total.
- `generateKodePesanan()` format `INV-YYYYMMDD-XXXX` (urut harian 4 digit).
- Buat `pesanan` + `detail_pesanan` dengan **snapshot** `nama_produk` & `harga_satuan`; kurangi stok; kosongkan keranjang.

**Controller & view:** `CheckoutController@create` (form prefilled + ringkasan + ongkir/total), `@store` (tangkap exception â†’ pesan). View `checkout/create.blade.php`.

**Verifikasi uji (via tinker):**

- Ongkir: 520.000 â†’ 0; 100.000 â†’ 15.000 âœ… (skenario #4)
- Checkout: kode `INV-20261119-0006`, stok 120â†’118, keranjang kosong, snapshot tersimpan âœ…
- Snapshot: harga produk diubah ke 999.999, `detail.harga_satuan` tetap 5.000 âœ… (skenario #5)
- Stok 1, dua pelanggan: A sukses (stokâ†’0), B gagal dengan pesan jelas, stok tetap 0 (tidak negatif), tak ada pesanan setengah jadi âœ… (skenario #3)
- Render `/checkout` = 200.

Data di-reset `migrate:fresh --seed` setelah uji.

---

## Langkah 8 â€” Riwayat, Pembayaran, Pembatalan, Kelola Pesanan Admin (F-05..F-09) âœ…

**Perbaikan:** `app/Http/Controllers/Controller.php` ditambah trait `AuthorizesRequests` (Laravel 12 tidak menyertakannya otomatis) agar `$this->authorize()` tersedia.

**Pelanggan â€” `PesananController`:**

- `index` â€” daftar pesanan milik sendiri + badge status + pagination.
- `show` â€” Policy `view` (403 bila bukan miliknya).
- `bayar` â€” Policy `bayar`; hanya saat `menunggu_pembayaran`; upload bukti (JPG/PNG â‰¤2MB) â†’ status `menunggu_verifikasi`; hapus bukti lama bila ada.
- `batal` â€” Policy `batal`; hanya bila `bolehKe(dibatalkan)`; kembalikan stok dalam `DB::transaction`.

**Admin â€” `Admin\PesananController`:**

- `index` â€” filter `status` + cari `kode_pesanan`, pagination.
- `show` â€” detail + bukti bayar; tombol hanya dari `status->transisiValid()`.
- `ubahStatus` â€” validasi `bolehKe()` (aturan dari enum), resi wajib saat `dikirim` (`ValidationException`), stok dikembalikan bila `dibatalkan`, semua dalam transaksi.

**View:** `partials/status-badge` (warna per status), `pesanan/{index,show}`, `admin/pesanan/{index,show}`.

**Verifikasi:**

- Skenario #6: A akses pesanan B = **403**; pesanan sendiri = 200 âœ…
- Skenario #7: `menunggu_pembayaran â†’ dikirim` ditolak, status tetap âœ…
- Skenario #8: batal `menunggu_pembayaran` â†’ `dibatalkan`, stok 120â†’122 (+2) âœ…
- Render: `/pesanan`=200, `/admin/pesanan`=200, `/admin/pesanan/{kode}`=200.

Data di-reset `migrate:fresh --seed`.

---

## Langkah 9 â€” Pengujian Lengkap, README, Laporan âœ…

**9 skenario wajib â€” semua LULUS** (detail di [`docs/PENGUJIAN.md`](../PENGUJIAN.md)):

| #   | Skenario                  | Hasil                               |
| --- | ------------------------- | ----------------------------------- |
| 1   | Produk sama 2-            | 1 baris, jumlah 2                   |
| 2   | Ubah jumlah > stok        | ditolak, jumlah tetap               |
| 3   | Stok 1, dua checkout      | A sukses, B gagal, stok tak negatif |
| 4   | Ongkir 520rb/100rb        | 0 / 15.000                          |
| 5   | Ubah harga pasca-pesanan  | snapshot tetap                      |
| 6   | Akses pesanan orang lain  | 403                                 |
| 7   | Transisi ilegal           | ditolak                             |
| 8   | Batal menunggu_pembayaran | dibatalkan + stok kembali           |
| 9   | Injeksi role=admin        | tetap pelanggan                     |

**Dokumentasi:**

- `README.md` â€” setup dari nol + akun seeder + struktur penting.
- `docs/PENGUJIAN.md` â€” tabel hasil 9 skenario.

**Verifikasi akhir:**

- `route:list` â€” 30 route (publik, auth, pelanggan, admin) sesuai rancangan.
- `migrate:fresh --seed` dari DB kosong â€” bersih tanpa galat.
- `view:cache` â€” semua Blade terkompilasi.

---

## Status Akhir

âœ… **Seluruh cakupan wajib F-01 s.d. F-09 selesai dan terverifikasi.**

Komponen rubrik terpenuhi: Rancangan (8), DB & seeder (12), Katalog (10), Keranjang (10), Checkout/transaksi/stok/snapshot (20), Alur status & kelola pesanan (15), Otorisasi & keamanan (10), Tampilan (10), Kualitas kode & laporan (5).

**Sisa opsional (Tantangan Tambahan, tidak wajib):** dashboard agregasi, konfirmasi terima (dikirimâ†’selesai oleh pelanggan), filter harga/urut, CRUD kategori, kupon, ulasan, notifikasi email.

---

## Tahap Lanjutan 1 â€” Test Suite Otomatis âœ…

**Factories** (`database/factories/`): `KategoriFactory`, `ProdukFactory`, `PesananFactory`, `DetailPesananFactory`; `UserFactory` + state `role()/admin()/pelanggan()` (set role eksplisit via `afterCreating` karena tidak di `$fillable`).

**Test Feature** (SQLite in-memory + `RefreshDatabase`):

| File                     | Skenario                                                   |
| ------------------------ | ---------------------------------------------------------- |
| `KeranjangTest`          | #1 merge item, #2 ubah jumlah > stok                       |
| `CheckoutTest`           | #3 stok habis, #4 ongkir, #5 snapshot                      |
| `OtorisasiDanStatusTest` | #6 403, #7 transisi ilegal, #8 batal+stok, #9 injeksi role |
| `ExampleTest`            | smoke test katalog                                         |

**Hasil:** `php artisan test` â†’ **11 passed (25 assertions)** dalam ~1 detik. Menjalankan `npm run test`/CI kini memverifikasi seluruh aturan bisnis inti otomatis.

---

## Tahap Lanjutan 2 â€” Hardening Dasar âœ…

- **Rate limiting:** `RateLimiter::for('login')` (5/menit per email+IP) & `('daftar')` (10/menit per IP) di `AppServiceProvider`; diterapkan via `throttle:login` / `throttle:daftar` di route.
- **Halaman error kustom:** `resources/views/errors/403.blade.php` & `404.blade.php` (desain konsisten). Terverifikasi 404 memakai konten kustom.
- **Rekening via config:** `config/beli.php` + `.env` (`BELI_BANK`, `BELI_REKENING`, `BELI_ATAS_NAMA`); view `pesanan/show` memakai `config('beli.rekening.*')`. `.env.example` diperbarui.

**Verifikasi:** `view:cache` OK; `php artisan test` â†’ 11 passed; 404 kustom tampil.

---

## Penyesuaian â€” Reposisi ke E-Commerce Profesional âœ…

Proyek **bukan** "koperasi sekolah", melainkan platform e-commerce profesional. Teks & data disejajarkan:

| File                                     | Perubahan                                                                              |
| ---------------------------------------- | -------------------------------------------------------------------------------------- |
| `resources/views/pesanan/show.blade.php` | Instruksi transfer: "rekening resmi Beli.", "BCA 1234567890", "a.n. PT Beli Indonesia" |
| `database/seeders/AdminSeeder.php`       | Nama "Admin Beli.", alamat "Kantor Pusat Beli., Jakarta"                               |
| `database/seeders/KategoriSeeder.php`    | Kategori: Elektronik, Fashion, Rumah Tangga, Kecantikan                                |
| `database/seeders/ProdukSeeder.php`      | 14 produk e-commerce umum (TWS, smartwatch, sneakers, tumbler, serum, dll.)            |
| `README.md`                              | Judul & deskripsi: "Platform E-Commerce"                                               |

Data awal tetap memenuhi syarat: 4 kategori, 14 produk (3 stok 0, 3 stok â‰¤5), 5 pesanan status bervariasi. `migrate:fresh --seed` bersih.
