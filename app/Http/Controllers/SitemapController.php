<?php

namespace App\Http\Controllers;

use App\Models\Renungan;
use App\Support\CacheKonten;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    /**
     * Sitemap XML untuk mesin pencari: halaman statis ditambah setiap edisi
     * renungan (satu URL per tanggal terbit).
     */
    public function __invoke(): Response
    {
        $url = [];

        foreach (config('gereja.menu') as $item) {
            $url[] = [
                'loc' => route($item['route']),
                'changefreq' => $item['route'] === 'home' ? 'daily' : 'weekly',
                'priority' => $item['route'] === 'home' ? '1.0' : '0.8',
                'lastmod' => null,
            ];
        }

        // Sitemap dirayapi mesin pencari berulang kali untuk isi yang sama;
        // daftar renungannya diambil dari cache yang sama dengan halaman publik.
        CacheKonten::ingatModel('sitemap:renungan', Renungan::class, fn () => Renungan::query()
            ->select(['id', 'tanggal', 'updated_at'])
            ->terbaru()
            ->limit(200)
            ->get())
            ->each(function (Renungan $renungan) use (&$url) {
                $url[] = [
                    'loc' => route('renungan', ['tanggal' => $renungan->tanggal->toDateString()]),
                    'changefreq' => 'monthly',
                    'priority' => '0.6',
                    'lastmod' => $renungan->updated_at?->toAtomString(),
                ];
            });

        return response()
            ->view('sitemap', ['urls' => $url])
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }
}
