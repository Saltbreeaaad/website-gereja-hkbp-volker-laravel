<?php

namespace Tests\Feature;

use App\Models\Galeri;
use App\Models\JadwalIbadah;
use App\Models\KasGereja;
use App\Models\Parhalado;
use App\Models\Renungan;
use App\Models\WartaJemaat;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class HalamanPublikTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function semua_halaman_publik_dapat_diakses(): void
    {
        foreach (['home', 'profil', 'pelayan', 'renungan', 'galeri', 'penggunaan-gereja'] as $rute) {
            $this->get(route($rute))->assertOk();
        }
    }

    #[Test]
    public function halaman_tetap_tampil_meski_database_kosong(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Belum ada jadwal ibadah mendatang')
            ->assertSee('Data keuangan belum tersedia');
    }

    #[Test]
    public function setiap_halaman_memuat_meta_seo_dan_navigasi_mobile(): void
    {
        $response = $this->get(route('home'))->assertOk();

        $response->assertSee('<meta name="description"', false);
        $response->assertSee('<link rel="canonical"', false);
        $response->assertSee('property="og:title"', false);
        $response->assertSee('application/ld+json', false);

        // Tombol dan panel menu untuk layar kecil harus ada di setiap halaman.
        $response->assertSee('data-menu-toggle', false);
        $response->assertSee('id="menu-mobile"', false);
        $response->assertSee('Lompat ke konten utama');
    }

    #[Test]
    public function beranda_hanya_menampilkan_jadwal_ibadah_yang_belum_lewat(): void
    {
        JadwalIbadah::factory()->create([
            'nama_ibadah' => 'Ibadah Sudah Lewat',
            'tanggal' => today()->subWeek(),
        ]);

        JadwalIbadah::factory()->create([
            'nama_ibadah' => 'Ibadah Mendatang',
            'tanggal' => today()->addWeek(),
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Ibadah Mendatang')
            ->assertDontSee('Ibadah Sudah Lewat');
    }

    #[Test]
    public function jadwal_ibadah_hari_ini_masih_dianggap_mendatang(): void
    {
        JadwalIbadah::factory()->create([
            'nama_ibadah' => 'Ibadah Hari Ini',
            'tanggal' => today(),
        ]);

        $this->get(route('home'))->assertOk()->assertSee('Ibadah Hari Ini');
    }

    #[Test]
    public function saldo_kas_dihitung_dari_selisih_pemasukan_dan_pengeluaran(): void
    {
        KasGereja::factory()->create(['nominal' => 5_000_000]);
        KasGereja::factory()->pengeluaran(1_250_000)->create();

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Rp 3.750.000')
            // Total sepanjang masa tetap ditampilkan sebagai konteks saldo,
            // di samping grafik tren 12 bulan.
            ->assertSee('Rp 5.000.000')
            ->assertSee('Rp 1.250.000')
            ->assertSee('data-tren=', false);
    }

    #[Test]
    public function warta_tanpa_berkas_tidak_menampilkan_tautan_unduh_yang_rusak(): void
    {
        WartaJemaat::factory()->tanpaBerkas()->create(['judul' => 'Warta Belum Lengkap']);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Warta Belum Lengkap')
            ->assertSee('Berkas belum tersedia');
    }

    #[Test]
    public function warta_dengan_berkas_menampilkan_tautan_unduh(): void
    {
        WartaJemaat::factory()->create(['judul' => 'Warta Lengkap']);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Warta Lengkap')
            ->assertSee('storage/warta-jemaat/contoh.pdf', false);
    }

    #[Test]
    public function kartu_renungan_di_beranda_menautkan_ke_tanggal_edisinya(): void
    {
        $renungan = Renungan::factory()->create(['tanggal' => today()->subDays(3)]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee(route('renungan', ['tanggal' => $renungan->tanggal->toDateString()]), false);
    }

    #[Test]
    public function halaman_pelayan_mengelompokkan_berdasarkan_kategori(): void
    {
        Parhalado::factory()->create(['kategori' => 'Pendeta', 'nama' => 'Pdt Contoh']);
        Parhalado::factory()->create(['kategori' => 'Parhalado', 'nama' => 'Sintua Contoh']);
        Parhalado::factory()->create(['kategori' => 'Kategorial', 'nama' => 'Ketua Naposo']);

        $this->get(route('pelayan'))
            ->assertOk()
            ->assertSee('Pdt Contoh')
            ->assertSee('Sintua Contoh')
            ->assertSee('Ketua Naposo');
    }

    #[Test]
    public function galeri_dipaginasi(): void
    {
        Galeri::factory()->count(15)->create();

        $this->get(route('galeri'))
            ->assertOk()
            ->assertSee('Navigasi halaman galeri')
            ->assertSee(route('galeri').'?page=2', false);
    }

    #[Test]
    public function halaman_tidak_dikenal_menampilkan_404_bermerek(): void
    {
        $this->get('/halaman-yang-tidak-ada')
            ->assertNotFound()
            ->assertSee('Halaman Tidak Ditemukan')
            ->assertSee('Kembali ke Beranda');
    }
}
