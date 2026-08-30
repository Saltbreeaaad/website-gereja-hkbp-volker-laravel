<?php

namespace Tests\Unit;

use App\Casts\JamHarian;
use App\Models\JadwalIbadah;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class JamHarianTest extends TestCase
{
    private JamHarian $cast;

    protected function setUp(): void
    {
        parent::setUp();
        $this->cast = new JamHarian;
    }

    #[Test]
    #[DataProvider('nilaiJam')]
    public function membaca_berbagai_format_penyimpanan(string $tersimpan, string $diharapkan): void
    {
        $jam = $this->cast->get(new JadwalIbadah, 'waktu', $tersimpan, []);

        $this->assertSame($diharapkan, $jam->format('H:i:s'));
    }

    /** @return array<string, array{string, string}> */
    public static function nilaiJam(): array
    {
        return [
            'format singkat' => ['07:00', '07:00:00'],
            'format lengkap' => ['07:00:00', '07:00:00'],
            'baris lama bergaya datetime' => ['2026-08-20 07:00:00', '07:00:00'],
        ];
    }

    #[Test]
    public function menormalkan_penyimpanan_ke_format_jam(): void
    {
        $this->assertSame('19:00:00', $this->cast->set(new JadwalIbadah, 'waktu', '19:00', []));
        $this->assertSame('19:30:00', $this->cast->set(new JadwalIbadah, 'waktu', '19:30:00', []));
    }

    #[Test]
    public function nilai_kosong_tetap_null(): void
    {
        $this->assertNull($this->cast->get(new JadwalIbadah, 'waktu', null, []));
        $this->assertNull($this->cast->set(new JadwalIbadah, 'waktu', null, []));
    }

    #[Test]
    public function dua_jam_dari_hari_berbeda_tetap_dapat_dibandingkan(): void
    {
        $awal = $this->cast->get(new JadwalIbadah, 'waktu', '2020-01-01 08:00:00', []);
        $akhir = $this->cast->get(new JadwalIbadah, 'waktu', '17:00', []);

        $this->assertTrue($awal->lt($akhir));
    }
}
