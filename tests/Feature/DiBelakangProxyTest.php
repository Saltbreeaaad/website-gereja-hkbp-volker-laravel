<?php

namespace Tests\Feature;

use App\Models\Galeri;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Situs harus benar saat dilayani di belakang proxy TLS — Cloudflare Tunnel
 * saat presentasi, dan nanti cPanel atau load balancer di produksi.
 *
 * Dua hal yang dulu rusak dan tidak terlihat sama sekali dari localhost:
 * URL aset tetap berskema http di halaman https (diblokir browser sebagai
 * mixed content, situs tampil tanpa CSS), dan URL foto dipatok ke APP_URL
 * sehingga menunjuk `http://localhost:8000` di komputer pengunjung.
 */
class DiBelakangProxyTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Host diminta lewat URL absolut, bukan header `HTTP_HOST`: helper `get()`
     * memperlakukan kuncinya sebagai header HTTP biasa, sehingga nama host-nya
     * tidak pernah benar-benar berganti.
     */
    private const ASAL = 'http://contoh.trycloudflare.com';

    /** @return array<string, string> */
    private function headerProxy(): array
    {
        return [
            'X-Forwarded-Proto' => 'https',
            'X-Forwarded-For' => '203.0.113.9',
        ];
    }

    #[Test]
    public function url_aset_ikut_https_saat_permintaan_diteruskan_proxy(): void
    {
        $isi = $this->get(self::ASAL.'/', $this->headerProxy())->assertOk()->getContent();

        $this->assertStringContainsString('https://contoh.trycloudflare.com/build/', $isi);
        $this->assertStringNotContainsString('http://contoh.trycloudflare.com/build/', $isi);
    }

    #[Test]
    public function canonical_ikut_https(): void
    {
        $this->get(self::ASAL.'/', $this->headerProxy())
            ->assertOk()
            ->assertSee('<link rel="canonical" href="https://contoh.trycloudflare.com', escape: false);
    }

    #[Test]
    public function url_foto_relatif_sehingga_ikut_host_mana_pun(): void
    {
        Galeri::factory()->create(['foto' => 'galeri-photos/uji.jpg']);

        $isi = $this->get(self::ASAL.'/', $this->headerProxy())->assertOk()->getContent();

        // Relatif: browser menyelesaikannya terhadap host yang sedang dibuka.
        $this->assertStringContainsString('src="/storage/galeri-photos/uji.jpg"', $isi);
        $this->assertStringNotContainsString('localhost', $isi);
    }

    #[Test]
    public function og_image_tetap_absolut(): void
    {
        // Perayap media sosial tidak punya konteks halaman untuk menyelesaikan
        // URL relatif, jadi yang satu ini harus tetap absolut.
        $isi = $this->get(self::ASAL.'/', $this->headerProxy())->assertOk()->getContent();

        preg_match('/<meta property="og:image" content="([^"]+)"/', $isi, $cocok);

        $this->assertNotEmpty($cocok, 'og:image harus ada.');
        $this->assertStringStartsWith('https://contoh.trycloudflare.com/', $cocok[1]);
    }

    /**
     * HSTS menutup permintaan http yang PERTAMA — satu-satunya yang tidak bisa
     * ditutup redirect di sisi server, karena redirect itu sendiri baru datang
     * setelah permintaannya terkirim.
     *
     * public/.htaccess sudah memasangnya, tetapi hanya terbaca bila situs
     * dilayani Apache. Header yang sama harus ikut ke nginx, Caddy, dan hosting
     * mana pun — itulah sebab SecurityHeaders ada.
     */
    #[Test]
    public function hsts_terpasang_di_produksi_lewat_https(): void
    {
        $this->app['env'] = 'production';

        $this->get(self::ASAL.'/', $this->headerProxy())
            ->assertOk()
            ->assertHeader('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
    }

    #[Test]
    public function hsts_tidak_dipasang_saat_pengembangan_lokal(): void
    {
        // Memasangnya di lokal berarti mengunci `localhost` ke https di browser
        // pengembang selama setahun — kesalahan yang bertahan lama dan sulit
        // ditelusuri, karena tidak ada yang menghubungkannya dengan situs ini.
        $this->get('/')->assertOk()->assertHeaderMissing('Strict-Transport-Security');
    }

    #[Test]
    public function host_tidak_bisa_dipalsukan_lewat_x_forwarded_host(): void
    {
        // HEADER_X_FORWARDED_HOST sengaja tidak dipercaya: penyerang yang bisa
        // menjangkau PHP langsung tidak boleh bisa menentukan host yang dipakai
        // Laravel untuk membangun URL absolut.
        $isi = $this->get(self::ASAL.'/', [
            ...$this->headerProxy(),
            'X-Forwarded-Host' => 'situs-penyerang.example',
        ])->assertOk()->getContent();

        $this->assertStringNotContainsString('situs-penyerang.example', $isi);
        $this->assertStringContainsString('contoh.trycloudflare.com', $isi);
    }
}
