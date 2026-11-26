<?php

namespace App\Http\Controllers;

use App\Exceptions\StokTidakCukupException;
use App\Models\Alamat;
use App\Models\Keranjang;
use App\Models\Produk;
use App\Services\CheckoutService;
use Illuminate\Validation\Rule;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
    private const SESSION_CHECKOUT_LANGSUNG = 'checkout_langsung';

    public function __construct(private CheckoutService $checkoutService)
    {
    }

    public function create(Request $request): View|RedirectResponse
    {
        $checkoutLangsung = $request->query('mode') === 'langsung';
        $token = $checkoutLangsung ? (string) $request->query('token', '') : '';

        if ($checkoutLangsung) {
            $dataLangsung = Str::isUuid($token) ? $this->checkoutLangsung($request, $token) : null;

            if (! $dataLangsung) {
                return redirect()->route('katalog')->with(
                    'galat',
                    'Sesi beli langsung tidak ditemukan. Silakan pilih produk kembali.',
                );
            }

            $produk = Produk::aktif()->find($dataLangsung['produk_id']);

            if (! $produk) {
                $request->session()->forget(self::SESSION_CHECKOUT_LANGSUNG);

                return redirect()->route('katalog')->with('galat', 'Produk ini sudah tidak tersedia.');
            }

            $variasiLangsung = $dataLangsung['produk_variasi_id']
                ? $produk->variasi()->whereKey($dataLangsung['produk_variasi_id'])->first()
                : null;

            if (
                ($produk->variasi()->exists() && ! $variasiLangsung)
                || ($dataLangsung['produk_variasi_id'] && ! $variasiLangsung)
                || $dataLangsung['jumlah'] > ($variasiLangsung?->stok ?? $produk->stok)
            ) {
                $request->session()->forget(self::SESSION_CHECKOUT_LANGSUNG);

                return redirect()->route('produk.show', $produk)
                    ->with('galat', 'Stok untuk pilihan ini tidak mencukupi. Pilih variasi lain.');
            }

            $items = collect([(object) [
                'produk' => $produk,
                'variasi' => $variasiLangsung,
                'jumlah' => $dataLangsung['jumlah'],
            ]]);
        } else {
            $items = Keranjang::with(['produk', 'variasi'])
                ->where('user_id', auth()->id())
                ->get();

            if ($items->contains(fn ($item) => $item->produk->variasi()->exists() && ! $item->variasi)) {
                return redirect()->route('keranjang.index')->with(
                    'galat',
                    'Ada pilihan variasi yang sudah tidak tersedia. Perbarui keranjang sebelum checkout.',
                );
            }

            $stokTidakCukup = $items->first(
                fn ($item) => $item->jumlah > ($item->variasi?->stok ?? $item->produk->stok),
            );

            if ($stokTidakCukup) {
                return redirect()->route('keranjang.index')->with(
                    'galat',
                    "Stok untuk {$stokTidakCukup->produk->nama} tidak mencukupi. Ubah jumlah di keranjang.",
                );
            }
        }

        if ($items->isEmpty()) {
            return redirect()->route('keranjang.index')->with('galat', 'Keranjang kosong. Tambahkan produk dulu.');
        }

        $subtotal = $items->sum(fn ($i) => $i->produk->harga * $i->jumlah);
        $ongkir = $this->checkoutService->hitungOngkir($subtotal);
        $kodeKupon = strtoupper(trim((string) $request->query('kode_kupon', '')));
        $pratinjauKupon = $kodeKupon !== ''
            ? $this->checkoutService->pratinjauKupon($kodeKupon, $subtotal)
            : null;
        $diskon = $pratinjauKupon['diskon'] ?? 0;
        $subtotalSetelahDiskon = max(0, $subtotal - $diskon);
        $total = $subtotalSetelahDiskon + $ongkir;
        $galatKupon = $kodeKupon !== '' && ! $pratinjauKupon
            ? 'Kode kupon tidak valid, kedaluwarsa, atau kuotanya habis.'
            : null;
        $alamat = Alamat::where('user_id', auth()->id())->orderByDesc('utama')->latest()->get();
        $alamatUtama = $alamat->firstWhere('utama', true) ?? $alamat->first();

        return view('checkout.create', compact(
            'items',
            'subtotal',
            'ongkir',
            'diskon',
            'subtotalSetelahDiskon',
            'total',
            'kodeKupon',
            'pratinjauKupon',
            'galatKupon',
            'alamat',
            'alamatUtama',
            'checkoutLangsung',
            'token',
        ));
    }

    public function beliLangsung(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'produk_id' => ['required', 'integer', 'exists:produk,id'],
            'jumlah' => ['required', 'integer', 'min:1'],
            'ukuran' => ['nullable', 'string', 'max:40'],
            'warna' => ['nullable', 'string', 'max:60'],
        ], [
            'produk_id.required' => 'Produk tidak valid.',
            'produk_id.exists' => 'Produk tidak ditemukan.',
            'jumlah.required' => 'Jumlah wajib diisi.',
            'jumlah.integer' => 'Jumlah harus berupa angka.',
            'jumlah.min' => 'Jumlah minimal 1.',
        ]);

        $produk = Produk::aktif()->findOrFail($data['produk_id']);
        $jumlah = (int) $data['jumlah'];
        $variasi = null;

        if ($produk->variasi()->exists()) {
            if (empty($data['ukuran']) || empty($data['warna'])) {
                return back()->withInput()->withErrors([
                    'ukuran' => 'Pilih ukuran dan warna terlebih dahulu.',
                ]);
            }

            $variasi = $produk->cariVariasi($data['ukuran'], $data['warna']);

            if (! $variasi) {
                return back()->withInput()->withErrors([
                    'warna' => 'Kombinasi ukuran dan warna tidak tersedia.',
                ]);
            }
        } elseif (! empty($data['ukuran']) || ! empty($data['warna'])) {
            return back()->withInput()->withErrors([
                'ukuran' => 'Pilihan variasi produk tidak valid.',
            ]);
        }

        $stokTersedia = $variasi?->stok ?? $produk->stok;

        if ($jumlah > $stokTersedia) {
            return back()->withInput()->with('galat', "Jumlah melebihi stok tersedia ({$stokTersedia}).");
        }

        $token = Str::uuid()->toString();
        $request->session()->put(self::SESSION_CHECKOUT_LANGSUNG, [
            'token' => $token,
            'produk_id' => $produk->id,
            'produk_variasi_id' => $variasi?->id,
            'jumlah' => $jumlah,
        ]);

        return redirect()->route('checkout.create', [
            'mode' => 'langsung',
            'token' => $token,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'mode' => ['nullable', Rule::in(['keranjang', 'langsung'])],
            'token' => ['nullable', 'uuid', Rule::requiredIf($request->input('mode') === 'langsung')],
            'alamat_id' => ['nullable', 'integer'],
            'nama_penerima' => [Rule::requiredIf(! $request->filled('alamat_id')), 'nullable', 'string', 'max:150'],
            'no_hp' => [Rule::requiredIf(! $request->filled('alamat_id')), 'nullable', 'string', 'regex:/^08[0-9]{8,11}$/'],
            'alamat_pengiriman' => [Rule::requiredIf(! $request->filled('alamat_id')), 'nullable', 'string', 'max:500'],
            'kode_kupon' => ['nullable', 'string', 'max:50'],
            'metode_pembayaran' => ['required', Rule::in(['transfer', 'qris', 'cod'])],
        ], [
            'nama_penerima.required' => 'Nama penerima wajib diisi.',
            'no_hp.required' => 'Nomor HP wajib diisi.',
            'no_hp.regex' => 'Nomor HP harus berformat 08xxxxxxxxxx.',
            'alamat_pengiriman.required' => 'Alamat pengiriman wajib diisi.',
            'metode_pembayaran.required' => 'Metode pembayaran wajib dipilih.',
            'metode_pembayaran.in' => 'Metode pembayaran tidak valid.',
        ]);

        if ($request->filled('alamat_id')) {
            $alamat = Alamat::where('user_id', $request->user()->id)->find($data['alamat_id']);

            if (! $alamat) {
                return back()->withInput()->withErrors([
                    'alamat_id' => 'Alamat tersimpan tidak ditemukan. Pilih alamat milik Anda atau isi alamat baru.',
                ]);
            }

            $data['nama_penerima'] = $alamat->nama_penerima;
            $data['no_hp'] = $alamat->no_hp;
            $data['alamat_pengiriman'] = $alamat->alamat_pengiriman;
        }

        $checkoutLangsung = ($data['mode'] ?? 'keranjang') === 'langsung';
        $dataLangsung = $checkoutLangsung ? $this->checkoutLangsung($request, $data['token']) : null;

        if ($checkoutLangsung && ! $dataLangsung) {
            return redirect()->route('katalog')->with(
                'galat',
                'Sesi beli langsung tidak ditemukan. Silakan pilih produk kembali.',
            );
        }

        try {
            $pesanan = $this->checkoutService->checkout($request->user(), $data, $dataLangsung);
        } catch (StokTidakCukupException $e) {
            return back()->withInput()->with('galat', $e->getMessage());
        } catch (RuntimeException $e) {
            return back()->withInput()->with('galat', $e->getMessage());
        }

        if ($checkoutLangsung) {
            $request->session()->forget(self::SESSION_CHECKOUT_LANGSUNG);
        }

        return redirect()
            ->route('pesanan.show', $pesanan)
            ->with('sukses', 'Pesanan berhasil dibuat. Silakan lakukan pembayaran.');
    }

    /**
     * @return array{token:string,produk_id:int,produk_variasi_id:int|null,jumlah:int}|null
     */
    private function checkoutLangsung(Request $request, string $token): ?array
    {
        $data = $request->session()->get(self::SESSION_CHECKOUT_LANGSUNG);

        if (
            ! is_array($data)
            || ! isset($data['token'], $data['produk_id'], $data['jumlah'])
            || ! hash_equals((string) $data['token'], $token)
        ) {
            return null;
        }

        $produkId = filter_var($data['produk_id'], FILTER_VALIDATE_INT);
        $jumlah = filter_var($data['jumlah'], FILTER_VALIDATE_INT);
        $variasiId = isset($data['produk_variasi_id'])
            ? filter_var($data['produk_variasi_id'], FILTER_VALIDATE_INT)
            : null;

        if ($produkId === false || $jumlah === false || $jumlah < 1 || $variasiId === false) {
            return null;
        }

        return [
            'token' => $token,
            'produk_id' => $produkId,
            'produk_variasi_id' => $variasiId,
            'jumlah' => $jumlah,
        ];
    }
}
