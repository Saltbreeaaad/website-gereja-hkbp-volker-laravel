<?php

namespace Tests\Feature;

use App\Models\KasGereja;
use App\Models\PeriodeKas;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class LaporanKasTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function periode_yang_ditutup_menolak_transaksi_baru(): void
    {
        PeriodeKas::create([
            'periode' => today()->format('Y-m'),
            'saldo_awal' => 0,
            'ditutup_at' => now(),
        ]);

        $this->expectException(ValidationException::class);
        KasGereja::factory()->create(['tanggal' => today()]);
    }

    #[Test]
    public function laporan_menghitung_saldo_awal_pemasukan_dan_pengeluaran(): void
    {
        $admin = User::factory()->create();
        PeriodeKas::create(['periode' => today()->format('Y-m'), 'saldo_awal' => 500_000]);
        KasGereja::factory()->create(['nominal' => 1_000_000]);
        KasGereja::factory()->pengeluaran(250_000)->create();

        $this->assertCount(2, KasGereja::query()->get());

        $this->actingAs($admin)
            ->get(route('admin.kas.laporan'))
            ->assertOk()
            ->assertSee('Rp 500.000')
            ->assertSee('Rp 1.000.000')
            ->assertSee('Rp 250.000')
            ->assertSee('Rp 1.250.000');
    }

    /**
     * Tamu diantar ke halaman masuk, bukan dibalas 403 telanjang: rutenya kini
     * memakai middleware `auth` seperti seluruh halaman admin lain, sehingga
     * pengurus yang sesinya kedaluwarsa mendarat di tempat yang berguna.
     */
    #[Test]
    public function laporan_dan_csv_tidak_dapat_diakses_tamu(): void
    {
        $masuk = route('filament.admin.auth.login');

        $this->get(route('admin.kas.laporan'))->assertRedirect($masuk);
        $this->get(route('admin.kas.csv'))->assertRedirect($masuk);
    }

    #[Test]
    public function csv_dapat_diunduh_oleh_pengurus(): void
    {
        $admin = User::factory()->create();
        KasGereja::factory()->create(['keterangan' => 'Persembahan Uji']);

        $response = $this->actingAs($admin)->get(route('admin.kas.csv'));

        $response->assertOk()->assertDownload();
        $this->assertStringContainsString('Persembahan Uji', $response->streamedContent());
    }

    #[Test]
    public function bukti_transaksi_dihapus_bersama_transaksi(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('bukti-kas/uji.pdf', '%PDF-1.4');
        $kas = KasGereja::factory()->create(['bukti' => 'bukti-kas/uji.pdf']);

        $kas->delete();

        Storage::disk('local')->assertMissing('bukti-kas/uji.pdf');
    }

    /**
     * Ekspor CSV kini mengalirkan barisnya alih-alih memuat seluruh transaksi
     * ke memori lebih dulu. Yang dijaga di sini adalah bahwa urutan dan angka
     * ringkasannya tidak ikut berubah karenanya: `lazyById()` — pilihan yang
     * tampak wajar untuk mengalirkan baris — akan memaksa urutan menurut id
     * dan diam-diam mengacak laporan yang harus urut tanggal.
     */
    #[Test]
    public function csv_tetap_urut_tanggal_dan_totalnya_benar(): void
    {
        $admin = User::factory()->create();

        // Sengaja dibuat dengan id yang berlawanan arah dengan tanggalnya.
        KasGereja::factory()->create(['tanggal' => '2026-03-20', 'jenis' => 'Pengeluaran', 'nominal' => 100_000, 'keterangan' => 'Belakangan']);
        KasGereja::factory()->create(['tanggal' => '2026-03-05', 'jenis' => 'Pemasukan', 'nominal' => 500_000, 'keterangan' => 'Duluan']);

        $isi = $this->actingAs($admin)
            ->get(route('admin.kas.csv', ['dari' => '2026-03-01', 'sampai' => '2026-03-31']))
            ->assertOk()
            ->streamedContent();

        $this->assertLessThan(strpos($isi, 'Belakangan'), strpos($isi, 'Duluan'));
        $this->assertStringContainsString('"Total pemasukan";;;500000', $isi);
        $this->assertStringContainsString('"Total pengeluaran";;;100000', $isi);
        $this->assertStringContainsString('"Saldo akhir";;;400000', $isi);
    }
}
