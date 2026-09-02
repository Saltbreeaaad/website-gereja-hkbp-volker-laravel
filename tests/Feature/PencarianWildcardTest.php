<?php

namespace Tests\Feature;

use App\Models\Renungan;
use App\Models\WartaJemaat;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Wildcard LIKE di dalam kata pencarian diperlakukan sebagai teks biasa.
 *
 * `%` dan `_` sebelumnya diteruskan mentah ke klausa LIKE, sehingga pencarian
 * jemaat diam-diam berubah maknanya.
 */
class PencarianWildcardTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function persen_dicari_sebagai_huruf_bukan_wildcard(): void
    {
        WartaJemaat::factory()->create(['judul' => 'Kenaikan 50% Persembahan']);
        WartaJemaat::factory()->create(['judul' => 'Kenaikan 5000 Persembahan']);

        $this->get(route('warta', ['q' => '50%']))
            ->assertOk()
            ->assertSee('Kenaikan 50% Persembahan', false)
            ->assertDontSee('Kenaikan 5000 Persembahan');
    }

    #[Test]
    public function underscore_dicari_sebagai_huruf_bukan_wildcard(): void
    {
        WartaJemaat::factory()->create(['judul' => 'Berkas A_B Final']);
        WartaJemaat::factory()->create(['judul' => 'Berkas AXB Final']);

        $this->get(route('warta', ['q' => 'A_B']))
            ->assertOk()
            ->assertSee('Berkas A_B Final')
            ->assertDontSee('Berkas AXB Final');
    }

    /** Karakter escape itu sendiri tidak boleh bocor sebagai perilaku khusus. */
    #[Test]
    public function tanda_seru_tetap_dicari_apa_adanya(): void
    {
        WartaJemaat::factory()->create(['judul' => 'Horas! Selamat Datang']);
        WartaJemaat::factory()->create(['judul' => 'Horas Selamat Datang']);

        $this->get(route('warta', ['q' => 'Horas!']))
            ->assertOk()
            ->assertSee('Horas! Selamat Datang')
            ->assertDontSee('Horas Selamat Datang');
    }

    /** Pencarian arsip renungan menyapu tiga kolom; semuanya harus ikut aman. */
    #[Test]
    public function pencarian_lintas_kolom_tetap_menemukan_yang_seharusnya(): void
    {
        Renungan::factory()->create(['judul' => 'Damai', 'penulis' => 'Pdt. Sitorus', 'isi' => 'Isi renungan pertama.']);
        Renungan::factory()->create(['judul' => 'Sukacita', 'penulis' => 'Pdt. Panjaitan', 'isi' => 'Isi renungan kedua.']);

        $this->get(route('renungan.arsip', ['q' => 'Sitorus']))
            ->assertOk()
            ->assertSee('Damai')
            ->assertDontSee('Sukacita');
    }

    #[Test]
    public function pencarian_kosong_tetap_menampilkan_semua(): void
    {
        WartaJemaat::factory()->create(['judul' => 'Warta Pertama']);
        WartaJemaat::factory()->create(['judul' => 'Warta Kedua']);

        $this->get(route('warta'))
            ->assertOk()
            ->assertSee('Warta Pertama')
            ->assertSee('Warta Kedua');
    }
}
