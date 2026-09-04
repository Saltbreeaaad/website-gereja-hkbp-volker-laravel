<?php

namespace Tests\Feature;

use App\Filament\Resources\PenggunaanGerejaResource\Pages\ListPenggunaanGereja;
use App\Models\PenggunaanGereja;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TindakLanjutPermohonanTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function perubahan_status_memiliki_riwayat_dan_pelaku(): void
    {
        $admin = User::factory()->create();
        $permohonan = PenggunaanGereja::factory()->create();

        $this->actingAs($admin);
        $permohonan->update([
            'status' => PenggunaanGereja::DISETUJUI,
            'catatan_admin' => 'Silakan digunakan.',
        ]);

        $riwayat = $permohonan->riwayatStatus()->firstOrFail();
        $this->assertSame(PenggunaanGereja::MENUNGGU, $riwayat->status_lama);
        $this->assertSame(PenggunaanGereja::DISETUJUI, $riwayat->status_baru);
        $this->assertTrue($riwayat->user->is($admin));
    }

    #[Test]
    public function kalender_hanya_memuat_jadwal_disetujui_yang_belum_lewat(): void
    {
        PenggunaanGereja::factory()->disetujui()->create([
            'nama_kegiatan' => 'Latihan Koor',
            'tanggal' => today()->addDay(),
        ]);
        PenggunaanGereja::factory()->create(['nama_kegiatan' => 'Masih Menunggu']);
        PenggunaanGereja::factory()->disetujui()->create([
            'nama_kegiatan' => 'Sudah Lewat',
            'tanggal' => today()->subDay(),
        ]);

        $this->get(route('penggunaan-gereja.kalender'))
            ->assertOk()
            ->assertHeader('Content-Type', 'text/calendar; charset=UTF-8')
            ->assertSee('BEGIN:VCALENDAR')
            ->assertSee('Latihan Koor')
            ->assertDontSee('Masih Menunggu')
            ->assertDontSee('Sudah Lewat');
    }

    #[Test]
    public function permohonan_lama_ditutup_otomatis(): void
    {
        $permohonan = PenggunaanGereja::factory()->create([
            'tanggal' => today()->addMonth(),
            'created_at' => now()->subDays(31),
        ]);

        $this->artisan('hkbp:kedaluwarsakan-permohonan')
            ->expectsOutputToContain('1 permohonan')
            ->assertExitCode(0);

        $this->assertSame(PenggunaanGereja::DITOLAK, $permohonan->fresh()->status);
    }

    #[Test]
    public function nomor_indonesia_mendapat_tautan_whatsapp_status(): void
    {
        $permohonan = PenggunaanGereja::factory()->create(['kontak' => '0812-3456-7890']);

        $this->assertStringStartsWith('https://wa.me/6281234567890', $permohonan->urlWhatsAppStatus());
        $this->assertStringContainsString(rawurlencode($permohonan->kode), $permohonan->urlWhatsAppStatus());
    }

    /**
     * Keputusan pengurus tidak sampai ke pemohon dengan sendirinya.
     *
     * Tombol "Kirim Status" sudah ada sejak lama, tetapi ia menunggu diingat.
     * Yang dijaga di sini: begitu status berubah, ajakan mengabarinya muncul
     * di layar pengurus saat itu juga.
     */
    #[Test]
    public function menyetujui_permohonan_mengingatkan_pengurus_mengabari_pemohon(): void
    {
        $admin = User::factory()->create();
        $permohonan = PenggunaanGereja::factory()->create(['kontak' => '081234567890']);

        Livewire::actingAs($admin)
            ->test(ListPenggunaanGereja::class)
            ->callTableAction('setujui', $permohonan)
            ->assertNotified('Beri tahu pemohon');

        $this->assertSame(PenggunaanGereja::DISETUJUI, $permohonan->fresh()->status);
    }

    #[Test]
    public function menolak_permohonan_juga_mengingatkan(): void
    {
        $admin = User::factory()->create();
        $permohonan = PenggunaanGereja::factory()->create(['kontak' => '081234567890']);

        Livewire::actingAs($admin)
            ->test(ListPenggunaanGereja::class)
            ->callTableAction('tolak', $permohonan)
            ->assertNotified('Beri tahu pemohon');

        $this->assertSame(PenggunaanGereja::DITOLAK, $permohonan->fresh()->status);
    }

    /**
     * Kontak yang bukan nomor telepon tidak boleh lewat diam-diam: pemohon itu
     * justru yang paling mungkin tidak pernah dikabari sama sekali.
     */
    #[Test]
    public function kontak_yang_tidak_dapat_di_whatsapp_diberitahukan_ke_pengurus(): void
    {
        $admin = User::factory()->create();
        $permohonan = PenggunaanGereja::factory()->create(['kontak' => 'budi@example.test']);

        Livewire::actingAs($admin)
            ->test(ListPenggunaanGereja::class)
            ->callTableAction('setujui', $permohonan)
            ->assertNotified('Pemohon belum dikabari');
    }
}
