<?php

namespace Tests\Feature;

use App\Models\Galeri;
use App\Models\Renungan;
use App\Models\WartaJemaat;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PencarianKontenTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function warta_dapat_dicari_berdasarkan_judul(): void
    {
        WartaJemaat::factory()->create(['judul' => 'Warta Natal']);
        WartaJemaat::factory()->create(['judul' => 'Warta Paskah']);

        $this->get(route('warta', ['q' => 'Natal']))
            ->assertOk()
            ->assertSee('Warta Natal')
            ->assertDontSee('Warta Paskah');
    }

    #[Test]
    public function galeri_dapat_dicari_dan_disaring_berdasarkan_kategori(): void
    {
        Galeri::factory()->create(['judul' => 'Aksi Diakonia', 'kategori' => 'Sosial']);
        Galeri::factory()->create(['judul' => 'Latihan Koor', 'kategori' => 'Kategorial']);

        $this->get(route('galeri', ['q' => 'Diakonia', 'kategori' => 'Sosial']))
            ->assertOk()
            ->assertSee('Aksi Diakonia')
            ->assertDontSee('Latihan Koor');
    }

    #[Test]
    public function arsip_renungan_mencari_judul_penulis_dan_isi(): void
    {
        Renungan::factory()->create(['judul' => 'Pengharapan Baru', 'penulis' => 'Pdt. Siregar', 'isi' => 'Kasih yang memulihkan.']);
        Renungan::factory()->create(['judul' => 'Renungan Lain', 'penulis' => 'Tim', 'isi' => 'Isi lainnya.']);

        $this->get(route('renungan.arsip', ['q' => 'memulihkan']))
            ->assertOk()
            ->assertSee('Pengharapan Baru')
            ->assertDontSee('Renungan Lain');
    }
}
