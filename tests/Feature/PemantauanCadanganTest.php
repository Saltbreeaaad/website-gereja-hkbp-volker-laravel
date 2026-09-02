<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PemantauanCadanganTest extends TestCase
{
    use RefreshDatabase;

    private string $direktori;

    protected function setUp(): void
    {
        parent::setUp();
        $this->direktori = sys_get_temp_dir().'/hkbp-backup-test-'.bin2hex(random_bytes(4));
        mkdir($this->direktori);
        config(['gereja.direktori_cadangan' => $this->direktori]);
    }

    protected function tearDown(): void
    {
        foreach (glob($this->direktori.'/*') ?: [] as $berkas) {
            unlink($berkas);
        }
        rmdir($this->direktori);
        parent::tearDown();
    }

    #[Test]
    public function cadangan_baru_dan_berukuran_wajar_dinyatakan_sehat(): void
    {
        file_put_contents($this->direktori.'/sehat.sql', str_repeat('-', 2048));

        $this->artisan('hkbp:periksa-cadangan')
            ->expectsOutputToContain('Cadangan sehat')
            ->assertExitCode(0);
    }

    #[Test]
    public function cadangan_lama_memberi_notifikasi_kepada_admin(): void
    {
        $admin = User::factory()->create();
        $berkas = $this->direktori.'/lama.sql';
        file_put_contents($berkas, str_repeat('-', 2048));
        touch($berkas, now()->subDays(3)->timestamp);

        $this->artisan('hkbp:periksa-cadangan')->assertExitCode(1);

        $this->assertDatabaseHas('notifications', ['notifiable_id' => $admin->id]);
    }
}
