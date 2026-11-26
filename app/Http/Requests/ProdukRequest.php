<?php

namespace App\Http\Requests;

use App\Models\DetailPesanan;
use App\Models\Keranjang;
use App\Models\Produk;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class ProdukRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $variasi = $this->input('variasi', []);
        $panduanUkuran = $this->input('panduan_ukuran', []);

        $this->merge([
            'variasi' => $variasi === null
                ? []
                : (is_array($variasi) ? $this->filterBlankRows($variasi, ['id', 'ukuran', 'warna', 'stok']) : $variasi),
            'panduan_ukuran' => $panduanUkuran === null
                ? []
                : (is_array($panduanUkuran) ? $this->filterBlankRows($panduanUkuran, ['ukuran', 'panduan']) : $panduanUkuran),
        ]);
    }

    public function authorize(): bool
    {
        return $this->user() !== null && $this->user()->isAdmin();
    }

    public function rules(): array
    {
        $produk = $this->route('produk');

        return [
            'nama' => ['required', 'string', 'max:150'],
            'kategori_id' => ['required', 'exists:kategori,id'],
            'deskripsi' => ['required', 'string'],
            'harga' => ['required', 'integer', 'min:0'],
            'stok' => ['required', 'integer', 'min:0'],
            'gambar' => ['nullable', 'array', 'max:10'],
            'gambar.*' => ['required', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
            'hapus_gambar' => ['sometimes', 'array', 'max:10'],
            'hapus_gambar.*' => ['required', 'string', 'max:255'],
            'variasi' => ['nullable', 'array', 'max:100'],
            'variasi.*' => ['array'],
            'variasi.*.id' => [
                'nullable',
                'integer',
                'distinct',
                $produk instanceof Produk
                    ? Rule::exists('produk_variasi', 'id')->where('produk_id', $produk->id)
                    : 'prohibited',
            ],
            'variasi.*.ukuran' => ['required', 'string', 'max:40'],
            'variasi.*.warna' => ['required', 'string', 'max:60'],
            'variasi.*.stok' => ['required', 'integer', 'min:0', 'max:4294967295'],
            'panduan_ukuran' => ['nullable', 'array', 'max:100'],
            'panduan_ukuran.*' => ['array'],
            'panduan_ukuran.*.ukuran' => ['required', 'string', 'max:40'],
            'panduan_ukuran.*.panduan' => ['required', 'string', 'max:250'],
            'aktif' => ['nullable', 'boolean'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $produk = $this->route('produk');

            if ($produk instanceof Produk) {
                $gambarSaatIni = $produk->gambarGaleris();
                $gambarDihapus = $this->input('hapus_gambar', []);
                $gambarDihapus = is_array($gambarDihapus)
                    ? array_values(array_unique(array_filter($gambarDihapus, 'is_string')))
                    : [];
                $gambarTidakDikenal = array_diff($gambarDihapus, $gambarSaatIni);

                if ($gambarTidakDikenal !== []) {
                    $validator->errors()->add('hapus_gambar', 'Pilih gambar yang tersedia pada produk ini.');

                    return;
                }

                $gambarBaru = $this->file('gambar', []);
                $gambarBaru = is_array($gambarBaru) ? $gambarBaru : [];
                $jumlahAkhir = count($gambarSaatIni) - count($gambarDihapus) + count($gambarBaru);

                if ($jumlahAkhir > 10) {
                    $validator->errors()->add('gambar', 'Maksimal 10 gambar untuk setiap produk.');
                }
            }

            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $variasi = $this->input('variasi', []);
            $pasangan = [];
            $totalStokVariasi = array_sum(array_column($variasi, 'stok'));

            if ($totalStokVariasi > 4294967295) {
                $validator->errors()->add('variasi', 'Total stok variasi melebihi batas yang didukung.');
            }

            foreach ($variasi as $baris) {
                $key = mb_strtolower(trim($baris['ukuran']) . '|' . trim($baris['warna']));

                if (isset($pasangan[$key])) {
                    $validator->errors()->add('variasi', 'Kombinasi ukuran dan warna tidak boleh duplikat.');
                    break;
                }

                $pasangan[$key] = true;
            }

            $ukuranPanduan = [];
            foreach ($this->input('panduan_ukuran', []) as $baris) {
                $key = mb_strtolower(trim($baris['ukuran']));

                if (isset($ukuranPanduan[$key])) {
                    $validator->errors()->add('panduan_ukuran', 'Ukuran pada tabel tidak boleh duplikat.');
                    break;
                }

                $ukuranPanduan[$key] = true;
            }

            $ukuranTersedia = array_unique(array_map(
                fn ($baris) => mb_strtolower(trim($baris['ukuran'])),
                $variasi,
            ));

            if (array_diff(array_keys($ukuranPanduan), $ukuranTersedia) !== []) {
                $validator->errors()->add('panduan_ukuran', 'Ukuran pada tabel harus tersedia sebagai variasi produk.');
            }

            if ($produk instanceof Produk) {
                if (
                    $variasi !== []
                    && ! $produk->variasi()->exists()
                    && Keranjang::where('produk_id', $produk->id)->whereNull('produk_variasi_id')->exists()
                ) {
                    $validator->errors()->add(
                        'variasi',
                        'Produk belum dapat memakai variasi karena masih ada item tanpa pilihan di keranjang pelanggan.',
                    );
                }

                $this->validasiPerubahanVariasi($validator, $produk, $variasi);
            }
        });
    }

    /**
     * @param  mixed  $rows
     * @param  list<string>  $keys
     * @return array<int, array<string, mixed>>
     */
    private function filterBlankRows(mixed $rows, array $keys): array
    {
        if (! is_array($rows)) {
            return [];
        }

        return array_values(array_filter($rows, function ($row) use ($keys) {
            if (! is_array($row)) {
                return true;
            }

            foreach ($keys as $key) {
                if (! array_key_exists($key, $row)) {
                    continue;
                }

                if (! is_scalar($row[$key]) && $row[$key] !== null) {
                    return true;
                }

                if (trim((string) $row[$key]) !== '') {
                    return true;
                }
            }

            return false;
        }));
    }

    /**
     * @param  array<int, array<string, mixed>>  $variasiBaru
     */
    private function validasiPerubahanVariasi(Validator $validator, Produk $produk, array $variasiBaru): void
    {
        $dikirim = collect($variasiBaru)->keyBy(fn ($baris) => (int) ($baris['id'] ?? 0));
        $variasiLama = $produk->variasi()->get();

        foreach ($variasiLama as $variasi) {
            $barisBaru = $dikirim->get($variasi->id);
            $dipakaiKeranjang = Keranjang::where('produk_variasi_id', $variasi->id)->exists();
            $pernahDipesan = DetailPesanan::where('produk_variasi_id', $variasi->id)->exists();

            if (($dipakaiKeranjang || $pernahDipesan) && (
                ! $barisBaru
                || trim((string) $barisBaru['ukuran']) !== $variasi->ukuran
                || trim((string) $barisBaru['warna']) !== $variasi->warna
            )) {
                $validator->errors()->add(
                    'variasi',
                    "Variasi {$variasi->ukuran} · {$variasi->warna} tidak dapat dihapus atau diubah karena sudah dipakai dalam keranjang atau pesanan.",
                );

                return;
            }
        }
    }

    public function messages(): array
    {
        return [
            'nama.required' => 'Nama produk wajib diisi.',
            'nama.max' => 'Nama produk maksimal 150 karakter.',
            'kategori_id.required' => 'Kategori wajib dipilih.',
            'kategori_id.exists' => 'Kategori tidak valid.',
            'deskripsi.required' => 'Deskripsi wajib diisi.',
            'harga.required' => 'Harga wajib diisi.',
            'harga.integer' => 'Harga harus berupa angka.',
            'harga.min' => 'Harga tidak boleh negatif.',
            'stok.required' => 'Stok wajib diisi.',
            'stok.integer' => 'Stok harus berupa angka.',
            'stok.min' => 'Stok tidak boleh negatif.',
            'gambar.array' => 'Pilih gambar produk dalam format yang valid.',
            'gambar.max' => 'Maksimal 10 gambar untuk setiap produk.',
            'gambar.*.image' => 'Setiap file harus berupa gambar.',
            'gambar.*.mimes' => 'Gambar harus berformat JPG atau PNG.',
            'gambar.*.max' => 'Ukuran setiap gambar maksimal 2 MB.',
            'hapus_gambar.array' => 'Daftar gambar yang dihapus tidak valid.',
            'hapus_gambar.max' => 'Maksimal 10 gambar dapat dihapus sekaligus.',
            'variasi.array' => 'Daftar variasi produk tidak valid.',
            'variasi.max' => 'Maksimal 100 kombinasi variasi untuk satu produk.',
            'variasi.*.array' => 'Data setiap variasi harus berupa baris yang valid.',
            'variasi.*.ukuran.required' => 'Ukuran wajib diisi untuk setiap variasi.',
            'variasi.*.ukuran.max' => 'Ukuran maksimal 40 karakter.',
            'variasi.*.warna.required' => 'Warna wajib diisi untuk setiap variasi.',
            'variasi.*.warna.max' => 'Warna maksimal 60 karakter.',
            'variasi.*.stok.required' => 'Stok wajib diisi untuk setiap variasi.',
            'variasi.*.stok.integer' => 'Stok variasi harus berupa angka.',
            'variasi.*.stok.min' => 'Stok variasi tidak boleh negatif.',
            'variasi.*.stok.max' => 'Stok variasi terlalu besar.',
            'panduan_ukuran.array' => 'Tabel ukuran tidak valid.',
            'panduan_ukuran.max' => 'Maksimal 100 baris pada tabel ukuran.',
            'panduan_ukuran.*.array' => 'Setiap baris tabel ukuran tidak valid.',
            'panduan_ukuran.*.ukuran.required' => 'Ukuran wajib diisi pada setiap baris tabel.',
            'panduan_ukuran.*.panduan.required' => 'Panduan ukuran wajib diisi pada setiap baris tabel.',
        ];
    }
}
