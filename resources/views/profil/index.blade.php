@extends('layouts.app')

@section('judul', 'Profil')

@section('konten')
    <h1 class="mb-6 text-2xl font-bold">Profil Pelanggan</h1>
    <div class="grid gap-6 lg:grid-cols-2">
        <form action="{{ route('profil.update') }}" method="POST"
            class="h-fit space-y-4 rounded-lg border border-line bg-white p-5">
            @csrf
            @method('PATCH')
            <label class="block text-sm font-semibold">Nama
                <input name="name" value="{{ old('name', $user->name) }}" required
                    class="mt-1 w-full rounded-lg border border-line px-3 py-2.5">
                @error('name')<span class="mt-1 block text-sm text-rose-700">{{ $message }}</span>@enderror
            </label>
            <label class="block text-sm font-semibold">Nomor HP
                <input name="no_hp" value="{{ old('no_hp', $user->no_hp) }}" required
                    class="mt-1 w-full rounded-lg border border-line px-3 py-2.5">
                @error('no_hp')<span class="mt-1 block text-sm text-rose-700">{{ $message }}</span>@enderror
            </label>
            <label class="block text-sm font-semibold">Alamat profil
                <textarea name="alamat" rows="3" class="mt-1 w-full rounded-lg border border-line px-3 py-2.5">{{ old('alamat', $user->alamat) }}</textarea>
                @error('alamat')<span class="mt-1 block text-sm text-rose-700">{{ $message }}</span>@enderror
            </label>
            <label class="block text-sm font-semibold">Password baru (opsional)
                <input name="password" type="password" autocomplete="new-password"
                    class="mt-1 w-full rounded-lg border border-line px-3 py-2.5">
                @error('password')<span class="mt-1 block text-sm text-rose-700">{{ $message }}</span>@enderror
            </label>
            <label class="block text-sm font-semibold">Konfirmasi password
                <input name="password_confirmation" type="password" autocomplete="new-password"
                    class="mt-1 w-full rounded-lg border border-line px-3 py-2.5">
            </label>
            <button type="submit"
                class="min-h-11 rounded-lg bg-brand px-4 py-2 text-sm font-semibold text-white hover:bg-brand-dark">
                Simpan Profil
            </button>
        </form>

        <div class="rounded-lg border border-line bg-white p-5">
            <h2 class="mb-1 text-lg font-bold">Alamat Tersimpan</h2>
            <p class="mb-4 text-sm text-muted">Pilih alamat utama untuk mengisi checkout lebih cepat.</p>

            @forelse ($alamat as $item)
                <article class="mb-4 rounded-lg border border-line p-4">
                    <form action="{{ route('profil.alamat.update', $item) }}" method="POST" class="space-y-3">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="alamat_id" value="{{ $item->id }}">
                        @php($formAlamatIni = (int) old('alamat_id') === $item->id)
                        <div class="grid gap-3 sm:grid-cols-2">
                            <label class="block text-xs font-semibold text-muted">Label alamat
                                <input name="label" value="{{ $formAlamatIni ? old('label', $item->label) : $item->label }}" required maxlength="50"
                                    class="mt-1 w-full rounded-lg border border-line px-3 py-2 text-sm text-ink">
                                @if ($formAlamatIni)
                                    @error('label')<span class="mt-1 block text-sm text-rose-700">{{ $message }}</span>@enderror
                                @endif
                            </label>
                            <label class="block text-xs font-semibold text-muted">Nama penerima
                                <input name="nama_penerima" value="{{ $formAlamatIni ? old('nama_penerima', $item->nama_penerima) : $item->nama_penerima }}" required maxlength="150"
                                    class="mt-1 w-full rounded-lg border border-line px-3 py-2 text-sm text-ink">
                                @if ($formAlamatIni)
                                    @error('nama_penerima')<span class="mt-1 block text-sm text-rose-700">{{ $message }}</span>@enderror
                                @endif
                            </label>
                        </div>
                        <label class="block text-xs font-semibold text-muted">Nomor HP
                            <input name="no_hp" value="{{ $formAlamatIni ? old('no_hp', $item->no_hp) : $item->no_hp }}" required
                                class="mt-1 w-full rounded-lg border border-line px-3 py-2 text-sm text-ink">
                            @if ($formAlamatIni)
                                @error('no_hp')<span class="mt-1 block text-sm text-rose-700">{{ $message }}</span>@enderror
                            @endif
                        </label>
                        <label class="block text-xs font-semibold text-muted">Alamat lengkap
                            <textarea name="alamat_pengiriman" rows="2" required maxlength="500"
                                class="mt-1 w-full rounded-lg border border-line px-3 py-2 text-sm text-ink">{{ $formAlamatIni ? old('alamat_pengiriman', $item->alamat_pengiriman) : $item->alamat_pengiriman }}</textarea>
                            @if ($formAlamatIni)
                                @error('alamat_pengiriman')<span class="mt-1 block text-sm text-rose-700">{{ $message }}</span>@enderror
                            @endif
                        </label>
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <label class="flex min-h-11 items-center gap-2 text-sm font-semibold text-ink">
                                <input type="checkbox" name="utama" value="1" @checked($item->utama)
                                    class="rounded border-line text-brand focus:ring-brand">
                                Alamat utama
                            </label>
                            <div class="flex items-center gap-3">
                                <button type="submit" class="min-h-11 px-2 text-sm font-semibold text-brand hover:underline">
                                    Simpan
                                </button>
                                <button type="submit" form="hapus-alamat-{{ $item->id }}"
                                    class="min-h-11 px-2 text-sm font-semibold text-rose-700 hover:underline">
                                    Hapus
                                </button>
                            </div>
                        </div>
                    </form>
                    <form id="hapus-alamat-{{ $item->id }}" action="{{ route('profil.alamat.destroy', $item) }}"
                        method="POST" onsubmit="return confirm('Hapus alamat tersimpan ini?')">
                        @csrf
                        @method('DELETE')
                    </form>
                </article>
            @empty
                <p class="rounded-lg bg-canvas p-4 text-sm text-muted">Belum ada alamat tersimpan. Alamat pertama akan otomatis menjadi alamat utama.</p>
            @endforelse

            <form action="{{ route('profil.alamat.store') }}" method="POST" class="mt-5 space-y-3 border-t border-line pt-5">
                @csrf
                <h3 class="font-semibold">Tambah alamat</h3>
                <label class="block text-sm font-semibold">Label
                    <input name="label" value="{{ old('label') }}" placeholder="Rumah, Kantor, ..." required maxlength="50"
                        class="mt-1 w-full rounded-lg border border-line px-3 py-2.5 text-sm">
                    @error('label')<span class="mt-1 block text-sm text-rose-700">{{ $message }}</span>@enderror
                </label>
                <label class="block text-sm font-semibold">Nama penerima
                    <input name="nama_penerima" value="{{ old('nama_penerima') }}" required maxlength="150"
                        class="mt-1 w-full rounded-lg border border-line px-3 py-2.5 text-sm">
                    @error('nama_penerima')<span class="mt-1 block text-sm text-rose-700">{{ $message }}</span>@enderror
                </label>
                <label class="block text-sm font-semibold">Nomor HP
                    <input name="no_hp" value="{{ old('no_hp') }}" required
                        class="mt-1 w-full rounded-lg border border-line px-3 py-2.5 text-sm">
                    @error('no_hp')<span class="mt-1 block text-sm text-rose-700">{{ $message }}</span>@enderror
                </label>
                <label class="block text-sm font-semibold">Alamat lengkap
                    <textarea name="alamat_pengiriman" rows="3" required maxlength="500"
                        class="mt-1 w-full rounded-lg border border-line px-3 py-2.5 text-sm">{{ old('alamat_pengiriman') }}</textarea>
                    @error('alamat_pengiriman')<span class="mt-1 block text-sm text-rose-700">{{ $message }}</span>@enderror
                </label>
                <label class="flex min-h-11 items-center gap-2 text-sm">
                    <input type="checkbox" name="utama" value="1" @checked(old('utama'))
                        class="rounded border-line text-brand focus:ring-brand">
                    Jadikan alamat utama
                </label>
                <button type="submit"
                    class="min-h-11 rounded-lg bg-brand px-4 py-2 text-sm font-semibold text-white hover:bg-brand-dark">
                    Tambah Alamat
                </button>
            </form>
        </div>
    </div>
@endsection
