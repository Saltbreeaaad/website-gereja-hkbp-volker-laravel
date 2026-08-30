<?php

namespace Tests\Feature;

use App\Models\KasGereja;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TrenKasTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function ringkasan_selalu_mengembalikan_dua_belas_bulan_berurutan(): void
    {
        $tren = KasGereja::ringkasanBulanan(12);

        $this->assertCount(12, $tren);
        $this->assertSame(
            today()->format('Y-m'),
            $tren->last()['periode'],
            'Bulan terakhir harus bulan berjalan.'
        );
        $this->assertSame(
            today()->startOfMonth()->subMonths(11)->format('Y-m'),
            $tren->first()['periode']
        );
    }

    #[Test]
    public function bulan_tanpa_transaksi_bernilai_nol_bukan_hilang(): void
    {
        KasGereja::factory()->create(['tanggal' => today(), 'nominal' => 500_000]);

        $tren = KasGereja::ringkasanBulanan(12);

        $this->assertCount(12, $tren);
        $this->assertSame(0, $tren->first()['pemasukan']);
        $this->assertSame(500_000, $tren->last()['pemasukan']);
    }

    #[Test]
    public function pemasukan_dan_pengeluaran_dipisah_per_bulan(): void
    {
        $bulanLalu = today()->startOfMonth()->subMonth();

        KasGereja::factory()->create(['tanggal' => $bulanLalu, 'nominal' => 700_000]);
        KasGereja::factory()->pengeluaran(300_000)->create(['tanggal' => $bulanLalu]);
        KasGereja::factory()->create(['tanggal' => today(), 'nominal' => 900_000]);

        $tren = KasGereja::ringkasanBulanan(12)->keyBy('periode');

        $this->assertSame(700_000, $tren[$bulanLalu->format('Y-m')]['pemasukan']);
        $this->assertSame(300_000, $tren[$bulanLalu->format('Y-m')]['pengeluaran']);
        $this->assertSame(900_000, $tren[today()->format('Y-m')]['pemasukan']);
        $this->assertSame(0, $tren[today()->format('Y-m')]['pengeluaran']);
    }

    #[Test]
    public function transaksi_lebih_lama_dari_rentang_tidak_ikut_terhitung(): void
    {
        KasGereja::factory()->create(['tanggal' => today()->subYears(3), 'nominal' => 1_000_000]);

        $tren = KasGereja::ringkasanBulanan(12);

        $this->assertSame(0, $tren->sum('pemasukan'), 'Transaksi di luar 12 bulan tidak boleh ikut.');
    }

    #[Test]
    public function beranda_menampilkan_tabel_rincian_per_bulan(): void
    {
        KasGereja::factory()->create(['tanggal' => today(), 'nominal' => 1_250_000]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Tren 12 bulan terakhir')
            ->assertSee('Lihat rincian per bulan')
            // Tabel rincian adalah cadangan yang terbaca tanpa JavaScript.
            ->assertSee('1.250.000');
    }

    #[Test]
    public function grafik_tidak_dirender_saat_belum_ada_transaksi(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Data keuangan belum tersedia.')
            ->assertDontSee('Tren 12 bulan terakhir');
    }

    #[Test]
    public function bagian_tren_disembunyikan_bila_seluruh_transaksi_di_luar_rentang(): void
    {
        // Saldo tetap tampil, tetapi grafik 12 bulan yang seluruhnya nol tidak
        // memberi informasi apa pun — jadi jangan dirender.
        KasGereja::factory()->create(['tanggal' => today()->subYears(3), 'nominal' => 1_000_000]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Saldo Kas')
            ->assertDontSee('Tren 12 bulan terakhir');
    }
}
