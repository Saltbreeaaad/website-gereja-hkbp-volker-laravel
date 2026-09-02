<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\Totp;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
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
}
