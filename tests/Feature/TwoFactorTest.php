<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\Totp;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use ReflectionMethod;
use Tests\TestCase;

class TwoFactorTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function akun_dengan_2fa_diarahkan_ke_tantangan(): void
    {
        $user = User::factory()->create();
        $user->forceFill([
            'two_factor_secret' => Totp::buatRahasia(),
            'two_factor_recovery_codes' => [],
            'two_factor_confirmed_at' => now(),
        ])->save();

        $this->actingAs($user)
            ->get('/admin')
            ->assertRedirect(route('two-factor.challenge'));
    }

    #[Test]
    public function akun_tanpa_2fa_tetap_dapat_membuka_panel(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/admin')->assertOk();
    }

    #[Test]
    public function tantangan_menolak_kode_yang_salah(): void
    {
        $user = User::factory()->create();
        $user->forceFill([
            'two_factor_secret' => Totp::buatRahasia(),
            'two_factor_recovery_codes' => [],
            'two_factor_confirmed_at' => now(),
        ])->save();

        $this->actingAs($user)
            ->post(route('two-factor.verify'), ['kode' => '000000'])
            ->assertSessionHasErrors('kode');
    }

    #[Test]
    public function kode_yang_sudah_dipakai_tidak_dapat_diputar_ulang(): void
    {
        // Kode TOTP tetap sah ~90 detik karena jendela toleransinya ±1 langkah.
        // Kode yang terbaca dari balik bahu atau tertinggal di layar yang tidak
        // terkunci karena itu masih dapat dipakai orang lain — kecuali kode
        // yang sudah berhasil dipakai langsung dibakar. RFC 6238 §5.2.
        $rahasia = Totp::buatRahasia();
        $user = User::factory()->create();
        $user->forceFill([
            'two_factor_secret' => $rahasia,
            'two_factor_recovery_codes' => [],
            'two_factor_confirmed_at' => now(),
        ])->save();

        $kode = (new ReflectionMethod(Totp::class, 'kode'))->invoke(null, $rahasia, intdiv(time(), 30));

        $this->actingAs($user)
            ->post(route('two-factor.verify'), ['kode' => $kode])
            ->assertSessionHasNoErrors();

        $this->actingAs($user)
            ->post(route('two-factor.verify'), ['kode' => $kode])
            ->assertSessionHasErrors('kode');
    }
}
