<?php

namespace Tests\Feature;

use App\Models\Renungan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RenunganTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function tanpa_parameter_menampilkan_renungan_hari_ini(): void
    {
        Renungan::factory()->create(['judul' => 'Renungan Hari Ini', 'tanggal' => today()]);
        Renungan::factory()->create(['judul' => 'Renungan Kemarin', 'tanggal' => today()->subDay()]);

        $this->get(route('renungan'))
            ->assertOk()
            ->assertSee('Renungan Hari Ini');
    }

    #[Test]
    public function dapat_membuka_edisi_pada_tanggal_tertentu(): void
    {
        $renungan = Renungan::factory()->create([
            'judul' => 'Edisi Lampau',
            'tanggal' => today()->subDays(10),
        ]);

        $this->get(route('renungan', ['tanggal' => $renungan->tanggal->toDateString()]))
            ->assertOk()
            ->assertSee('Edisi Lampau');
    }

    #[Test]
    public function tanggal_tidak_valid_jatuh_ke_hari_ini_tanpa_error(): void
    {
        Renungan::factory()->create(['judul' => 'Renungan Hari Ini', 'tanggal' => today()]);

        foreach (['bukan-tanggal', '2026-13-45', '<script>alert(1)</script>', ''] as $input) {
            $this->get(route('renungan', ['tanggal' => $input]))
                ->assertOk()
                ->assertSee('Renungan Hari Ini');
        }
    }

    #[Test]
    public function tanggal_tanpa_renungan_menampilkan_pesan_kosong(): void
    {
        $this->get(route('renungan', ['tanggal' => today()->addDays(5)->toDateString()]))
            ->assertOk()
            ->assertSee('Tidak Ada Renungan')
            ->assertSee('Lihat Renungan Hari Ini');
    }

    #[Test]
    public function menampilkan_navigasi_ke_edisi_sebelum_dan_sesudah(): void
    {
        Renungan::factory()->create(['judul' => 'Edisi Lama', 'tanggal' => today()->subDays(2)]);
        Renungan::factory()->create(['judul' => 'Edisi Tengah', 'tanggal' => today()->subDay()]);
        Renungan::factory()->create(['judul' => 'Edisi Baru', 'tanggal' => today()]);

        $this->get(route('renungan', ['tanggal' => today()->subDay()->toDateString()]))
            ->assertOk()
            ->assertSee('Edisi sebelumnya')
            ->assertSee('Edisi berikutnya')
            ->assertSee('Edisi Lama')
            ->assertSee('Edisi Baru');
    }

    #[Test]
    public function judul_dan_meta_deskripsi_mengikuti_renungan_yang_dibuka(): void
    {
        Renungan::factory()->create([
            'judul' => 'Damai di Tengah Badai',
            'tanggal' => today(),
            'isi' => 'Tuhan menyertai kita dalam setiap pergumulan hidup.',
        ]);

        $this->get(route('renungan'))
            ->assertOk()
            ->assertSee('<title>Damai di Tengah Badai - Renungan', false)
            ->assertSee('Tuhan menyertai kita dalam setiap pergumulan hidup.', false);
    }
}
