<?php

namespace Tests\Feature;

use App\Models\Galeri;
use App\Models\JadwalIbadah;
use App\Models\KasGereja;
use App\Models\Parhalado;
use App\Models\PenggunaanGereja;
use App\Models\Renungan;
use App\Models\WartaJemaat;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Regresi untuk bug yang hanya muncul di store cache yang MENYERIALKAN nilai.
 *
 * `config('cache.serializable_classes')` bernilai `false` — bawaan Laravel yang
 * menolak meng-unserialize kelas PHP apa pun dari cache (perlindungan gadget
 * chain bila APP_KEY bocor). Store `database` (dipakai produksi), `file`, dan
 * `redis` semuanya menegakkannya; store `array` yang dipakai pengujian tidak
 * menyerialkan sama sekali.
 *
 * Akibatnya Eloquent Collection yang disimpan ke cache kembali sebagai
 * `__PHP_Incomplete_Class`, dan halaman meledak pada kunjungan KEDUA — yang
 * pertama masih dilayani hasil query segar. Seluruh suite hijau, situsnya rusak.
 *
 * Karena itu tiap kasus di bawah memuat halamannya DUA KALI: yang pertama
 * mengisi cache, yang kedua membacanya kembali.
 */
class CacheStoreSerialisasiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Store `file` memakai serialisasi yang sama dengan `database`.
        config(['cache.default' => 'file']);
        Cache::store('file')->flush();
    }

    protected function tearDown(): void
    {
        Cache::store('file')->flush();

        parent::tearDown();
    }

    #[Test]
    public function beranda_tetap_utuh_pada_kunjungan_kedua(): void
    {
        Parhalado::factory()->create(['nama' => 'St. Marbun', 'kategori' => 'Parhalado']);
        JadwalIbadah::factory()->create(['nama_ibadah' => 'Ibadah Minggu', 'tanggal' => today()]);
        WartaJemaat::factory()->create(['judul' => 'Warta Pekan Ini']);
        Renungan::factory()->create(['judul' => 'Damai Sejahtera']);
        Galeri::factory()->create(['judul' => 'Paskah Bersama']);
        KasGereja::factory()->create(['nominal' => 2_500_000, 'tanggal' => today()]);

        foreach (['pertama', 'kedua'] as $kunjungan) {
            $this->get(route('home'))
                ->assertOk()
                ->assertSee('St. Marbun')
                ->assertSee('Ibadah Minggu')
                ->assertSee('Warta Pekan Ini')
                ->assertSee('Damai Sejahtera')
                ->assertSee('Paskah Bersama')
                ->assertSee('Rp 2.500.000');
        }
    }

    #[Test]
    public function tanggal_tetap_menjadi_carbon_setelah_dibaca_dari_cache(): void
    {
        // Model dirakit ulang dari atribut mentah; bila cast-nya tidak ikut
        // terpasang, `$item->tanggal->translatedFormat(...)` di Blade meledak.
        Renungan::factory()->create(['judul' => 'Uji Tanggal', 'tanggal' => '2026-03-15']);

        $this->get(route('home'))->assertOk();
        $this->get(route('home'))->assertOk()->assertSee('15 Mar 2026');
    }

    #[Test]
    public function halaman_pelayan_tetap_utuh_pada_kunjungan_kedua(): void
    {
        Parhalado::factory()->create(['nama' => 'Pdt. Sihombing', 'kategori' => 'Pendeta']);

        $this->get(route('pelayan'))->assertOk();
        $this->get(route('pelayan'))->assertOk()->assertSee('Pdt. Sihombing');
    }

    #[Test]
    public function paginasi_galeri_tetap_utuh_pada_kunjungan_kedua(): void
    {
        Galeri::factory()->count(13)->create(['judul' => 'Dokumentasi Kegiatan']);

        foreach (['pertama', 'kedua'] as $kunjungan) {
            $this->get(route('galeri'))
                ->assertOk()
                ->assertSee('Dokumentasi Kegiatan')
                // Tautan paginasi harus ikut selamat: paginator dirakit ulang
                // dari angka yang disimpan, bukan disimpan sebagai objek.
                ->assertSee('page=2', escape: false);
        }
    }

    #[Test]
    public function paginasi_arsip_warta_tetap_utuh_pada_kunjungan_kedua(): void
    {
        WartaJemaat::factory()->count(13)->create(['judul' => 'Warta Mingguan']);

        $this->get(route('warta'))->assertOk();
        $this->get(route('warta'))->assertOk()->assertSee('Warta Mingguan')->assertSee('page=2', escape: false);
    }

    #[Test]
    public function halaman_penggunaan_gereja_tetap_utuh_pada_kunjungan_kedua(): void
    {
        PenggunaanGereja::factory()->create([
            'nama_kegiatan' => 'Latihan Koor',
            'tanggal' => today()->addDay(),
            'status' => PenggunaanGereja::DISETUJUI,
        ]);

        $this->get(route('penggunaan-gereja'))->assertOk();
        $this->get(route('penggunaan-gereja'))->assertOk()->assertSee('Latihan Koor');
    }

    #[Test]
    public function halaman_renungan_tetap_utuh_pada_kunjungan_kedua(): void
    {
        Renungan::factory()->create(['judul' => 'Firman Hari Ini', 'tanggal' => today()]);

        $this->get(route('renungan'))->assertOk();
        $this->get(route('renungan'))->assertOk()->assertSee('Firman Hari Ini');
    }

    #[Test]
    public function sitemap_tetap_utuh_pada_kunjungan_kedua(): void
    {
        Renungan::factory()->create(['tanggal' => '2026-03-15']);

        $this->get('/sitemap.xml')->assertOk();
        $this->get('/sitemap.xml')->assertOk()->assertSee('2026-03-15');
    }

    #[Test]
    public function tidak_ada_objek_yang_ikut_tersimpan_ke_cache(): void
    {
        Galeri::factory()->create();
        Parhalado::factory()->create();

        $this->get(route('home'))->assertOk();
        $this->get(route('pelayan'))->assertOk();

        // Pagar langsung di atas akar masalahnya: apa pun yang masuk cache isi
        // harus berupa nilai polos, supaya tidak bergantung pada store apa pun.
        foreach ($this->berkasCache() as $berkas) {
            $isi = file_get_contents($berkas);

            $this->assertStringNotContainsString(
                'O:',
                substr($isi, 10),
                "Objek terserialisasi masuk ke cache: {$berkas}"
            );
        }
    }

    /** @return list<string> */
    private function berkasCache(): array
    {
        $berkas = [];

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(storage_path('framework/cache/data'), \FilesystemIterator::SKIP_DOTS)
        );

        foreach ($iterator as $item) {
            if ($item->isFile()) {
                $berkas[] = $item->getPathname();
            }
        }

        return $berkas;
    }
}
