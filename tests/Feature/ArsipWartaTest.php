<?php

namespace Tests\Feature;

use App\Models\WartaJemaat;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ArsipWartaTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function arsip_menampilkan_seluruh_warta_dari_terbaru(): void
    {
        WartaJemaat::factory()->create(['judul' => 'Warta Lama', 'tanggal' => today()->subYears(2)]);
        WartaJemaat::factory()->create(['judul' => 'Warta Baru', 'tanggal' => today()]);

        $response = $this->get(route('warta'))->assertOk();

        $isi = $response->getContent();
        $this->assertLessThan(
            strpos($isi, 'Warta Lama'),
            strpos($isi, 'Warta Baru'),
            'Warta terbaru harus tampil lebih dulu.'
        );
    }

    #[Test]
    public function filter_tahun_mempersempit_daftar(): void
    {
        WartaJemaat::factory()->create(['judul' => 'Terbit 2024', 'tanggal' => '2024-05-12']);
        WartaJemaat::factory()->create(['judul' => 'Terbit 2026', 'tanggal' => '2026-05-12']);

        $this->get(route('warta', ['tahun' => 2024]))
            ->assertOk()
            ->assertSee('Terbit 2024')
            ->assertDontSee('Terbit 2026');
    }

    #[Test]
    public function tahun_di_luar_daftar_diabaikan_bukan_menjadi_galat(): void
    {
        WartaJemaat::factory()->create(['judul' => 'Warta Tersedia', 'tanggal' => '2026-05-12']);

        // URL lama yang dibagikan jemaat harus tetap membuka arsip.
        $this->get(route('warta', ['tahun' => 1998]))
            ->assertOk()
            ->assertSee('Warta Tersedia');

        $this->get(route('warta', ['tahun' => 'bukan-angka']))->assertOk();
    }

    #[Test]
    public function warta_tanpa_berkas_tidak_menawarkan_tautan_unduh(): void
    {
        WartaJemaat::factory()->tanpaBerkas()->create(['judul' => 'Belum Diunggah']);

        $this->get(route('warta'))
            ->assertOk()
            ->assertSee('Belum Diunggah')
            ->assertSee('Berkas belum tersedia');
    }

    #[Test]
    public function arsip_kosong_menampilkan_keadaan_kosong(): void
    {
        $this->get(route('warta'))
            ->assertOk()
            ->assertSee('Arsip masih kosong');
    }

    #[Test]
    public function pilihan_tahun_hanya_muncul_bila_ada_lebih_dari_satu_tahun(): void
    {
        WartaJemaat::factory()->create(['tanggal' => '2026-01-05']);

        $this->get(route('warta'))->assertOk()->assertDontSee('Tahun terbit');

        WartaJemaat::factory()->create(['tanggal' => '2024-01-05']);

        $this->get(route('warta'))
            ->assertOk()
            ->assertSee('Tahun terbit')
            ->assertSee('2025', escape: false); // tahun antara ikut terdaftar
    }

    #[Test]
    public function arsip_terdaftar_di_menu_navigasi_dan_sitemap(): void
    {
        $this->get(route('home'))->assertOk()->assertSee(route('warta'));
        $this->get('/sitemap.xml')->assertOk()->assertSee(route('warta'));
    }
}
