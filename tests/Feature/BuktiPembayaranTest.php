<?php

namespace Tests\Feature;

use App\Models\Pesanan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BuktiPembayaranTest extends TestCase
{
    use RefreshDatabase;

    public function test_bukti_bayar_disimpan_di_disk_privasi_dan_dilayani_hanya_untuk_pihak_berwenang(): void
    {
        Storage::fake('local');
        Storage::fake('public');
        Notification::fake();

        $user = User::factory()->pelanggan()->create();
        $pesanan = Pesanan::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)
            ->post(route('pesanan.bayar', $pesanan), [
                'bukti_bayar' => UploadedFile::fake()->createWithContent(
                    'bukti.png',
                    base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAIAAACQd1PeAAAADUlEQVR4nGP4z8AAAAMBAQDJ/pLvAAAAAElFTkSuQmCC')
                ),
            ])
            ->assertSessionHas('sukses');

        $pesanan->refresh();
        Storage::disk('local')->assertExists($pesanan->bukti_bayar);
        Storage::disk('public')->assertMissing($pesanan->bukti_bayar);

        $response = $this->actingAs($user)->get(route('pesanan.bukti', $pesanan));
        $response->assertOk();
        $this->assertStringContainsString('private', $response->headers->get('Cache-Control'));
        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));

        $orangLain = User::factory()->pelanggan()->create();
        $this->actingAs($orangLain)
            ->get(route('pesanan.bukti', $pesanan))
            ->assertForbidden();
    }
}
