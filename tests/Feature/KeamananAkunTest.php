<?php

namespace Tests\Feature;

use App\Filament\Pages\KeamananAkun;
use App\Models\User;
use App\Support\Totp;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use ReflectionMethod;
use Tests\TestCase;

/**
 * Pengerasan alur 2FA: konfirmasi kata sandi, kode pemulihan yang dapat
 * diterbitkan ulang, dan verifikasi yang terikat pada satu sesi masuk.
 */
class KeamananAkunTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Sesi yang ditinggal terbuka tidak cukup untuk mematikan 2FA.
     */
    #[Test]
    public function menonaktifkan_2fa_menolak_kata_sandi_yang_salah(): void
    {
        [$pengguna, $rahasia] = $this->penggunaBer2FA();

        Livewire::actingAs($pengguna)
            ->test(KeamananAkun::class)
            ->set('kataSandi', 'salah-sekali')
            ->set('kode', $this->kodeSaatIni($rahasia))
            ->call('nonaktifkan')
            ->assertHasErrors('kataSandi');

        $this->assertTrue($pengguna->fresh()->twoFactorAktif());
    }

    #[Test]
    public function menonaktifkan_2fa_berhasil_dengan_kata_sandi_dan_kode_benar(): void
    {
        [$pengguna, $rahasia] = $this->penggunaBer2FA();

        Livewire::actingAs($pengguna)
            ->test(KeamananAkun::class)
            ->set('kataSandi', 'password')
            ->set('kode', $this->kodeSaatIni($rahasia))
            ->call('nonaktifkan')
            ->assertHasNoErrors();

        $this->assertFalse($pengguna->fresh()->twoFactorAktif());
    }

    #[Test]
    public function mengaktifkan_2fa_menolak_kata_sandi_yang_salah(): void
    {
        $pengguna = User::factory()->create();

        $komponen = Livewire::actingAs($pengguna)->test(KeamananAkun::class);
        $rahasia = $komponen->get('rahasia');

        $komponen
            ->set('kataSandi', 'salah-sekali')
            ->set('kode', $this->kodeSaatIni($rahasia))
            ->call('aktifkan')
            ->assertHasErrors('kataSandi');

        $this->assertFalse($pengguna->fresh()->twoFactorAktif());
    }

    #[Test]
    public function mengaktifkan_2fa_menghasilkan_delapan_kode_pemulihan(): void
    {
        $pengguna = User::factory()->create();

        $komponen = Livewire::actingAs($pengguna)->test(KeamananAkun::class);
        $rahasia = $komponen->get('rahasia');

        $komponen
            ->set('kataSandi', 'password')
            ->set('kode', $this->kodeSaatIni($rahasia))
            ->call('aktifkan')
            ->assertHasNoErrors();

        $this->assertTrue($pengguna->fresh()->twoFactorAktif());
        $this->assertCount(8, $komponen->get('kodePemulihan'));
        $this->assertCount(8, $pengguna->fresh()->two_factor_recovery_codes);
    }

    /**
     * Sebelumnya kode pemulihan hanya lahir sekali; habis berarti terkunci.
     */
    #[Test]
    public function kode_pemulihan_dapat_diterbitkan_ulang(): void
    {
        [$pengguna, $rahasia] = $this->penggunaBer2FA();
        $lama = $pengguna->two_factor_recovery_codes;

        $komponen = Livewire::actingAs($pengguna)
            ->test(KeamananAkun::class)
            ->set('kataSandi', 'password')
            ->set('kode', $this->kodeSaatIni($rahasia))
            ->call('buatUlangKodePemulihan')
            ->assertHasNoErrors();

        $this->assertCount(8, $komponen->get('kodePemulihan'));
        $this->assertNotSame($lama, $pengguna->fresh()->two_factor_recovery_codes);
    }

    #[Test]
    public function terbitkan_ulang_menolak_kata_sandi_yang_salah(): void
    {
        [$pengguna, $rahasia] = $this->penggunaBer2FA();
        $lama = $pengguna->two_factor_recovery_codes;

        Livewire::actingAs($pengguna)
            ->test(KeamananAkun::class)
            ->set('kataSandi', 'salah-sekali')
            ->set('kode', $this->kodeSaatIni($rahasia))
            ->call('buatUlangKodePemulihan')
            ->assertHasErrors('kataSandi');

        $this->assertSame($lama, $pengguna->fresh()->two_factor_recovery_codes);
    }

    #[Test]
    public function sisa_kode_pemulihan_dilaporkan(): void
    {
        [$pengguna] = $this->penggunaBer2FA();

        Livewire::actingAs($pengguna)
            ->test(KeamananAkun::class)
            ->assertSee('Sisa 8 kode pemulihan');
    }

    /**
     * Verifikasi 2FA terikat pada sesi masuk yang bersangkutan.
     *
     * Tanpa ini, tanda "sudah terverifikasi" bertahan di sesi dan login
     * berikutnya dari sesi yang sama melewati gerbangnya begitu saja.
     */
    #[Test]
    public function login_baru_membatalkan_verifikasi_dua_faktor_sebelumnya(): void
    {
        [$pengguna] = $this->penggunaBer2FA();

        session(['two_factor_verified_user_id' => $pengguna->getKey()]);
        $this->assertSame($pengguna->getKey(), session('two_factor_verified_user_id'));

        Auth::login($pengguna);

        $this->assertNull(session('two_factor_verified_user_id'));
    }

    #[Test]
    public function laporan_kas_kembali_meminta_2fa_setelah_login_ulang(): void
    {
        [$pengguna] = $this->penggunaBer2FA();

        $this->actingAs($pengguna)
            ->withSession(['two_factor_verified_user_id' => $pengguna->getKey()])
            ->get(route('admin.kas.laporan'))
            ->assertOk();

        // Sesi yang sama, tetapi melewati proses login lagi.
        Auth::login($pengguna);

        $this->get(route('admin.kas.laporan'))->assertRedirect(route('two-factor.challenge'));
    }

    /**
     * Akun ber-2FA beserta rahasianya.
     *
     * Dikembalikan sebagai pasangan, bukan lewat parameter by-ref: pemanggilnya
     * selalu membutuhkan keduanya, dan by-ref memaksa variabel yang belum
     * terdefinisi dideklarasikan lebih dulu di setiap tes.
     *
     * @return array{User, string}
     */
    private function penggunaBer2FA(): array
    {
        $rahasia = Totp::buatRahasia();

        $pengguna = User::factory()->create([
            'password' => Hash::make('password'),
            'two_factor_secret' => $rahasia,
            'two_factor_recovery_codes' => collect(range(1, 8))
                ->map(fn (): string => Hash::make(strtoupper(str()->random(5).'-'.str()->random(5))))
                ->all(),
            'two_factor_confirmed_at' => now(),
        ]);

        return [$pengguna, $rahasia];
    }

    private function kodeSaatIni(string $rahasia): string
    {
        return (new ReflectionMethod(Totp::class, 'kode'))->invoke(null, $rahasia, intdiv(time(), 30));
    }
}
