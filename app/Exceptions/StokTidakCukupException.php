<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Dilempar ketika stok produk tidak mencukupi saat checkout.
 * Ditangkap CheckoutController untuk menampilkan pesan ke pengguna.
 */
class StokTidakCukupException extends RuntimeException
{
    public static function untuk(string $namaProduk, int $diminta, int $tersedia): self
    {
        return new self(
            "Stok \"{$namaProduk}\" tidak cukup. Diminta {$diminta}, tersedia {$tersedia}."
        );
    }
}
