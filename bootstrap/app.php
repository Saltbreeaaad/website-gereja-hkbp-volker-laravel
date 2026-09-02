<?php

use App\Http\Middleware\SecurityHeaders;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Hanya grup `web`: panel Filament merakit daftar middleware-nya sendiri
        // di AdminPanelProvider, jadi CSP ketat ini tidak menyentuh /admin.
        $middleware->web(append: [
            SecurityHeaders::class,
        ]);

        // Panel Filament menamai halaman masuknya `filament.admin.auth.login`,
        // bukan `login`. Middleware `auth` bawaan Laravel mengalihkan tamu ke
        // `route('login')` dan akan melempar RouteNotFoundException karena nama
        // itu tidak pernah didaftarkan di proyek ini. Halaman admin yang berada
        // di luar panel (laporan kas, tantangan 2FA) memakai `auth`, jadi
        // tujuannya ditunjuk sekali di sini.
        $middleware->redirectGuestsTo(fn () => route('filament.admin.auth.login'));

        // Di belakang proxy (Cloudflare Tunnel, cPanel, nginx, load balancer),
        // permintaan sampai ke PHP sebagai http meski pengunjung membukanya
        // lewat https. Tanpa memercayai X-Forwarded-*, Laravel membangkitkan
        // URL aset berskema http di halaman https, browser memblokirnya sebagai
        // mixed content, dan situsnya tampil tanpa CSS sama sekali.
        //
        // Nilainya ditulis harfiah, bukan dari env(): berkas ini dijalankan
        // SEBELUM .env dimuat, jadi env() di sini selalu null dan pengaturannya
        // diam-diam tidak pernah berlaku.
        //
        // HEADER_X_FORWARDED_HOST sengaja TIDAK ikut. Itulah satu-satunya header
        // di daftar ini yang berbahaya bila dipercaya tanpa syarat: penyerang
        // yang bisa menjangkau PHP secara langsung dapat memalsukan host, dan
        // Laravel akan memakainya untuk membangun URL absolut. Nama host asli
        // sudah diteruskan proxy lewat header Host biasa, jadi tidak ada yang
        // hilang dengan menolaknya.
        $middleware->trustProxies(
            at: '*',
            headers: Request::HEADER_X_FORWARDED_FOR
                | Request::HEADER_X_FORWARDED_PORT
                | Request::HEADER_X_FORWARDED_PROTO
                | Request::HEADER_X_FORWARDED_AWS_ELB,
        );
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
