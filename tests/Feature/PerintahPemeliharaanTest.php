<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PerintahPemeliharaanTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function perintah_akun_membuat_administrator_pertama(): void
    {
        $this->artisan('hkbp:akun', ['email' => 'pengurus@hkbpvolker.id', '--peran' => User::ADMIN])
            ->expectsQuestion('Nama lengkap', 'Pengurus Gereja')
            ->expectsQuestion('Kata sandi baru (minimal 8 karakter)', 'rahasia-kuat')
            ->expectsQuestion('Ulangi kata sandi', 'rahasia-kuat')
            ->assertExitCode(0);

        $akun = User::firstWhere('email', 'pengurus@hkbpvolker.id');

        $this->assertNotNull($akun);
        $this->assertSame(User::ADMIN, $akun->role);
        // Kata sandi tersimpan sebagai hash, bukan teks polos.
        $this->assertNotSame('rahasia-kuat', $akun->password);
    }

    #[Test]
    public function perintah_akun_mengganti_kata_sandi_akun_yang_sudah_ada(): void
    {
        $akun = User::factory()->create(['email' => 'lama@hkbpvolker.id']);
        $sandiLama = $akun->password;

        $this->artisan('hkbp:akun', ['email' => 'lama@hkbpvolker.id'])
            ->expectsQuestion('Kata sandi baru (minimal 8 karakter)', 'sandi-yang-baru')
            ->expectsQuestion('Ulangi kata sandi', 'sandi-yang-baru')
            ->assertExitCode(0);

        $this->assertNotSame($sandiLama, $akun->fresh()->password);
        $this->assertDatabaseCount('users', 1);
    }

    #[Test]
    public function perintah_akun_menolak_konfirmasi_kata_sandi_yang_tidak_cocok(): void
    {
        $this->artisan('hkbp:akun', ['email' => 'baru@hkbpvolker.id', '--peran' => User::SEKRETARIS])
            ->expectsQuestion('Nama lengkap', 'Sekretaris')
            ->expectsQuestion('Kata sandi baru (minimal 8 karakter)', 'sandi-satu')
            ->expectsQuestion('Ulangi kata sandi', 'sandi-dua')
            ->assertExitCode(1);

        $this->assertDatabaseCount('users', 0);
    }

    #[Test]
    public function perintah_akun_menolak_surel_yang_tidak_sah(): void
    {
        $this->artisan('hkbp:akun', ['email' => 'bukan-surel'])->assertExitCode(1);

        $this->assertDatabaseCount('users', 0);
    }

    #[Test]
    public function administrator_terakhir_tidak_dapat_diturunkan_perannya(): void
    {
        User::factory()->create(['email' => 'satu-satunya@hkbpvolker.id']);

        $this->artisan('hkbp:akun', [
            'email' => 'satu-satunya@hkbpvolker.id',
            '--peran' => User::SEKRETARIS,
        ])
            ->expectsQuestion('Kata sandi baru (minimal 8 karakter)', 'sandi-panjang')
            ->expectsQuestion('Ulangi kata sandi', 'sandi-panjang')
            ->assertExitCode(1);

        $this->assertSame(User::ADMIN, User::firstWhere('email', 'satu-satunya@hkbpvolker.id')->role);
    }

    #[Test]
    public function perintah_cadangan_menolak_koneksi_selain_mysql(): void
    {
        // Pengujian berjalan di SQLite. Perintahnya harus berhenti dengan pesan
        // yang jelas, bukan memanggil mysqldump dan gagal dengan galat proses.
        $this->artisan('hkbp:cadangkan')
            ->expectsOutputToContain('hanya untuk MySQL/MariaDB')
            ->assertExitCode(1);
    }

    #[Test]
    public function cadangan_terjadwal_tiap_jam_dengan_pengaman_sekali_sehari(): void
    {
        $acara = collect(app(Schedule::class)->events())
            ->first(fn ($event) => str_contains($event->command ?? '', 'hkbp:cadangkan'));

        $this->assertNotNull($acara, 'Cadangan harus terdaftar di routes/console.php.');

        // Tiap jam, bukan sekali dini hari: mesin yang tidak menyala 24 jam
        // (laptop, atau server yang sempat mati) akan melewatkan jadwal harian
        // sepenuhnya — penjadwal Laravel tidak menyusul jadwal yang terlewat.
        $this->assertSame('15 * * * *', $acara->expression);

        // Yang menjaga tetap satu cadangan per hari.
        $this->assertStringContainsString('--sekali-sehari', $acara->command);
    }
}
