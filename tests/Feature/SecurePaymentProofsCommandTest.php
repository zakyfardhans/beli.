<?php

namespace Tests\Feature;

use App\Models\Pesanan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SecurePaymentProofsCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_memindahkan_bukti_lama_dan_dapat_dijalankan_kembali(): void
    {
        Storage::fake('public');
        Storage::fake('local');
        $path = 'bukti-bayar/arsip.jpg';
        $orphan = 'bukti-bayar/tanpa-pesanan.jpg';
        Storage::disk('public')->put($path, 'legacy proof');
        Storage::disk('public')->put($orphan, 'orphan proof');
        Pesanan::factory()->create(['bukti_bayar' => $path]);

        $this->artisan('beli:secure-payment-proofs')
            ->expectsOutput('1 bukti pembayaran dipindahkan ke penyimpanan privat.')
            ->assertExitCode(0);

        Storage::disk('local')->assertExists($path);
        Storage::disk('public')->assertMissing($path);
        Storage::disk('public')->assertMissing($orphan);

        $this->artisan('beli:secure-payment-proofs')->assertExitCode(0);
    }
}
