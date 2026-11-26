<?php

namespace App\Http\Controllers\Admin;

use App\Enums\StatusPesanan;
use App\Http\Controllers\Controller;
use App\Models\Pesanan;
use App\Models\Produk;
use App\Models\ProdukVariasi;
use App\Models\AuditLog;
use App\Models\StokMutasi;
use App\Notifications\StatusPesananBerubah;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PesananController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->query('status');
        $q = trim((string) $request->query('q', ''));

        $pesanan = Pesanan::with('user')
            ->when($status && StatusPesanan::tryFrom($status), fn ($query) => $query->where('status', $status))
            ->when($q !== '', fn ($query) => $query->where('kode_pesanan', 'like', "%{$q}%"))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('admin.pesanan.index', [
            'pesanan' => $pesanan,
            'status' => $status,
            'q' => $q,
            'semuaStatus' => StatusPesanan::cases(),
        ]);
    }

    public function show(Pesanan $pesanan): View
    {
        $pesanan->load('detailPesanan', 'user');

        return view('admin.pesanan.show', [
            'pesanan' => $pesanan,
            'transisi' => $pesanan->status->transisiValid(),
        ]);
    }

    public function ubahStatus(Request $request, Pesanan $pesanan): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'string'],
            'no_resi' => ['nullable', 'string', 'max:50'],
        ], [
            'status.required' => 'Status tujuan wajib dipilih.',
        ]);

        $tujuan = StatusPesanan::tryFrom($data['status']);

        if (! $tujuan) {
            return back()->with('galat', 'Status tidak valid.');
        }

        $pesananTersimpan = DB::transaction(function () use ($pesanan, $tujuan, $data) {
            $terkunci = Pesanan::whereKey($pesanan->id)->lockForUpdate()->firstOrFail();

            if (! $terkunci->status->bolehKe($tujuan)) {
                return null;
            }

            if ($terkunci->status->butuhResi($tujuan) && empty($data['no_resi'])) {
                throw ValidationException::withMessages([
                    'no_resi' => 'Nomor resi wajib diisi saat status menjadi Dikirim.',
                ]);
            }

            $statusLama = $terkunci->status;

            // Kembalikan stok bila dibatalkan.
            if ($tujuan === StatusPesanan::Dibatalkan) {
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
                        'jenis' => 'pembatalan-admin',
                        'perubahan' => $detail->jumlah,
                        'stok_sesudah' => $produk->fresh()->stok,
                        'keterangan' => 'Pengembalian stok oleh admin',
                        'user_id' => auth()->id(),
                    ]);
                }
            }

            $terkunci->status = $tujuan;
            if ($tujuan === StatusPesanan::Dikirim && ! empty($data['no_resi'])) {
                $terkunci->no_resi = $data['no_resi'];
            }
            $terkunci->save();
            AuditLog::create([
                'user_id' => auth()->id(),
                'aksi' => 'ubah_status',
                'entitas' => 'pesanan',
                'entitas_id' => $terkunci->id,
                'nilai_lama' => $statusLama->value,
                'nilai_baru' => $tujuan->value,
                'ip' => request()->ip(),
            ]);

            return $terkunci->load('user');
        });

        if (! $pesananTersimpan) {
            return back()->with('galat', 'Status pesanan sudah berubah atau transisi tidak lagi diizinkan.');
        }

        $pesananTersimpan->user->notify(new StatusPesananBerubah($pesananTersimpan));

        return back()->with('sukses', "Status pesanan diubah menjadi {$tujuan->label()}.");
    }
}
