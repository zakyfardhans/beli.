<?php

namespace Tests\Feature;

use App\Models\Produk;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /** Smoke test: katalog publik dapat diakses. */
    public function test_katalog_publik_dapat_diakses(): void
    {
        Produk::factory()->create(['aktif' => true]);

        $this->get('/')->assertOk();
    }
}
