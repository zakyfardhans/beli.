<?php

namespace App\Policies;

use App\Models\Pesanan;
use App\Models\User;

/**
 * Otorisasi kepemilikan pesanan.
 * Pelanggan hanya boleh melihat/mengelola pesanannya sendiri.
 * Admin boleh melihat semua.
 */
class PesananPolicy
{
    /**
     * Admin bypass semua pengecekan (lihat AuthServiceProvider / Gate::before).
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return null;
    }

    public function view(User $user, Pesanan $pesanan): bool
    {
        return $user->id === $pesanan->user_id;
    }

    public function bayar(User $user, Pesanan $pesanan): bool
    {
        return $user->id === $pesanan->user_id;
    }

    public function batal(User $user, Pesanan $pesanan): bool
    {
        return $user->id === $pesanan->user_id;
    }

    public function terima(User $user, Pesanan $pesanan): bool
    {
        return $user->id === $pesanan->user_id;
    }
}
