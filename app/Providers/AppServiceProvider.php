<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Di luar produksi: lazy loading, atribut yang tidak ada, dan pengisian
        // massal yang tidak diizinkan langsung melempar exception, bukan diam-diam
        // menghasilkan halaman yang salah.
        Model::shouldBeStrict(! $this->app->isProduction());

        // Donasi dan formulir permohonan harus lewat HTTPS di produksi; tanpa ini
        // aset dan URL absolut bisa jatuh ke http di balik proxy/load balancer.
        if ($this->app->isProduction()) {
            URL::forceScheme('https');
        }

        // Prefetch chunk Vite (swiper/chart/lucide) setelah halaman selesai dimuat.
        Vite::prefetch(concurrency: 3);
    }
}
