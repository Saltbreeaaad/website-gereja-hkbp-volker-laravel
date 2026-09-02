<?php

namespace Tests\Feature;

use App\Filament\Resources\UserResource;
use App\Filament\Resources\UserResource\Pages\EditUser;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Mengganti surel dan kata sandi pengurus lewat panel admin.
 *
 * Ini prosedur yang didokumentasikan di README, jadi ia perlu dijaga tes —
 * termasuk perilaku yang paling mudah salah dipahami: kolom kata sandi yang
 * dibiarkan kosong saat menyunting berarti "jangan diubah", bukan "kosongkan
 * kata sandinya".
 */
class GantiAkunPengurusTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => User::ADMIN]);
    }

    #[Test]
    public function administrator_dapat_mengganti_surel_pengurus(): void
    {
        $admin = $this->admin();
        $sasaran = User::factory()->create(['email' => 'lama@hkbpvolker.test']);

        Livewire::actingAs($admin)
            ->test(EditUser::class, ['record' => $sasaran->getKey()])
            ->fillForm(['email' => 'baru@hkbpvolker.id'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('baru@hkbpvolker.id', $sasaran->fresh()->email);
    }

    #[Test]
    public function administrator_dapat_mengganti_kata_sandi(): void
    {
        $admin = $this->admin();
        $sasaran = User::factory()->create();
        $sandiLama = $sasaran->password;

        Livewire::actingAs($admin)
            ->test(EditUser::class, ['record' => $sasaran->getKey()])
            ->fillForm(['password' => 'kata-sandi-baru-2026'])
            ->call('save')
            ->assertHasNoFormErrors();

        $baru = $sasaran->fresh();

        $this->assertNotSame($sandiLama, $baru->password);
        $this->assertTrue(Hash::check('kata-sandi-baru-2026', $baru->password));
    }

    #[Test]
    public function kata_sandi_dibiarkan_kosong_berarti_tidak_diubah(): void
    {
        $admin = $this->admin();
        $sasaran = User::factory()->create(['name' => 'Nama Lama']);
        $sandiLama = $sasaran->password;

        Livewire::actingAs($admin)
            ->test(EditUser::class, ['record' => $sasaran->getKey()])
            ->fillForm(['name' => 'Nama Baru', 'password' => ''])
            ->call('save')
            ->assertHasNoFormErrors();

        $baru = $sasaran->fresh();

        $this->assertSame('Nama Baru', $baru->name);
        $this->assertSame($sandiLama, $baru->password, 'Kata sandi tidak boleh ikut berubah.');
    }

    #[Test]
    public function surel_yang_sudah_dipakai_akun_lain_ditolak(): void
    {
        $admin = $this->admin();
        User::factory()->create(['email' => 'sudah@hkbpvolker.test']);
        $sasaran = User::factory()->create(['email' => 'saya@hkbpvolker.test']);

        Livewire::actingAs($admin)
            ->test(EditUser::class, ['record' => $sasaran->getKey()])
            ->fillForm(['email' => 'sudah@hkbpvolker.test'])
            ->call('save')
            ->assertHasFormErrors(['email']);

        $this->assertSame('saya@hkbpvolker.test', $sasaran->fresh()->email);
    }

    #[Test]
    public function kata_sandi_terlalu_pendek_ditolak(): void
    {
        $admin = $this->admin();
        $sasaran = User::factory()->create();

        Livewire::actingAs($admin)
            ->test(EditUser::class, ['record' => $sasaran->getKey()])
            ->fillForm(['password' => 'pendek'])
            ->call('save')
            ->assertHasFormErrors(['password']);
    }

    #[Test]
    public function bendahara_tidak_dapat_membuka_halaman_akun(): void
    {
        $bendahara = User::factory()->bendahara()->create();

        $this->actingAs($bendahara)
            ->get(UserResource::getUrl('index'))
            ->assertForbidden();
    }
}
