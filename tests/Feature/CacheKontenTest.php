<?php

namespace Tests\Feature;

use App\Models\Galeri;
use App\Models\JadwalIbadah;
use App\Support\CacheKonten;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CacheKontenTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function kunjungan_kedua_ke_beranda_tidak_menembak_database_lagi(): void
    {
        Galeri::factory()->create(['judul' => 'Paskah Bersama']);

        $this->get(route('home'))->assertOk();

        $jumlahQuery = 0;
        DB::listen(function () use (&$jumlahQuery) {
            $jumlahQuery++;
        });

        $this->get(route('home'))->assertOk()->assertSee('Paskah Bersama');

        $this->assertSame(0, $jumlahQuery, 'Beranda kedua seharusnya seluruhnya dilayani dari cache.');
    }

    #[Test]
    public function menyimpan_data_baru_langsung_membatalkan_cache_beranda(): void
    {
        $this->get(route('home'))->assertOk()->assertDontSee('Kebaktian Syukur');

        JadwalIbadah::factory()->create([
            'nama_ibadah' => 'Kebaktian Syukur',
            'tanggal' => today(),
        ]);

        $this->get(route('home'))->assertOk()->assertSee('Kebaktian Syukur');
    }

    #[Test]
    public function menghapus_data_langsung_membatalkan_cache_beranda(): void
    {
        $galeri = Galeri::factory()->create(['judul' => 'Retret Naposobulung']);

        $this->get(route('home'))->assertOk()->assertSee('Retret Naposobulung');

        $galeri->delete();

        $this->get(route('home'))->assertOk()->assertDontSee('Retret Naposobulung');
    }

    #[Test]
    public function sakelar_konfigurasi_mematikan_cache_sepenuhnya(): void
    {
        config(['gereja.cache_konten' => false]);

        // Closure biasa, bukan arrow function: arrow function menyalin $hitung
        // ke lingkupnya sendiri sehingga referensinya tidak pernah ikut naik.
        $hitung = 0;
        $nilai = function () use (&$hitung) {
            return CacheKonten::ingat('uji', function () use (&$hitung) {
                return ++$hitung;
            });
        };

        $this->assertSame(1, $nilai());
        $this->assertSame(2, $nilai(), 'Dengan cache mati, callback harus dijalankan ulang tiap kali.');

        config(['gereja.cache_konten' => true]);

        $this->assertSame(3, $nilai());
        $this->assertSame(3, $nilai(), 'Dengan cache menyala, hasilnya harus dipakai ulang.');
    }

    #[Test]
    public function halaman_galeri_menyimpan_tiap_nomor_halaman_secara_terpisah(): void
    {
        // 13 foto: satu meluber ke halaman kedua.
        Galeri::factory()->count(12)->create(['judul' => 'Foto Halaman Satu']);
        Galeri::factory()->create([
            'judul' => 'Foto Halaman Dua',
            'tanggal' => today()->subYear(),
        ]);

        $this->get(route('galeri'))->assertOk()->assertDontSee('Foto Halaman Dua');
        $this->get(route('galeri', ['page' => 2]))->assertOk()->assertSee('Foto Halaman Dua');

        // Ulangi dari cache: isi tiap halaman harus tetap seperti semula.
        $this->get(route('galeri'))->assertOk()->assertDontSee('Foto Halaman Dua');
        $this->get(route('galeri', ['page' => 2]))->assertOk()->assertSee('Foto Halaman Dua');
    }

    /**
     * Produksi memakai store `database`, yang menyimpan nilai dalam bentuk
     * terserialisasi. `Cache::increment` di store semacam itu hanya bekerja bila
     * nilainya terbaca sebagai angka — tes berjalan dengan store `array` yang
     * menyimpan nilai apa adanya, jadi jalur itu tidak akan pernah teruji di
     * sana. Store `file` memakai serialisasi yang sama seperti `database`.
     */
    #[Test]
    public function nomor_versi_tetap_naik_di_store_yang_menyerialkan_nilai(): void
    {
        config(['cache.default' => 'file']);
        Cache::store('file')->flush();

        $awal = CacheKonten::versi();

        CacheKonten::segarkan();

        $this->assertSame($awal + 1, CacheKonten::versi());

        Cache::store('file')->flush();
    }
}
