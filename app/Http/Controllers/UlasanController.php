<?php

namespace App\Http\Controllers;

use App\Enums\StatusPesanan;
use App\Models\Pesanan;
use App\Models\Produk;
use App\Models\UlasanProduk;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class UlasanController extends Controller
{
    public function store(Request $request, Produk $produk): RedirectResponse
    {
        $data = $request->validate([
            'pesanan_id' => ['required', 'exists:pesanan,id'],
            'rating' => ['required', 'integer', 'between:1,5'],
            'komentar' => ['nullable', 'string', 'max:1000'],
            'foto' => ['nullable', 'array', 'max:3'],
            'foto.*' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $pesanan = Pesanan::with('detailPesanan')
            ->whereKey($data['pesanan_id'])
            ->where('user_id', $request->user()->id)
            ->where('status', StatusPesanan::Selesai)
            ->firstOrFail();

        abort_unless($pesanan->detailPesanan->contains('produk_id', $produk->id), 403);

        $ulasan = UlasanProduk::firstOrNew([
            'user_id' => $request->user()->id,
            'produk_id' => $produk->id,
            'pesanan_id' => $pesanan->id,
        ]);
        $fotoLama = $ulasan->foto ?? [];
        $fotoBaru = [];

        foreach ($request->file('foto', []) ?? [] as $file) {
            $path = $file->store('ulasan/' . $produk->id, 'public');

            if (! $path) {
                if ($fotoBaru !== [] && ! Storage::disk('public')->delete($fotoBaru)) {
                    Log::warning('Gagal membersihkan foto ulasan setelah unggahan gagal.', [
                        'produk_id' => $produk->id,
                        'user_id' => $request->user()->id,
                    ]);
                }

                return back()->withInput()->withErrors([
                    'foto' => 'Foto ulasan gagal disimpan. Silakan coba unggah kembali.',
                ]);
            }

            $fotoBaru[] = $path;
        }

        $ulasan->rating = $data['rating'];
        $ulasan->komentar = $data['komentar'] ?? null;
        if ($fotoBaru !== []) {
            $ulasan->foto = $fotoBaru;
        }
        $ulasan->save();

        if ($fotoBaru !== [] && $fotoLama !== [] && ! Storage::disk('public')->delete($fotoLama)) {
            Log::warning('Foto lama ulasan gagal dihapus setelah ulasan diperbarui.', [
                'ulasan_id' => $ulasan->id,
            ]);
        }

        return back()->with('sukses', 'Ulasan berhasil disimpan.');
    }
}
