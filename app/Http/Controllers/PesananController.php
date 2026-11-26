<?php

namespace App\Http\Controllers;

use App\Enums\StatusPesanan;
use App\Models\Pesanan;
use App\Models\Produk;
use App\Models\ProdukVariasi;
use App\Models\StokMutasi;
use App\Models\AuditLog;
use App\Notifications\StatusPesananBerubah;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class PesananController extends Controller
{
    public function index(): View
    {
        $pesanan = Pesanan::with(['detailPesanan.produk'])
            ->where('user_id', auth()->id())
            ->latest()
            ->paginate(10);

        return view('pesanan.index', compact('pesanan'));
    }

    public function show(Pesanan $pesanan): View
    {
        $this->authorize('view', $pesanan);

        $pesanan->load('detailPesanan.produk');

        return view('pesanan.show', compact('pesanan'));
    }

    public function bayar(Request $request, Pesanan $pesanan): RedirectResponse
    {
        $this->authorize('bayar', $pesanan);

        $request->validate([
            'bukti_bayar' => ['required', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
        ], [
            'bukti_bayar.required' => 'Bukti bayar wajib diunggah.',
            'bukti_bayar.image' => 'File harus berupa gambar.',
            'bukti_bayar.mimes' => 'Bukti bayar harus berformat JPG atau PNG.',
            'bukti_bayar.max' => 'Ukuran bukti bayar maksimal 2 MB.',
        ]);

        $pathLama = $pesanan->bukti_bayar;
        $pathBaru = $request->file('bukti_bayar')->store('bukti-bayar', 'local');

        $pesananTersimpan = DB::transaction(function () use ($pesanan, $pathBaru) {
            $terkunci = Pesanan::whereKey($pesanan->id)->lockForUpdate()->firstOrFail();

            if ($terkunci->status !== StatusPesanan::MenungguPembayaran) {
                return null;
            }

            $terkunci->update([
                'bukti_bayar' => $pathBaru,
                'status' => StatusPesanan::MenungguVerifikasi,
            ]);

            return $terkunci->load('user');
        });

        if (! $pesananTersimpan) {
            Storage::disk('local')->delete($pathBaru);

            return back()->with('galat', 'Bukti bayar hanya dapat diunggah saat menunggu pembayaran.');
        }

        if ($pathLama && Storage::disk('local')->exists($pathLama)) {
            Storage::disk('local')->delete($pathLama);
        }

        $pesananTersimpan->user->notify(new StatusPesananBerubah($pesananTersimpan));

        return back()->with('sukses', 'Bukti bayar berhasil diunggah. Menunggu verifikasi admin.');
    }

    public function buktiBayar(Pesanan $pesanan): BinaryFileResponse
    {
        $this->authorize('view', $pesanan);

        abort_unless(
            $pesanan->bukti_bayar && Storage::disk('local')->exists($pesanan->bukti_bayar),
            404
        );

        $response = response()->file(Storage::disk('local')->path($pesanan->bukti_bayar), [
            'Content-Type' => Storage::disk('local')->mimeType($pesanan->bukti_bayar),
        ]);
        $response->setPrivate();
        $response->headers->addCacheControlDirective('no-store');

        return $response;
    }

    public function batal(Pesanan $pesanan): RedirectResponse
    {
        $this->authorize('batal', $pesanan);

        $berhasil = DB::transaction(function () use ($pesanan) {
            $terkunci = Pesanan::whereKey($pesanan->id)->lockForUpdate()->firstOrFail();

            if (! in_array($terkunci->status, [StatusPesanan::MenungguPembayaran, StatusPesanan::MenungguVerifikasi], true)) {
                return false;
            }

            $terkunci->load('detailPesanan');

            foreach ($terkunci->detailPesanan as $detail) {
                $produk = Produk::whereKey($detail->produk_id)->lockForUpdate()->firstOrFail();

                if ($detail->produk_variasi_id) {
                    $variasi = ProdukVariasi::whereKey($detail->produk_variasi_id)
                        ->where('produk_id', $produk->id)
                        ->lockForUpdate()
                        ->firstOrFail();
                    $variasi->increment('stok', $detail->jumlah);
                }

                $produk->increment('stok', $detail->jumlah);
                StokMutasi::create([
                    'produk_id' => $produk->id,
                    'jenis' => 'pembatalan',
                    'perubahan' => $detail->jumlah,
                    'stok_sesudah' => $produk->fresh()->stok,
                    'keterangan' => 'Pengembalian stok saat pembatalan pesanan',
                    'user_id' => auth()->id(),
                ]);
            }

            $terkunci->update(['status' => StatusPesanan::Dibatalkan]);

            return true;
        });

        if (! $berhasil) {
            return back()->with('galat', 'Pesanan ini tidak dapat dibatalkan.');
        }

        return back()->with('sukses', 'Pesanan dibatalkan dan stok dikembalikan.');
    }

    public function terima(Pesanan $pesanan): RedirectResponse
    {
        $this->authorize('terima', $pesanan);

        $berhasil = DB::transaction(function () use ($pesanan) {
            $terkunci = Pesanan::whereKey($pesanan->id)->lockForUpdate()->firstOrFail();

            if (! $terkunci->status->bolehKe(StatusPesanan::Selesai)) {
                return false;
            }

            $terkunci->update(['status' => StatusPesanan::Selesai]);

            return true;
        });

        if (! $berhasil) {
            return back()->with('galat', 'Pesanan belum dapat dikonfirmasi diterima.');
        }

        return back()->with('sukses', 'Pesanan dikonfirmasi telah diterima.');
    }

    public function status(Pesanan $pesanan): \Illuminate\Http\JsonResponse
    {
        $this->authorize('view', $pesanan);

        return response()->json([
            'status' => $pesanan->status->value,
            'status_label' => $pesanan->status->label(),
        ]);
    }

    public function showQris(Pesanan $pesanan): View|RedirectResponse
    {
        $this->authorize('view', $pesanan);

        if ($pesanan->status !== StatusPesanan::MenungguPembayaran) {
            return redirect()->route('pesanan.show', $pesanan)
                ->with('galat', 'Pembayaran QRIS hanya tersedia untuk pesanan dengan status menunggu pembayaran.');
        }

        if (! $pesanan->qr_token) {
            $pesanan->update(['qr_token' => Str::random(64)]);
            $pesanan->refresh();
        }

        $urlQris = route('pesanan.qris.scan', [
            'pesanan' => $pesanan->kode_pesanan,
            'token' => $pesanan->qr_token,
        ]);

        $renderer = new ImageRenderer(
            new RendererStyle(300),
            new \BaconQrCode\Renderer\Image\SvgImageBackEnd()
        );
        $writer = new Writer($renderer);
        $qrCodeSvg = $writer->writeString($urlQris);

        return view('pesanan.qris', compact('pesanan', 'qrCodeSvg', 'urlQris'));
    }

    public function scanQris(Pesanan $pesanan, string $token): View|RedirectResponse
    {
        if (! $pesanan->qr_token || ! hash_equals($pesanan->qr_token, $token)) {
            abort(403, 'Token QR tidak valid atau sudah kedaluwarsa.');
        }

        if (auth()->id() !== $pesanan->user_id) {
            abort(403, 'Anda tidak memiliki akses ke pesanan ini.');
        }

        if ($pesanan->status !== StatusPesanan::MenungguPembayaran) {
            return redirect()->route('pesanan.show', $pesanan)
                ->with('galat', 'Pesanan ini sudah tidak dalam status menunggu pembayaran.');
        }

        return view('pesanan.qris-scan', compact('pesanan'));
    }

    public function konfirmasiQris(Request $request, Pesanan $pesanan, string $token): RedirectResponse
    {
        if (! $pesanan->qr_token || ! hash_equals($pesanan->qr_token, $token)) {
            abort(403, 'Token QR tidak valid atau sudah kedaluwarsa.');
        }

        if (auth()->id() !== $pesanan->user_id) {
            abort(403, 'Anda tidak memiliki akses ke pesanan ini.');
        }

        $berhasil = DB::transaction(function () use ($pesanan) {
            $terkunci = Pesanan::whereKey($pesanan->id)->lockForUpdate()->firstOrFail();

            if ($terkunci->status !== StatusPesanan::MenungguPembayaran) {
                return false;
            }

            $terkunci->update([
                'status' => StatusPesanan::MenungguVerifikasi,
            ]);

            return true;
        });

        if (! $berhasil) {
            return back()->with('galat', 'Pesanan tidak dapat dikonfirmasi. Status sudah berubah.');
        }

        return redirect()->route('pesanan.show', $pesanan)
            ->with('sukses', 'Pembayaran berhasil dikonfirmasi. Menunggu verifikasi admin.');
    }
}
