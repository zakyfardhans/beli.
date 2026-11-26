<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kupon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class KuponController extends Controller
{
    public function index(): View
    {
        return view('admin.kupon.index', [
            'kupon' => Kupon::latest()->paginate(15),
        ]);
    }

    public function create(): View
    {
        return view('admin.kupon.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validasiKupon($request);
        $data['aktif'] = $request->boolean('aktif');
        $data['terpakai'] = 0;
        Kupon::create($data);

        return redirect()->route('admin.kupon.index')->with('sukses', 'Kupon berhasil ditambahkan.');
    }

    public function edit(Kupon $kupon): View
    {
        return view('admin.kupon.edit', compact('kupon'));
    }

    public function update(Request $request, Kupon $kupon): RedirectResponse
    {
        DB::transaction(function () use ($request, $kupon) {
            $terkunci = Kupon::whereKey($kupon->id)->lockForUpdate()->firstOrFail();
            $data = $this->validasiKupon($request, $terkunci);
            $data['aktif'] = $request->boolean('aktif');
            $terkunci->update($data);
        });

        return redirect()->route('admin.kupon.index')->with('sukses', 'Kupon berhasil diperbarui.');
    }

    public function destroy(Kupon $kupon): RedirectResponse
    {
        $dapatDihapus = DB::transaction(function () use ($kupon) {
            $terkunci = Kupon::whereKey($kupon->id)->lockForUpdate()->firstOrFail();

            if ($terkunci->terpakai > 0) {
                return false;
            }

            $terkunci->delete();

            return true;
        });

        if (! $dapatDihapus) {
            return back()->with('galat', 'Kupon yang pernah digunakan tidak dapat dihapus. Nonaktifkan kupon saja.');
        }

        return redirect()->route('admin.kupon.index')->with('sukses', 'Kupon berhasil dihapus.');
    }

    private function validasiKupon(Request $request, ?Kupon $kupon = null): array
    {
        $request->merge([
            'kode' => strtoupper(trim((string) $request->input('kode'))),
        ]);

        $minimumKuota = max(1, $kupon?->terpakai ?? 0);

        return $request->validate([
            'kode' => [
                'required',
                'string',
                'max:50',
                'regex:/^[A-Z0-9_-]+$/',
                Rule::unique('kupons', 'kode')->ignore($kupon?->id),
            ],
            'persentase' => ['required', 'integer', 'between:1,100'],
            'kuota' => ['required', 'integer', "min:{$minimumKuota}"],
            'kedaluwarsa' => $kupon
                ? ['required', 'date']
                : ['required', 'date', 'after:now'],
            'aktif' => ['nullable', 'boolean'],
        ], [
            'kode.required' => 'Kode kupon wajib diisi.',
            'kode.regex' => 'Kode hanya boleh berisi huruf, angka, tanda hubung, dan garis bawah.',
            'kode.unique' => 'Kode kupon sudah digunakan.',
            'persentase.between' => 'Diskon harus antara 1 dan 100 persen.',
            'kuota.min' => 'Kuota tidak boleh lebih kecil dari jumlah penggunaan.',
            'kedaluwarsa.after' => 'Waktu kedaluwarsa harus berada di masa depan.',
        ]);
    }
}
