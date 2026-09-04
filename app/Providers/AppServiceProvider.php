<?php

namespace App\Providers;

use Carbon\CarbonImmutable;
use Illuminate\Auth\Events\Login;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;
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

        // Tanggal yang tidak bisa berubah diam-diam.
        //
        // Cast bawaan mengembalikan Illuminate\Support\Carbon yang MUTABLE:
        // `$jadwal->tanggal->addDay()` mengubah nilainya di tempat, bukan
        // menghasilkan salinan. Model di proyek ini sudah menyatakan dirinya
        // CarbonImmutable pada docblock, dan App\Casts\JamHarian memang
        // mengembalikan CarbonImmutable -- jadi yang tertulis sudah menjadi
        // maksudnya sejak awal, hanya belum pernah ditegakkan.
        //
        // Setelah baris ini, keduanya sungguh sama. Yang salah tulis akan
        // gagal dengan jelas (nilai tidak berubah) alih-alih merusak data
        // di kejauhan.
        Date::use(CarbonImmutable::class);

        // Donasi dan formulir permohonan harus lewat HTTPS di produksi; tanpa ini
        // aset dan URL absolut bisa jatuh ke http di balik proxy/load balancer.
        if ($this->app->isProduction()) {
            URL::forceScheme('https');
        }

        // Setiap login baru harus melewati 2FA lagi.
        //
        // PastikanTwoFactorTerverifikasi menilai dari satu kunci sesi, dan kunci
        // itu bertahan selama sesinya bertahan. Filament memang meng-invalidate
        // sesi saat logout, tetapi itu berarti keamanan gerbang 2FA bergantung
        // pada perilaku satu jalur keluar tertentu — jalur lain (sesi yang
        // dipakai ulang, logout yang hanya melepas pengguna tanpa membuang
        // sesinya) meninggalkan tanda "sudah terverifikasi" untuk login
        // berikutnya. Membatalkannya pada peristiwa Login mengikat verifikasi
        // ke sesi masuk yang bersangkutan, bukan ke cara seseorang keluar.
        Event::listen(Login::class, fn () => session()->forget('two_factor_verified_user_id'));
    }
}
