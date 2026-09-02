<?php

namespace Tests\Feature;

use App\Models\KasGereja;
use App\Models\PeriodeKas;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Saldo awal laporan kas untuk rentang yang tidak mulai di tanggal 1.
 *
 * Perhitungan lama membaca saldo_awal bulan $dari apa adanya, sehingga rentang
 * seperti "16–31 Maret" melaporkan saldo akhir yang tidak sama dengan kas yang
 * sebenarnya — transaksi 1–15 Maret hilang dari daftar tetapi saldo awalnya
 * tetap saldo awal 1 Maret.
 */
class SaldoAwalLaporanTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create());
        PeriodeKas::query()->create(['periode' => '2026-03', 'saldo_awal' => 1_000_000]);
    }

    #[Test]
    public function rentang_yang_mulai_di_tengah_bulan_membawa_transaksi_sebelumnya(): void
    {
        // 1-15 Maret: +300.000, -100.000  => saldo per 16 Maret = 1.200.000
        $this->transaksi('2026-03-05', 'Pemasukan', 300_000);
        $this->transaksi('2026-03-10', 'Pengeluaran', 100_000);
        // Dalam rentang laporan.
        $this->transaksi('2026-03-20', 'Pemasukan', 50_000);

        $this->get(route('admin.kas.laporan', ['dari' => '2026-03-16', 'sampai' => '2026-03-31']))
            ->assertOk()
            ->assertSee('Rp 1.200.000')  // saldo awal
            ->assertSee('Rp 1.250.000'); // saldo akhir
    }

    #[Test]
    public function rentang_sebulan_penuh_memakai_saldo_awal_periode(): void
    {
        $this->transaksi('2026-03-20', 'Pemasukan', 50_000);

        $this->get(route('admin.kas.laporan', ['dari' => '2026-03-01', 'sampai' => '2026-03-31']))
            ->assertOk()
            ->assertSee('Rp 1.000.000')
            ->assertSee('Rp 1.050.000');
    }

    /** Bulan setelah periode tercatat mundur ke periode terakhir, bukan ke nol. */
    #[Test]
    public function bulan_tanpa_periode_tercatat_mewarisi_periode_sebelumnya(): void
    {
        $this->transaksi('2026-03-10', 'Pemasukan', 200_000);

        $this->get(route('admin.kas.laporan', ['dari' => '2026-04-01', 'sampai' => '2026-04-30']))
            ->assertOk()
            ->assertSee('Rp 1.200.000');
    }

    private function transaksi(string $tanggal, string $jenis, int $nominal): void
    {
        KasGereja::query()->create([
            'tanggal' => $tanggal,
            'jenis' => $jenis,
            'keterangan' => 'Uji '.$jenis,
            'nominal' => $nominal,
        ]);
    }
}
