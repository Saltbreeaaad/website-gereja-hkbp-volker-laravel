<?php

namespace Tests\Feature;

use App\Models\Renungan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SeoTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function sitemap_memuat_seluruh_halaman_utama(): void
    {
        $response = $this->get('/sitemap.xml')
            ->assertOk()
            ->assertHeader('Content-Type', 'application/xml; charset=UTF-8');

        foreach (['home', 'profil', 'pelayan', 'renungan', 'galeri', 'penggunaan-gereja'] as $rute) {
            $response->assertSee(route($rute), false);
        }
    }

    #[Test]
    public function sitemap_memuat_setiap_edisi_renungan(): void
    {
        $renungan = Renungan::factory()->create(['tanggal' => today()->subDays(4)]);

        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertSee(route('renungan', ['tanggal' => $renungan->tanggal->toDateString()]), false);
    }

    #[Test]
    public function robots_menunjuk_ke_sitemap_dan_menutup_panel_admin(): void
    {
        $this->get('/robots.txt')
            ->assertOk()
            ->assertSee('Disallow: /admin')
            ->assertSee('Sitemap: '.route('sitemap'));
    }

    #[Test]
    public function setiap_halaman_punya_judul_dan_deskripsi_yang_berbeda(): void
    {
        $judul = [];

        foreach (['home', 'profil', 'pelayan', 'renungan', 'galeri', 'penggunaan-gereja'] as $rute) {
            $isi = $this->get(route($rute))->assertOk()->getContent();

            preg_match('/<title>(.*?)<\/title>/s', $isi, $cocok);
            $judul[$rute] = $cocok[1] ?? null;

            $this->assertNotNull($judul[$rute], "Halaman {$rute} tidak punya <title>.");
            $this->assertMatchesRegularExpression(
                '/<meta name="description" content=".+?">/',
                $isi,
                "Halaman {$rute} tidak punya meta description."
            );
        }

        $this->assertSame(
            count($judul),
            count(array_unique($judul)),
            'Ada halaman yang memakai <title> yang sama.'
        );
    }
}
