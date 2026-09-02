<?php

namespace Tests\Feature;

use App\Models\JadwalIbadah;
use App\Models\PengumumanPenting;
use App\Models\PermohonanDoa;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
}
