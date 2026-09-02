<?php

namespace Tests\Feature;

use App\Models\KasGereja;
use App\Models\LogAktivitas;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AktivitasAdminTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function perubahan_data_mencatat_pelaku_dan_nilai_lama_baru(): void
    {
        $admin = User::factory()->create();
        $kas = KasGereja::factory()->create(['keterangan' => 'Lama']);

        $this->actingAs($admin);
        $kas->update(['keterangan' => 'Baru']);

        $log = LogAktivitas::query()->where('aksi', 'diubah')->latest('id')->firstOrFail();

        $this->assertTrue($log->user->is($admin));
        $this->assertSame('Lama', $log->perubahan['lama']['keterangan']);
        $this->assertSame('Baru', $log->perubahan['baru']['keterangan']);
    }

    #[Test]
    public function kata_sandi_tidak_pernah_disimpan_di_log(): void
    {
        $user = User::factory()->create();
        $user->update(['password' => 'sandi-baru-yang-panjang']);

        $seluruhLog = LogAktivitas::query()->get()->pluck('perubahan')->toJson();

        $this->assertStringNotContainsString('password', $seluruhLog);
        $this->assertStringNotContainsString('sandi-baru-yang-panjang', $seluruhLog);
    }

    #[Test]
    public function mengganti_kata_sandi_mengakhiri_sesi_lain(): void
    {
        config(['session.driver' => 'database']);

        $user = User::factory()->create();

        DB::table('sessions')->insert([
            'id' => 'sesi-lama',
            'user_id' => $user->id,
            'payload' => '{}',
            'last_activity' => now()->timestamp,
        ]);

        $user->update(['password' => 'sandi-baru-yang-panjang']);

        $this->assertDatabaseMissing('sessions', ['id' => 'sesi-lama']);
    }

    #[Test]
    public function honeypot_menolak_pengiriman_bot(): void
    {
        $this->post(route('penggunaan-gereja.store'), [
            'website' => 'https://spam.example',
            'nama_kegiatan' => 'Spam',
            'nama_pemohon' => 'Bot',
            'kontak' => '081234567890',
            'tanggal' => today()->addDay()->toDateString(),
            'waktu_mulai' => '10:00',
            'waktu_selesai' => '11:00',
        ])->assertSessionHasErrors('website');

        $this->assertDatabaseCount('penggunaan_gerejas', 0);
    }
}
