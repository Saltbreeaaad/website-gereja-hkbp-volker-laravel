<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Filament menolak dengan 403 setiap pengguna yang modelnya tidak
 * mengimplementasikan FilamentUser, kecuali saat APP_ENV=local. Tes ini
 * berjalan di APP_ENV=testing, jadi ia benar-benar menjalankan jalur yang
 * sama dengan produksi — bukan kelonggaran mode lokal.
 */
class AksesPanelAdminTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function pengurus_yang_sudah_masuk_dapat_membuka_panel_admin(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/admin')
            ->assertSuccessful();
    }

    #[Test]
    public function tamu_diarahkan_ke_halaman_masuk(): void
    {
        $this->get('/admin')->assertRedirect('/admin/login');
    }

    #[Test]
    public function panel_admin_tetap_terbuka_saat_lingkungan_produksi(): void
    {
        // Inti dari perbaikan ini: sebelum User mengimplementasikan FilamentUser,
        // baris ini menghasilkan 403 dan mengunci pengurus keluar dari situsnya
        // sendiri begitu APP_ENV diubah ke production.
        config(['app.env' => 'production']);

        $this->actingAs(User::factory()->create())
            ->get('/admin')
            ->assertSuccessful();
    }

    #[Test]
    public function halaman_admin_tidak_diindeks_mesin_pencari(): void
    {
        $this->get('/robots.txt')
            ->assertOk()
            ->assertSee('Disallow: /admin');
    }
}
