<?php

namespace Tests\Feature;

use App\Models\Galeri;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AntarmukaTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function paginasi_memakai_kelas_yang_ikut_dipindai_tailwind(): void
    {
        Galeri::factory()->count(15)->create();

        $isi = $this->get(route('galeri'))->assertOk()->getContent();

        // View paginasi bawaan tinggal di vendor/, yang tidak dipindai Tailwind,
        // sehingga tombolnya tampil polos. Pastikan yang dipakai adalah view
        // terbitan di resources/views/vendor/pagination.
        $this->assertStringContainsString('min-w-11 h-11', $isi);
        $this->assertStringNotContainsString('dark:bg-gray-800', $isi);

        $this->assertStringContainsString('Menampilkan', $isi);
        $this->assertStringContainsString('aria-current="page"', $isi);
    }

    #[Test]
    public function foto_galeri_dapat_diperbesar_lewat_tombol(): void
    {
        Galeri::factory()->create(['judul' => 'Paskah 2026', 'foto' => 'galeri-photos/uji.jpg']);

        $this->get(route('galeri'))
            ->assertOk()
            ->assertSee('id="lightbox"', false)
            ->assertSee('data-lightbox="'.\Storage::disk('public')->url('galeri-photos/uji.jpg').'"', false)
            ->assertSee('Perbesar foto: Paskah 2026');
    }

    #[Test]
    public function galeri_tanpa_foto_tidak_memunculkan_tombol_perbesar(): void
    {
        Galeri::factory()->create(['judul' => 'Belum Ada Foto', 'foto' => null]);

        $this->get(route('galeri'))
            ->assertOk()
            ->assertSee('Belum Ada Foto')
            ->assertDontSee('data-lightbox=', false);
    }

    #[Test]
    public function carousel_menyediakan_kontrol_jeda(): void
    {
        // WCAG 2.2.2: gerakan otomatis harus bisa dihentikan pengguna.
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('data-swiper-toggle', false)
            ->assertSee('Putar otomatis:');
    }

    #[Test]
    public function ringkasan_galat_formulir_dapat_menerima_fokus(): void
    {
        $this->from(route('penggunaan-gereja'))
            ->followingRedirects()
            ->post(route('penggunaan-gereja.store'), [])
            ->assertOk()
            ->assertSee('data-error-summary', false)
            ->assertSee('tabindex="-1"', false);
    }

    #[Test]
    public function tidak_ada_teks_berkontras_rendah_yang_tersisa(): void
    {
        Galeri::factory()->create();

        // gold-500/600 dan slate-400 gagal rasio AA 4.5:1 di atas latar terang.
        foreach (['home', 'profil', 'pelayan', 'renungan', 'galeri', 'penggunaan-gereja'] as $rute) {
            $isi = $this->get(route($rute))->assertOk()->getContent();

            foreach (['text-gold-500', 'text-gold-600', 'text-slate-400'] as $kelas) {
                $this->assertStringNotContainsString(
                    $kelas,
                    $isi,
                    "Halaman {$rute} masih memakai {$kelas} yang gagal kontras AA."
                );
            }
        }
    }
}
