<?php

namespace Tests\Unit;

use App\Support\Totp;
use PHPUnit\Framework\Attributes\Test;
use ReflectionMethod;
use Tests\TestCase;

class TotpTest extends TestCase
{
    #[Test]
    public function rahasia_dan_uri_authenticator_valid(): void
    {
        $rahasia = Totp::buatRahasia();

        $this->assertMatchesRegularExpression('/^[A-Z2-7]+$/', $rahasia);
        $this->assertStringContainsString('secret='.$rahasia, Totp::uri($rahasia, 'admin@example.test'));
    }

    #[Test]
    public function kode_dengan_format_salah_ditolak(): void
    {
        $this->assertFalse(Totp::verifikasi(Totp::buatRahasia(), 'abc'));
    }

    #[Test]
    public function kode_yang_sudah_dipakai_tidak_diterima_lagi(): void
    {
        $rahasia = Totp::buatRahasia();
        $kode = $this->kodeSaatIni($rahasia);

        $this->assertTrue(Totp::verifikasiSekali($rahasia, $kode, penanda: 1));
        $this->assertFalse(Totp::verifikasiSekali($rahasia, $kode, penanda: 1));
    }

    #[Test]
    public function kode_terpakai_dicatat_per_akun(): void
    {
        // Dua pengurus dapat saja menghasilkan enam angka yang sama pada
        // langkah waktu yang sama. Kode yang dibakar satu akun tidak boleh
        // ikut mengunci akun lain.
        $rahasia = Totp::buatRahasia();
        $kode = $this->kodeSaatIni($rahasia);

        $this->assertTrue(Totp::verifikasiSekali($rahasia, $kode, penanda: 1));
        $this->assertTrue(Totp::verifikasiSekali($rahasia, $kode, penanda: 2));
    }

    private function kodeSaatIni(string $rahasia): string
    {
        return (new ReflectionMethod(Totp::class, 'kode'))->invoke(null, $rahasia, intdiv(time(), 30));
    }
}
