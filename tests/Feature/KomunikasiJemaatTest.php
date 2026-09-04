<?php

namespace Tests\Feature;

use App\Models\JadwalIbadah;
use App\Models\PengumumanPenting;
use App\Models\PermohonanDoa;
use App\Models\User;
use App\Notifications\PermohonanDoaMasuk;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class KomunikasiJemaatTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function pengumuman_aktif_tampil_di_beranda(): void
    {
        PengumumanPenting::query()->create(['judul' => 'Ibadah Khusus', 'isi' => 'Dilaksanakan Sabtu ini.', 'aktif' => true]);
        PengumumanPenting::query()->create(['judul' => 'Arsip', 'isi' => 'Tidak ditampilkan.', 'aktif' => false]);

        $this->get(route('home'))->assertOk()->assertSee('Ibadah Khusus')->assertDontSee('Arsip');
    }

    #[Test]
    public function jemaat_dapat_mengirim_pokok_doa_tanpa_identitas(): void
    {
        $this->post(route('doa.store'), ['isi' => 'Mohon doakan kesehatan keluarga kami.'])
            ->assertRedirect(route('doa'));

        $this->assertDatabaseHas('permohonan_doas', ['isi' => 'Mohon doakan kesehatan keluarga kami.', 'status' => PermohonanDoa::BARU]);
    }

    #[Test]
    public function agenda_dan_berkas_kalender_tersedia(): void
    {
        JadwalIbadah::query()->create(['nama_ibadah' => 'Ibadah Minggu', 'tanggal' => today()->addWeek(), 'waktu' => '09:00', 'keterangan' => 'Ibadah bersama']);

        $this->get(route('agenda'))->assertOk()->assertSee('Ibadah Minggu');
        $this->get(route('agenda.kalender'))->assertOk()->assertHeader('content-type', 'text/calendar; charset=UTF-8')->assertSee('BEGIN:VCALENDAR', false);
    }

    /**
     * Pokok doa yang masuk harus terlihat tanpa seseorang kebetulan membuka
     * panel — tetapi hanya oleh yang memang boleh membacanya.
     */
    #[Test]
    public function pokok_doa_memberi_tahu_administrator_dan_sekretaris_saja(): void
    {
        Notification::fake();

        $admin = User::factory()->create();
        $sekretaris = User::factory()->sekretaris()->create();
        $bendahara = User::factory()->bendahara()->create();

        $this->post(route('doa.store'), ['isi' => 'Mohon doakan kesehatan keluarga kami.'])
            ->assertSessionHasNoErrors();

        Notification::assertSentTo([$admin, $sekretaris], PermohonanDoaMasuk::class);
        Notification::assertNotSentTo($bendahara, PermohonanDoaMasuk::class);
    }

    /**
     * Halaman /doa menjanjikan formulir privat. Baris notifikasi adalah
     * salinan kedua yang tidak dijaga policy mana pun — ia ikut ke cadangan
     * dan muncul di lonceng yang sering terbuka saat orang lain melihat layar.
     */
    #[Test]
    public function notifikasi_doa_tidak_membawa_isi_maupun_nama_pengirim(): void
    {
        $permohonan = PermohonanDoa::query()->create([
            'nama' => 'Marisi Simanjuntak',
            'isi' => 'Mohon doakan operasi ibu saya minggu depan.',
            'status' => PermohonanDoa::BARU,
        ]);

        $isi = json_encode((new PermohonanDoaMasuk($permohonan))->toArray(User::factory()->create()));

        $this->assertIsString($isi);
        $this->assertStringNotContainsString('operasi ibu saya', $isi);
        $this->assertStringNotContainsString('Marisi', $isi);
    }
}
