<?php

namespace Tests\Feature;

use App\Models\Galeri;
use App\Models\KasGereja;
use App\Models\User;
use App\Models\WartaJemaat;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PeranPengurusTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function ketiga_peran_boleh_masuk_panel(): void
    {
        foreach ([User::ADMIN, User::BENDAHARA, User::SEKRETARIS] as $peran) {
            $this->actingAs(User::factory()->create(['role' => $peran]))
                ->get('/admin')
                ->assertSuccessful();
        }
    }

    #[Test]
    public function peran_yang_tidak_dikenal_ditolak_dari_panel(): void
    {
        // Baris yang perannya rusak (salah ketik di SQL, sisa migrasi setengah
        // jalan) harus ditolak, bukan diperlakukan sebagai pengurus penuh.
        $this->actingAs(User::factory()->create(['role' => 'entah-apa']))
            ->get('/admin')
            ->assertForbidden();
    }

    #[Test]
    public function bendahara_mengelola_kas_tetapi_tidak_mengubah_warta(): void
    {
        $bendahara = User::factory()->bendahara()->create();

        $this->assertTrue(Gate::forUser($bendahara)->allows('create', KasGereja::class));
        $this->assertTrue(Gate::forUser($bendahara)->allows('update', KasGereja::factory()->create()));

        $this->assertFalse(Gate::forUser($bendahara)->allows('create', WartaJemaat::class));
        $this->assertFalse(Gate::forUser($bendahara)->allows('update', WartaJemaat::factory()->create()));
    }

    #[Test]
    public function sekretaris_mengelola_isi_tetapi_tidak_mengubah_kas(): void
    {
        $sekretaris = User::factory()->sekretaris()->create();

        $this->assertTrue(Gate::forUser($sekretaris)->allows('create', WartaJemaat::class));
        $this->assertTrue(Gate::forUser($sekretaris)->allows('create', Galeri::class));

        $this->assertFalse(Gate::forUser($sekretaris)->allows('create', KasGereja::class));
        $this->assertFalse(Gate::forUser($sekretaris)->allows('update', KasGereja::factory()->create()));
    }

    #[Test]
    public function semua_peran_tetap_boleh_melihat_seluruh_modul(): void
    {
        // Menyembunyikan menu dari peran yang tidak boleh mengubahnya hanya
        // memindahkan pertanyaannya ke grup WhatsApp pengurus.
        foreach ([User::BENDAHARA, User::SEKRETARIS] as $peran) {
            $pengurus = User::factory()->create(['role' => $peran]);

            $this->assertTrue(Gate::forUser($pengurus)->allows('viewAny', KasGereja::class));
            $this->assertTrue(Gate::forUser($pengurus)->allows('viewAny', WartaJemaat::class));
        }
    }

    #[Test]
    public function hanya_administrator_yang_boleh_menghapus(): void
    {
        $warta = WartaJemaat::factory()->create();

        $this->assertTrue(Gate::forUser(User::factory()->create())->allows('delete', $warta));
        $this->assertFalse(Gate::forUser(User::factory()->sekretaris()->create())->allows('delete', $warta));
        $this->assertFalse(Gate::forUser(User::factory()->bendahara()->create())->allows('delete', $warta));
    }

    #[Test]
    public function administrator_boleh_membuat_sekretaris_boleh_menghapus_isinya_sendiri_tidak(): void
    {
        $sekretaris = User::factory()->sekretaris()->create();
        $galeri = Galeri::factory()->create();

        $this->assertTrue(Gate::forUser($sekretaris)->allows('update', $galeri));
        $this->assertFalse(Gate::forUser($sekretaris)->allows('delete', $galeri));
    }

    #[Test]
    public function daftar_akun_hanya_terlihat_oleh_administrator(): void
    {
        $this->assertTrue(Gate::forUser(User::factory()->create())->allows('viewAny', User::class));
        $this->assertFalse(Gate::forUser(User::factory()->bendahara()->create())->allows('viewAny', User::class));
        $this->assertFalse(Gate::forUser(User::factory()->sekretaris()->create())->allows('viewAny', User::class));
    }

    #[Test]
    public function administrator_tidak_dapat_menghapus_akunnya_sendiri(): void
    {
        $admin = User::factory()->create();
        $lain = User::factory()->create();

        $this->assertFalse(Gate::forUser($admin)->allows('delete', $admin));
        $this->assertTrue(Gate::forUser($admin)->allows('delete', $lain));
    }

    #[Test]
    public function seeder_data_contoh_menolak_berjalan_di_produksi(): void
    {
        // Akun `password` dan baris kas palsu tidak boleh pernah sampai ke
        // basis data sungguhan.
        $this->app['env'] = 'production';

        // --force melewati konfirmasi bawaan Laravel untuk perintah destruktif
        // di produksi, sehingga yang benar-benar diuji adalah pagar di seeder.
        $this->artisan('db:seed', ['--force' => true])->assertExitCode(0);

        $this->assertDatabaseCount('users', 0);
        $this->assertDatabaseCount('kas_gereja', 0);
    }
}
