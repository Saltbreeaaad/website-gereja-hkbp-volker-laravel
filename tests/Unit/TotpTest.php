<?php

namespace Tests\Unit;

use App\Support\Totp;
use PHPUnit\Framework\Attributes\Test;
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
}
