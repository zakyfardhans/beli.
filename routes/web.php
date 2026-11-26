<?php

use App\Http\Controllers\Admin\PesananController as AdminPesananController;
use App\Http\Controllers\Admin\ProdukController as AdminProdukController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\KategoriController as AdminKategoriController;
use App\Http\Controllers\Admin\KuponController as AdminKuponController;
use App\Http\Controllers\UlasanController;
use App\Http\Controllers\WishlistController;
use App\Http\Controllers\ProfilController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\Admin\LaporanController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\KatalogController;
use App\Http\Controllers\KeranjangController;
use App\Http\Controllers\PesananController;
use Illuminate\Support\Facades\Route;

// --- Publik ---
Route::get('/', [KatalogController::class, 'index'])->name('katalog');
Route::get('/produk/{produk:slug}', [KatalogController::class, 'show'])->name('produk.show');
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');

// --- Tamu ---
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:login');
    Route::get('/daftar', [AuthController::class, 'showDaftar'])->name('daftar');
    Route::post('/daftar', [AuthController::class, 'daftar'])->middleware('throttle:daftar');
});

// --- Login ---
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');
Route::get('/pesanan/{pesanan:kode_pesanan}/bukti-bayar', [PesananController::class, 'buktiBayar'])
    ->middleware('auth')
    ->name('pesanan.bukti');

// --- Pelanggan ---
Route::middleware(['auth', 'role:pelanggan'])->group(function () {
    Route::get('/keranjang', [KeranjangController::class, 'index'])->name('keranjang.index');
    Route::post('/keranjang', [KeranjangController::class, 'store'])->name('keranjang.store');
    Route::patch('/keranjang/{item}', [KeranjangController::class, 'update'])->name('keranjang.update');
    Route::delete('/keranjang/{item}', [KeranjangController::class, 'destroy'])->name('keranjang.destroy');

    Route::get('/checkout', [CheckoutController::class, 'create'])->name('checkout.create');
    Route::post('/checkout/beli-langsung', [CheckoutController::class, 'beliLangsung'])->name('checkout.direct');
    Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');

    Route::get('/pesanan', [PesananController::class, 'index'])->name('pesanan.index');
    Route::get('/pesanan/{pesanan:kode_pesanan}', [PesananController::class, 'show'])->name('pesanan.show');
    Route::get('/pesanan/{pesanan:kode_pesanan}/status', [PesananController::class, 'status'])->name('pesanan.status');
    Route::get('/pesanan/{pesanan:kode_pesanan}/qris', [PesananController::class, 'showQris'])->name('pesanan.qris');
    Route::post('/pesanan/{pesanan:kode_pesanan}/bayar', [PesananController::class, 'bayar'])->name('pesanan.bayar');
    Route::patch('/pesanan/{pesanan:kode_pesanan}/batal', [PesananController::class, 'batal'])->name('pesanan.batal');
    Route::patch('/pesanan/{pesanan:kode_pesanan}/terima', [PesananController::class, 'terima'])->name('pesanan.terima');

    // QRIS Scan & Confirm (memerlukan auth untuk memastikan perangkat yang sama)
    Route::get('/qris/{pesanan:kode_pesanan}/{token}', [PesananController::class, 'scanQris'])->name('pesanan.qris.scan');
    Route::post('/qris/{pesanan:kode_pesanan}/{token}/konfirmasi', [PesananController::class, 'konfirmasiQris'])->name('pesanan.qris.konfirmasi');
    Route::post('/produk/{produk:slug}/ulasan', [UlasanController::class, 'store'])->name('produk.ulasan');
    Route::post('/produk/{produk:slug}/favorit', [WishlistController::class, 'toggle'])->name('produk.favorit');
    Route::get('/profil', [ProfilController::class, 'index'])->name('profil.index');
    Route::patch('/profil', [ProfilController::class, 'update'])->name('profil.update');
    Route::post('/profil/alamat', [ProfilController::class, 'alamatStore'])->name('profil.alamat.store');
    Route::patch('/profil/alamat/{alamat}', [ProfilController::class, 'alamatUpdate'])->name('profil.alamat.update');
    Route::delete('/profil/alamat/{alamat}', [ProfilController::class, 'alamatDestroy'])->name('profil.alamat.destroy');
    Route::get('/favorit', [WishlistController::class, 'index'])->name('favorit.index');
});

// --- Admin ---
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('produk', AdminProdukController::class)->except('show');
    Route::resource('kategori', AdminKategoriController::class)->except('show');
    Route::resource('kupon', AdminKuponController::class)->except('show');
    Route::get('/laporan/penjualan', [LaporanController::class, 'penjualan'])->name('laporan.penjualan');

    Route::get('/pesanan', [AdminPesananController::class, 'index'])->name('pesanan.index');
    Route::get('/pesanan/{pesanan:kode_pesanan}', [AdminPesananController::class, 'show'])->name('pesanan.show');
    Route::patch('/pesanan/{pesanan:kode_pesanan}/status', [AdminPesananController::class, 'ubahStatus'])->name('pesanan.status');
});
