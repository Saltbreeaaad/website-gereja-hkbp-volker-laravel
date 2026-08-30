<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Vite;
use Symfony\Component\HttpFoundation\Response;

/**
 * Header keamanan untuk halaman publik.
 *
 * public/.htaccess sudah memasang X-Content-Type-Options dan kawan-kawannya,
 * tetapi itu hanya berlaku bila situs dilayani Apache. Header di sini ikut ke
 * mana pun aplikasi dipasang (nginx, Caddy, `php artisan serve`) dan menambahkan
 * Content-Security-Policy, yang tidak praktis ditulis di .htaccess karena
 * nonce-nya harus berganti setiap request.
 *
 * Middleware ini sengaja hanya dipasang pada grup `web`. Panel Filament
 * merakit daftar middleware-nya sendiri di AdminPanelProvider, jadi halaman
 * admin tidak ikut terkena CSP ini — Alpine dan Livewire di sana butuh
 * kelonggaran yang tidak boleh diberikan ke halaman publik.
 */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        // Dibuat sebelum view dirender supaya tag <script> hasil @vite, skrip
        // prefetch Vite, dan blok JSON-LD di layout memakai nonce yang sama
        // dengan yang diumumkan di header.
        Vite::useCspNonce();

        $response = $next($request);

        foreach ($this->header() as $nama => $nilai) {
            $response->headers->set($nama, $nilai);
        }

        return $response;
    }

    /** @return array<string, string> */
    private function header(): array
    {
        return [
            'Content-Security-Policy' => $this->csp(),
            'X-Content-Type-Options' => 'nosniff',
            'Referrer-Policy' => 'strict-origin-when-cross-origin',
            'X-Frame-Options' => 'SAMEORIGIN',
            'Permissions-Policy' => 'camera=(), microphone=(), geolocation=(), interest-cohort=()',
            'Cross-Origin-Opener-Policy' => 'same-origin',
        ];
    }

    private function csp(): string
    {
        $nonce = Vite::cspNonce();

        // Peta lokasi di footer disematkan dari Google Maps.
        $peta = 'https://www.google.com';

        $skrip = ["'self'", "'nonce-{$nonce}'"];
        $gaya = ["'self'", 'https://fonts.googleapis.com'];
        $koneksi = ["'self'"];

        // Saat `npm run dev` menyala, @vite menunjuk ke server Vite dan membuka
        // WebSocket untuk hot reload. Tanpa kelonggaran ini alur pengembangan
        // ikut terblokir — dan kelonggarannya hilang sendiri begitu Vite mati.
        if (Vite::isRunningHot() && ($asal = $this->asalServerVite()) !== null) {
            $skrip[] = $asal;
            $gaya[] = $asal;
            $koneksi[] = $asal;
            $koneksi[] = str_replace(['http://', 'https://'], ['ws://', 'wss://'], $asal);
        }

        $arahan = [
            "default-src 'self'",
            'script-src '.implode(' ', $skrip),
            'style-src '.implode(' ', $gaya),
            "img-src 'self' data:",
            "font-src 'self' https://fonts.gstatic.com",
            'connect-src '.implode(' ', $koneksi),
            "frame-src {$peta}",
            "object-src 'none'",
            "base-uri 'self'",
            "form-action 'self'",
            "frame-ancestors 'self'",
        ];

        // Hanya di produksi: di lokal situs dilayani lewat http biasa, dan
        // arahan ini justru bisa mengubah permintaan aset menjadi https.
        if (app()->isProduction()) {
            $arahan[] = 'upgrade-insecure-requests';
        }

        return implode('; ', $arahan);
    }

    /** Asal server Vite saat mode hot, dibaca dari berkas hot yang ditulis Vite. */
    private function asalServerVite(): ?string
    {
        $isi = @file_get_contents(Vite::hotFile());

        if ($isi === false) {
            return null;
        }

        $asal = rtrim(trim($isi), '/');

        return $asal === '' ? null : $asal;
    }
}
