@php
    $editing = isset($produk);
    $gambarSaatIni = $editing ? $produk->gambarGaleris() : [];
    $hapusDipilih = old('hapus_gambar', []);
    $hapusDipilih = is_array($hapusDipilih) ? $hapusDipilih : [];
    $variasiForm = old('variasi', $editing ? $produk->variasi->map(fn ($item) => $item->only(['id', 'ukuran', 'warna', 'stok']))->all() : []);
    $variasiForm = is_array($variasiForm) ? $variasiForm : [];
    $variasiForm = $variasiForm !== [] ? $variasiForm : [[]];
    $panduanForm = old('panduan_ukuran', $editing ? ($produk->panduan_ukuran ?? []) : []);
    $panduanForm = is_array($panduanForm) ? $panduanForm : [];
    $panduanForm = $panduanForm !== [] ? $panduanForm : [[]];
@endphp

<div class="grid gap-4 md:grid-cols-2">
    <div class="md:col-span-2">
        <label for="nama" class="mb-1 block text-sm font-semibold">Nama Produk</label>
        <input id="nama" name="nama" type="text" value="{{ old('nama', $editing ? $produk->nama : '') }}"
            required
            class="w-full rounded-lg border border-line px-3.5 py-2.5 text-sm focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/20">
        @error('nama')
            <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="kategori_id" class="mb-1 block text-sm font-semibold">Kategori</label>
        <select id="kategori_id" name="kategori_id" required
            class="w-full rounded-lg border border-line px-3.5 py-2.5 text-sm focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/20">
            <option value="">Pilih Kategori</option>
            @foreach ($kategori as $kat)
                <option value="{{ $kat->id }}" @selected(old('kategori_id', $editing ? $produk->kategori_id : '') == $kat->id)>{{ $kat->nama }}</option>
            @endforeach
        </select>
        @error('kategori_id')
            <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="harga" class="mb-1 block text-sm font-semibold">Harga (Rp)</label>
        <input id="harga" name="harga" type="number" min="0"
            value="{{ old('harga', $editing ? $produk->harga : '') }}" required
            class="w-full rounded-lg border border-line px-3.5 py-2.5 text-sm focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/20">
        @error('harga')
            <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="stok" class="mb-1 block text-sm font-semibold">Stok</label>
        <input id="stok" name="stok" type="number" min="0"
            value="{{ old('stok', $editing ? $produk->stok : '') }}" required
            data-product-stock-input
            class="w-full rounded-lg border border-line px-3.5 py-2.5 text-sm focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/20">
        <p data-product-stock-help class="mt-1 text-xs text-muted">Jika variasi diisi, stok total dihitung dari stok setiap kombinasi.</p>
        @error('stok')
            <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
        @enderror
    </div>

    <fieldset class="md:col-span-2" data-product-variant-form>
        <legend class="text-sm font-semibold">Pilihan Ukuran dan Warna</legend>
        <p class="mt-1 text-xs leading-5 text-muted">
            Setiap kombinasi memiliki stok sendiri. Kombinasi yang sudah dipakai di keranjang atau pesanan tidak dapat dihapus atau diganti.
        </p>
        <div data-variant-rows class="mt-3 space-y-3">
            @foreach ($variasiForm as $indeks => $variasi)
                <div data-variant-row class="grid gap-3 rounded-lg border border-line p-3 sm:grid-cols-[1fr_1fr_8rem_auto] sm:items-end">
                    @if (isset($variasi['id']))
                        <input type="hidden" name="variasi[{{ $indeks }}][id]" value="{{ $variasi['id'] }}">
                    @endif
                    <div>
                        <label for="variasi-{{ $indeks }}-ukuran" class="mb-1 block text-xs font-semibold">Ukuran</label>
                        <input id="variasi-{{ $indeks }}-ukuran" name="variasi[{{ $indeks }}][ukuran]"
                            value="{{ $variasi['ukuran'] ?? '' }}" maxlength="40" data-variant-field="ukuran"
                            placeholder="Contoh: 36"
                            class="w-full rounded-lg border border-line px-3 py-2 text-sm focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/20">
                        @error("variasi.$indeks.ukuran")
                            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="variasi-{{ $indeks }}-warna" class="mb-1 block text-xs font-semibold">Warna</label>
                        <input id="variasi-{{ $indeks }}-warna" name="variasi[{{ $indeks }}][warna]"
                            value="{{ $variasi['warna'] ?? '' }}" maxlength="60" data-variant-field="warna"
                            placeholder="Contoh: Black Rainbow"
                            class="w-full rounded-lg border border-line px-3 py-2 text-sm focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/20">
                        @error("variasi.$indeks.warna")
                            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="variasi-{{ $indeks }}-stok" class="mb-1 block text-xs font-semibold">Stok</label>
                        <input id="variasi-{{ $indeks }}-stok" name="variasi[{{ $indeks }}][stok]" type="number"
                            min="0" value="{{ $variasi['stok'] ?? '' }}" data-variant-field="stok"
                            placeholder="0"
                            class="w-full rounded-lg border border-line px-3 py-2 text-sm focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/20">
                        @error("variasi.$indeks.stok")
                            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <button type="button" data-remove-variant
                        class="min-h-10 rounded-lg border border-line px-3 text-xs font-semibold text-ink hover:border-rose-300 hover:text-rose-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand">
                        Hapus
                    </button>
                </div>
            @endforeach
        </div>
        <button type="button" data-add-variant
            class="mt-3 min-h-10 rounded-lg border border-brand px-3 py-2 text-sm font-semibold text-brand hover:bg-brand-light focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand">
            Tambah kombinasi
        </button>
        <p data-variant-total aria-live="polite" class="mt-2 text-xs text-muted"></p>
        @error('variasi')
            <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
        @enderror
        <template data-variant-template>
            <div data-variant-row class="grid gap-3 rounded-lg border border-line p-3 sm:grid-cols-[1fr_1fr_8rem_auto] sm:items-end">
                <div>
                    <label for="variasi-__INDEX__-ukuran" class="mb-1 block text-xs font-semibold">Ukuran</label>
                    <input id="variasi-__INDEX__-ukuran" name="variasi[__INDEX__][ukuran]" maxlength="40"
                        data-variant-field="ukuran" placeholder="Contoh: 36"
                        class="w-full rounded-lg border border-line px-3 py-2 text-sm focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/20">
                </div>
                <div>
                    <label for="variasi-__INDEX__-warna" class="mb-1 block text-xs font-semibold">Warna</label>
                    <input id="variasi-__INDEX__-warna" name="variasi[__INDEX__][warna]" maxlength="60"
                        data-variant-field="warna" placeholder="Contoh: Black Rainbow"
                        class="w-full rounded-lg border border-line px-3 py-2 text-sm focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/20">
                </div>
                <div>
                    <label for="variasi-__INDEX__-stok" class="mb-1 block text-xs font-semibold">Stok</label>
                    <input id="variasi-__INDEX__-stok" name="variasi[__INDEX__][stok]" type="number" min="0"
                        data-variant-field="stok" placeholder="0"
                        class="w-full rounded-lg border border-line px-3 py-2 text-sm focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/20">
                </div>
                <button type="button" data-remove-variant
                    class="min-h-10 rounded-lg border border-line px-3 text-xs font-semibold text-ink hover:border-rose-300 hover:text-rose-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand">
                    Hapus
                </button>
            </div>
        </template>
    </fieldset>

    <fieldset class="md:col-span-2">
        <legend class="text-sm font-semibold">Tabel Ukuran</legend>
        <p class="mt-1 text-xs leading-5 text-muted">
            Isi keterangan ukuran untuk ditampilkan di halaman produk, misalnya panjang kaki 23 cm. Ukuran harus sama dengan variasi yang tersedia.
        </p>
        <div data-size-guide-rows class="mt-3 space-y-3">
            @foreach ($panduanForm as $indeks => $panduan)
                <div data-size-guide-row class="grid gap-3 rounded-lg border border-line p-3 sm:grid-cols-[10rem_1fr_auto] sm:items-end">
                    <div>
                        <label for="panduan-{{ $indeks }}-ukuran" class="mb-1 block text-xs font-semibold">Ukuran</label>
                        <input id="panduan-{{ $indeks }}-ukuran" name="panduan_ukuran[{{ $indeks }}][ukuran]"
                            value="{{ $panduan['ukuran'] ?? '' }}" maxlength="40"
                            class="w-full rounded-lg border border-line px-3 py-2 text-sm focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/20">
                        @error("panduan_ukuran.$indeks.ukuran")
                            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="panduan-{{ $indeks }}-detail" class="mb-1 block text-xs font-semibold">Panduan / ukuran (cm)</label>
                        <input id="panduan-{{ $indeks }}-detail" name="panduan_ukuran[{{ $indeks }}][panduan]"
                            value="{{ $panduan['panduan'] ?? '' }}" maxlength="250"
                            placeholder="Contoh: panjang kaki 23 cm"
                            class="w-full rounded-lg border border-line px-3 py-2 text-sm focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/20">
                        @error("panduan_ukuran.$indeks.panduan")
                            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <button type="button" data-remove-size-guide
                        class="min-h-10 rounded-lg border border-line px-3 text-xs font-semibold text-ink hover:border-rose-300 hover:text-rose-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand">
                        Hapus
                    </button>
                </div>
            @endforeach
        </div>
        <button type="button" data-add-size-guide
            class="mt-3 min-h-10 rounded-lg border border-brand px-3 py-2 text-sm font-semibold text-brand hover:bg-brand-light focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand">
            Tambah ukuran ke tabel
        </button>
        @error('panduan_ukuran')
            <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
        @enderror
        <template data-size-guide-template>
            <div data-size-guide-row class="grid gap-3 rounded-lg border border-line p-3 sm:grid-cols-[10rem_1fr_auto] sm:items-end">
                <div>
                    <label for="panduan-__INDEX__-ukuran" class="mb-1 block text-xs font-semibold">Ukuran</label>
                    <input id="panduan-__INDEX__-ukuran" name="panduan_ukuran[__INDEX__][ukuran]" maxlength="40"
                        class="w-full rounded-lg border border-line px-3 py-2 text-sm focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/20">
                </div>
                <div>
                    <label for="panduan-__INDEX__-detail" class="mb-1 block text-xs font-semibold">Panduan / ukuran (cm)</label>
                    <input id="panduan-__INDEX__-detail" name="panduan_ukuran[__INDEX__][panduan]" maxlength="250"
                        placeholder="Contoh: panjang kaki 23 cm"
                        class="w-full rounded-lg border border-line px-3 py-2 text-sm focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/20">
                </div>
                <button type="button" data-remove-size-guide
                    class="min-h-10 rounded-lg border border-line px-3 text-xs font-semibold text-ink hover:border-rose-300 hover:text-rose-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand">
                    Hapus
                </button>
            </div>
        </template>
    </fieldset>

    <div class="md:col-span-2">
        <label for="gambar" class="mb-1 block text-sm font-semibold">Galeri Gambar Produk</label>
        <p id="gambar-bantuan" class="text-xs leading-5 text-muted">
            Pilih JPG atau PNG, maksimal 2 MB per gambar dan 10 gambar total. Gambar baru ditambahkan setelah gambar saat ini.
        </p>

        @if ($gambarSaatIni !== [])
            <fieldset class="mt-3">
                <legend class="mb-2 text-xs font-semibold text-muted">Gambar saat ini ({{ count($gambarSaatIni) }})</legend>
                <div class="grid grid-cols-3 gap-3 sm:grid-cols-5">
                    @foreach ($gambarSaatIni as $gambar)
                        <div class="rounded-lg border border-line p-2">
                            <img src="{{ asset('storage/' . $gambar) }}"
                                alt="Gambar {{ $loop->iteration }} produk {{ $produk->nama }}"
                                class="aspect-square w-full rounded-md bg-canvas object-cover">
                            <label for="hapus-gambar-{{ $loop->index }}"
                                class="mt-2 flex min-h-8 cursor-pointer items-center gap-2 text-xs font-medium text-ink">
                                <input id="hapus-gambar-{{ $loop->index }}" type="checkbox" name="hapus_gambar[]"
                                    value="{{ $gambar }}" @checked(in_array($gambar, $hapusDipilih, true))
                                    class="rounded border-line text-brand focus:ring-brand">
                                Hapus gambar {{ $loop->iteration }}
                            </label>
                        </div>
                    @endforeach
                </div>
            </fieldset>
        @endif

        <input id="gambar" name="gambar[]" type="file" accept="image/png,image/jpeg" multiple
            data-gallery-uploader data-current-count="{{ count($gambarSaatIni) }}"
            aria-describedby="gambar-bantuan gambar-jumlah"
            class="mt-3 w-full rounded-lg border border-line px-3.5 py-2 text-sm file:mr-3 file:rounded-md file:border-0 file:bg-brand file:px-3 file:py-1.5 file:text-white">
        <p id="gambar-jumlah" data-gallery-upload-count aria-live="polite" class="mt-1 text-xs text-muted">
            Maksimal 10 gambar total.
        </p>
        @error('gambar')
            <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
        @enderror
        @for ($indeksGambar = 0; $indeksGambar <= 10; $indeksGambar++)
            @error("gambar.$indeksGambar")
                <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
            @enderror
        @endfor
        @error('hapus_gambar')
            <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="md:col-span-2">
        <label for="deskripsi" class="mb-1 block text-sm font-semibold">Deskripsi</label>
        <textarea id="deskripsi" name="deskripsi" rows="4" required
            class="w-full rounded-lg border border-line px-3.5 py-2.5 text-sm focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/20">{{ old('deskripsi', $editing ? $produk->deskripsi : '') }}</textarea>
        @error('deskripsi')
            <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="md:col-span-2">
        <label class="flex items-center gap-2 text-sm font-semibold">
            <input type="checkbox" name="aktif" value="1" @checked(old('aktif', $editing ? $produk->aktif : true))
                class="rounded border-line text-brand focus:ring-brand">
            Produk aktif (tampil di katalog)
        </label>
    </div>
</div>
