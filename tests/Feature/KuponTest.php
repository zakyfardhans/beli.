<?php

namespace Tests\Feature;

use App\Models\Kupon;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KuponTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_dapat_membuat_kupon_dengan_kode_normalisasi(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->post(route('admin.kupon.store'), [
                'kode' => ' hemat-10 ',
                'persentase' => 10,
                'kuota' => 25,
                'kedaluwarsa' => now()->addDays(10)->format('Y-m-d H:i:s'),
                'aktif' => 1,
            ])
            ->assertRedirect(route('admin.kupon.index'));

        $this->assertDatabaseHas('kupons', [
            'kode' => 'HEMAT-10',
            'persentase' => 10,
            'kuota' => 25,
            'terpakai' => 0,
            'aktif' => true,
        ]);
    }

    public function test_admin_tidak_dapat_menghapus_kupon_yang_sudah_digunakan(): void
    {
        $admin = User::factory()->admin()->create();
        $kupon = Kupon::create([
            'kode' => 'SUDAHPAKAI',
            'persentase' => 10,
            'kuota' => 10,
            'terpakai' => 1,
            'kedaluwarsa' => now()->addDays(2),
            'aktif' => true,
        ]);

        $this->actingAs($admin)
            ->delete(route('admin.kupon.destroy', $kupon))
            ->assertSessionHas('galat');

        $this->assertDatabaseHas('kupons', ['id' => $kupon->id]);
    }
}
