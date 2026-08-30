<?php

namespace Tests\Feature;

use App\Models\Galeri;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class FilterGaleriTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function filter_tahun_mempersempit_galeri(): void
    {
        Galeri::factory()->create(['judul' => 'Natal 2024', 'tanggal' => '2024-12-25']);
        Galeri::factory()->create(['judul' => 'Paskah 2026', 'tanggal' => '2026-04-05']);

        $this->get(route('galeri', ['tahun' => 2024]))
            ->assertOk()
            ->assertSee('Natal 2024')
            ->assertDontSee('Paskah 2026');
    }

    #[Test]
    public function penyaring_disembunyikan_bila_hanya_ada_satu_tahun(): void
    {
        Galeri::factory()->create(['tanggal' => '2026-04-05']);

        $this->get(route('galeri'))->assertOk()->assertDontSee('Tahun kegiatan');
    }

    #[Test]
    public function tahun_tanpa_foto_menampilkan_keadaan_kosong_yang_sesuai(): void
    {
        Galeri::factory()->create(['tanggal' => '2024-12-25']);
        Galeri::factory()->create(['tanggal' => '2026-04-05']);

        $this->get(route('galeri', ['tahun' => 2025]))
            ->assertOk()
            ->assertSee('Tidak ada foto pada tahun 2025')
            ->assertSee('Lihat seluruh galeri');
    }

    #[Test]
    public function tahun_yang_tidak_sah_diabaikan_bukan_menjadi_galat(): void
    {
        Galeri::factory()->create(['judul' => 'Foto Tersedia', 'tanggal' => '2026-04-05']);

        $this->get(route('galeri', ['tahun' => 'bukan-angka']))
            ->assertOk()
            ->assertSee('Foto Tersedia');
    }
}
