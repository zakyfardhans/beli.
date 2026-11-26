<?php

namespace App\Support;

/**
 * Format mata uang Rupiah satu kali untuk dipakai di seluruh aplikasi.
 */
class Rupiah
{
    public static function format(int|float|null $nilai): string
    {
        return 'Rp' . number_format((float) ($nilai ?? 0), 0, ',', '.');
    }
}
