<?php

namespace App\Providers;

use App\Models\Keranjang;
use App\Models\Pesanan;
use App\Policies\PesananPolicy;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Gate::policy(Pesanan::class, PesananPolicy::class);

        $this->konfigurasiRateLimiter();

        // Bagikan jumlah item keranjang ke semua view (badge navbar).
        View::composer('*', function ($view) {
            $jumlahKeranjang = 0;

            if (auth()->check() && auth()->user()->isPelanggan()) {
                $jumlahKeranjang = (int) Keranjang::where('user_id', auth()->id())->sum('jumlah');
            }

            $view->with('jumlahKeranjang', $jumlahKeranjang);
        });
    }

    private function konfigurasiRateLimiter(): void
    {
        // Login: 5 percobaan per menit per (email + IP).
        RateLimiter::for('login', function (Request $request) {
            $email = Str::lower((string) $request->input('email'));

            return Limit::perMinute(5)->by($email . '|' . $request->ip());
        });

        // Registrasi: 10 percobaan per menit per IP.
        RateLimiter::for('daftar', function (Request $request) {
            return Limit::perMinute(10)->by($request->ip());
        });
    }
}
