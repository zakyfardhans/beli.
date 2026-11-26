<?php

namespace App\Enums;

/**
 * Status pesanan beserta aturan transisi yang valid.
 *
 * Alur utama:
 *   menunggu_pembayaran †’ menunggu_verifikasi †’ diproses †’ dikirim †’ selesai
 *
 * Pembatalan:
 *   - menunggu_pembayaran: pelanggan ATAU admin
 *   - menunggu_verifikasi / diproses: hanya admin
 *
 * Aturan transisi HANYA hidup di enum ini (bukan di controller), sesuai jobsheet.
 */
enum StatusPesanan: string
{
    case MenungguPembayaran = 'menunggu_pembayaran';
    case MenungguVerifikasi = 'menunggu_verifikasi';
    case Diproses = 'diproses';
    case Dikirim = 'dikirim';
    case Selesai = 'selesai';
    case Dibatalkan = 'dibatalkan';

    public function label(): string
    {
        return match ($this) {
            self::MenungguPembayaran => 'Menunggu Pembayaran',
            self::MenungguVerifikasi => 'Menunggu Verifikasi',
            self::Diproses => 'Diproses',
            self::Dikirim => 'Dikirim',
            self::Selesai => 'Selesai',
            self::Dibatalkan => 'Dibatalkan',
        };
    }

    /**
     * Transisi status yang valid secara umum (tanpa memandang aktor).
     * Pemeriksaan aktor (pelanggan vs admin) dilakukan di layer otorisasi.
     */
    public function bolehKe(self $tujuan): bool
    {
        return match ($this) {
            self::MenungguPembayaran => in_array($tujuan, [
                self::MenungguVerifikasi,
                self::Dibatalkan,
            ], true),
            self::MenungguVerifikasi => in_array($tujuan, [
                self::Diproses,
                self::Dibatalkan,
            ], true),
            self::Diproses => in_array($tujuan, [
                self::Dikirim,
                self::Dibatalkan,
            ], true),
            self::Dikirim => $tujuan === self::Selesai,
            self::Selesai, self::Dibatalkan => false,
        };
    }

    /**
     * Daftar status tujuan yang valid dari status ini (untuk render tombol aksi admin).
     *
     * @return array<int, self>
     */
    public function transisiValid(): array
    {
        return array_values(array_filter(
            self::cases(),
            fn(self $t) => $this->bolehKe($t),
        ));
    }

    /**
     * Apakah transisi ke tujuan khusus butuh resi (dikirim).
     */
    public function butuhResi(self $tujuan): bool
    {
        return $tujuan === self::Dikirim;
    }
}
