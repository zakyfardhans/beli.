<?php

namespace Database\Factories;

use App\Enums\StatusPesanan;
use App\Models\Pesanan;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Pesanan>
 */
class PesananFactory extends Factory
{
    protected $model = Pesanan::class;

    public function definition(): array
    {
        $subtotal = fake()->numberBetween(20000, 600000);
        $ongkir = $subtotal >= 500000 ? 0 : 15000;

        return [
            'kode_pesanan' => 'INV-' . now()->format('Ymd') . '-' . fake()->unique()->numerify('####'),
            'user_id' => function () {
                $u = User::factory()->create();
                $u->role = 'pelanggan';
                $u->save();

                return $u->id;
            },
            'nama_penerima' => fake()->name(),
            'no_hp' => '08' . fake()->numerify('##########'),
            'alamat_pengiriman' => fake()->address(),
            'subtotal' => $subtotal,
            'ongkir' => $ongkir,
            'total' => $subtotal + $ongkir,
            'status' => StatusPesanan::MenungguPembayaran,
            'bukti_bayar' => null,
            'no_resi' => null,
        ];
    }
}
