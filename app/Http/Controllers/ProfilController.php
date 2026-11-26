<?php

namespace App\Http\Controllers;

use App\Models\Alamat;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class ProfilController extends Controller
{
    public function index(): View
    {
        return view('profil.index', [
            'user' => auth()->user(),
            'alamat' => Alamat::where('user_id', auth()->id())->latest()->get(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'no_hp' => ['required', 'string', 'regex:/^08[0-9]{8,11}$/'],
            'alamat' => ['nullable', 'string', 'max:500'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        $user = $request->user();
        $user->name = $data['name'];
        $user->no_hp = $data['no_hp'];
        $user->alamat = $data['alamat'] ?? null;
        if (! empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }
        $user->save();

        return back()->with('sukses', 'Profil berhasil diperbarui.');
    }

    public function alamatStore(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'label' => ['required', 'string', 'max:50'],
            'nama_penerima' => ['required', 'string', 'max:150'],
            'no_hp' => ['required', 'string', 'regex:/^08[0-9]{8,11}$/'],
            'alamat_pengiriman' => ['required', 'string', 'max:500'],
            'utama' => ['nullable', 'boolean'],
        ]);

        DB::transaction(function () use ($data, $request) {
            User::whereKey($request->user()->id)->lockForUpdate()->firstOrFail();
            $data['user_id'] = $request->user()->id;
            $data['utama'] = $request->boolean('utama')
                || ! Alamat::where('user_id', $request->user()->id)->exists();

            if ($data['utama']) {
                Alamat::where('user_id', $request->user()->id)->update(['utama' => false]);
            }

            Alamat::create($data);
        });

        return back()->with('sukses', 'Alamat berhasil ditambahkan.');
    }

    public function alamatUpdate(Request $request, Alamat $alamat): RedirectResponse
    {
        abort_unless($alamat->user_id === $request->user()->id, 403);

        $data = $request->validate([
            'label' => ['required', 'string', 'max:50'],
            'nama_penerima' => ['required', 'string', 'max:150'],
            'no_hp' => ['required', 'string', 'regex:/^08[0-9]{8,11}$/'],
            'alamat_pengiriman' => ['required', 'string', 'max:500'],
            'utama' => ['nullable', 'boolean'],
        ]);

        DB::transaction(function () use ($alamat, $data, $request) {
            User::whereKey($request->user()->id)->lockForUpdate()->firstOrFail();
            $utama = $request->boolean('utama');

            if ($utama) {
                Alamat::where('user_id', $request->user()->id)
                    ->where('id', '!=', $alamat->id)
                    ->update(['utama' => false]);
            }

            $alamat->update([...$data, 'utama' => $utama || $alamat->utama]);
        });

        return back()->with('sukses', 'Alamat berhasil diperbarui.');
    }

    public function alamatDestroy(Request $request, Alamat $alamat): RedirectResponse
    {
        abort_unless($alamat->user_id === $request->user()->id, 403);

        DB::transaction(function () use ($alamat, $request) {
            User::whereKey($request->user()->id)->lockForUpdate()->firstOrFail();
            $utama = $alamat->utama;
            $alamat->delete();

            if ($utama) {
                $alamatBerikutnya = Alamat::where('user_id', $request->user()->id)
                    ->latest()
                    ->first();
                $alamatBerikutnya?->update(['utama' => true]);
            }
        });

        return back()->with('sukses', 'Alamat berhasil dihapus.');
    }
}
