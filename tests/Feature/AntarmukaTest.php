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
    public function carousel_menyediakan_tombol_geser_kiri_dan_kanan(): void
    {
        $response = $this->get(route('home'))->assertOk();

        // Dua carousel di beranda: pelayan dan galeri.
        $isi = $response->getContent();
        $this->assertSame(2, substr_count($isi, 'data-swiper-prev'));
        $this->assertSame(2, substr_count($isi, 'data-swiper-next'));

        // Label khusus per carousel, bukan "sebelumnya/berikutnya" yang generik:
        // pembaca layar menyebutkan tombol di luar konteks visualnya.
        $response->assertSee('Geser daftar pelayan ke kiri')
            ->assertSee('Geser daftar pelayan ke kanan')
            ->assertSee('Geser galeri ke kiri')
            ->assertSee('Geser galeri ke kanan');
    }

    #[Test]
    public function tombol_carousel_tersembunyi_sampai_javascript_memasangnya(): void
    {
        // Tanpa JS carousel-nya menjadi daftar biasa, dan tombol yang tidak
        // melakukan apa pun lebih membingungkan daripada tidak ada tombol.
        $isi = $this->get(route('home'))->assertOk()->getContent();

        preg_match_all('/<button[^>]*data-swiper-(?:prev|next|toggle)[^>]*>/', $isi, $cocok);

        $this->assertCount(6, $cocok[0], 'Harus ada 6 tombol kontrol carousel.');

        foreach ($cocok[0] as $tombol) {
            $this->assertStringContainsString('hidden', $tombol);
        }
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
    public function halaman_statis_tidak_mengunduh_modul_widget_yang_tidak_dipakai(): void
    {
        // Swiper dan Chart.js diimpor dinamis oleh app.js. Prefetch global akan
        // mengunduh keduanya juga di halaman statis dan meniadakan lazy-load.
        $isi = $this->get(route('profil'))->assertOk()->getContent();

        $this->assertStringNotContainsString('vendor-swiper', $isi);
        $this->assertStringNotContainsString('vendor-chart', $isi);
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
