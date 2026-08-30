<?php

namespace Tests\Feature;

use App\Models\PenggunaanGereja;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PenggunaanGerejaTest extends TestCase
{
    use RefreshDatabase;

    /** @return array<string, string> */
    private function permohonan(array $ubah = []): array
    {
        return array_merge([
            'nama_kegiatan' => 'Latihan Koor Remaja',
            'nama_pemohon' => 'Budi Sitorus',
            'kontak' => '081234567890',
            'tanggal' => today()->addDays(7)->toDateString(),
            'waktu_mulai' => '18:00',
            'waktu_selesai' => '20:00',
            'keterangan' => 'Persiapan Natal',
        ], $ubah);
    }

    #[Test]
    public function permohonan_valid_tersimpan_dengan_status_menunggu(): void
    {
        $this->post(route('penggunaan-gereja.store'), $this->permohonan())
            ->assertSessionHas('success');

        $this->assertDatabaseHas('penggunaan_gerejas', [
            'nama_kegiatan' => 'Latihan Koor Remaja',
            'status' => PenggunaanGereja::MENUNGGU,
        ]);

        // Pemohon diantar ke halaman penelusuran dengan kodenya sudah terisi,
        // bukan dikembalikan ke formulir tanpa pegangan apa pun.
        $permohonan = PenggunaanGereja::firstWhere('nama_kegiatan', 'Latihan Koor Remaja');

        $this->post(route('penggunaan-gereja.store'), $this->permohonan(['nama_kegiatan' => 'Latihan Koor Ama']))
            ->assertRedirect(route('penggunaan-gereja.lacak', [
                'kode' => PenggunaanGereja::firstWhere('nama_kegiatan', 'Latihan Koor Ama')->kode,
            ]));

        $this->assertNotNull($permohonan->kode);
    }

    #[Test]
    public function jam_disimpan_dalam_format_yang_konsisten(): void
    {
        $this->post(route('penggunaan-gereja.store'), $this->permohonan());

        $this->assertDatabaseHas('penggunaan_gerejas', [
            'waktu_mulai' => '18:00:00',
            'waktu_selesai' => '20:00:00',
        ]);
    }

    #[Test]
    public function menolak_permohonan_yang_bentrok_dengan_jadwal_disetujui(): void
    {
        PenggunaanGereja::factory()->disetujui()->create([
            'tanggal' => today()->addDays(7),
            'waktu_mulai' => '17:00',
            'waktu_selesai' => '19:00',
        ]);

        $this->post(route('penggunaan-gereja.store'), $this->permohonan())
            ->assertSessionHasErrors('waktu_mulai');

        $this->assertDatabaseCount('penggunaan_gerejas', 1);
    }

    #[Test]
    public function jadwal_menunggu_tidak_mengunci_slot(): void
    {
        PenggunaanGereja::factory()->create([
            'tanggal' => today()->addDays(7),
            'waktu_mulai' => '17:00',
            'waktu_selesai' => '19:00',
        ]);

        $this->post(route('penggunaan-gereja.store'), $this->permohonan())
            ->assertSessionHasNoErrors();

        $this->assertDatabaseCount('penggunaan_gerejas', 2);
    }

    #[Test]
    public function jadwal_yang_bersentuhan_ujungnya_bukan_bentrok(): void
    {
        PenggunaanGereja::factory()->disetujui()->create([
            'tanggal' => today()->addDays(7),
            'waktu_mulai' => '16:00',
            'waktu_selesai' => '18:00',
        ]);

        // Mulai tepat saat kegiatan lain selesai — masih boleh.
        $this->post(route('penggunaan-gereja.store'), $this->permohonan())
            ->assertSessionHasNoErrors();
    }

    #[Test]
    public function bentrok_hanya_dihitung_pada_tanggal_yang_sama(): void
    {
        PenggunaanGereja::factory()->disetujui()->create([
            'tanggal' => today()->addDays(8),
            'waktu_mulai' => '18:00',
            'waktu_selesai' => '20:00',
        ]);

        $this->post(route('penggunaan-gereja.store'), $this->permohonan())
            ->assertSessionHasNoErrors();
    }

    #[Test]
    public function menolak_tanggal_yang_sudah_lewat(): void
    {
        $this->post(route('penggunaan-gereja.store'), $this->permohonan([
            'tanggal' => today()->subDay()->toDateString(),
        ]))->assertSessionHasErrors('tanggal');
    }

    #[Test]
    public function menolak_jam_selesai_sebelum_jam_mulai(): void
    {
        $this->post(route('penggunaan-gereja.store'), $this->permohonan([
            'waktu_mulai' => '20:00',
            'waktu_selesai' => '18:00',
        ]))->assertSessionHasErrors('waktu_selesai');
    }

    #[Test]
    public function menolak_kontak_yang_bukan_nomor_telepon(): void
    {
        $this->post(route('penggunaan-gereja.store'), $this->permohonan([
            'kontak' => 'bukan nomor telepon',
        ]))->assertSessionHasErrors('kontak');
    }

    #[Test]
    public function menolak_isian_wajib_yang_kosong(): void
    {
        $this->post(route('penggunaan-gereja.store'), [])
            ->assertSessionHasErrors([
                'nama_kegiatan', 'nama_pemohon', 'kontak',
                'tanggal', 'waktu_mulai', 'waktu_selesai',
            ]);
    }

    #[Test]
    public function daftar_publik_menyembunyikan_permohonan_yang_ditolak(): void
    {
        PenggunaanGereja::factory()->disetujui()->create(['nama_kegiatan' => 'Kegiatan Disetujui']);
        PenggunaanGereja::factory()->create(['nama_kegiatan' => 'Kegiatan Menunggu']);
        PenggunaanGereja::factory()->ditolak()->create(['nama_kegiatan' => 'Kegiatan Ditolak']);

        $this->get(route('penggunaan-gereja'))
            ->assertOk()
            ->assertSee('Kegiatan Disetujui')
            // Slot yang masih menunggu tetap tampil — tanpa teksnya — supaya
            // pemohon berikutnya tetap bisa menghindari bentrok.
            ->assertSee('Permohonan menunggu konfirmasi')
            ->assertDontSee('Kegiatan Ditolak');
    }

    /**
     * Formulir permohonan terbuka tanpa login. Kalau teks bebasnya langsung
     * tayang, siapa pun di internet bisa menerbitkan tulisan apa pun di halaman
     * gereja — jadi teks itu baru boleh muncul setelah pengurus menyetujuinya.
     */
    #[Test]
    public function teks_permohonan_yang_belum_disetujui_tidak_ditayangkan(): void
    {
        PenggunaanGereja::factory()->create([
            'nama_kegiatan' => 'Iklan Judi Online',
            'nama_pemohon' => 'Pengirim Spam',
            'keterangan' => 'Kunjungi situs kami sekarang juga',
        ]);

        $this->get(route('penggunaan-gereja'))
            ->assertOk()
            ->assertSee('Permohonan menunggu konfirmasi')
            ->assertDontSee('Iklan Judi Online')
            ->assertDontSee('Pengirim Spam')
            ->assertDontSee('Kunjungi situs kami sekarang juga');
    }

    #[Test]
    public function teks_permohonan_yang_disetujui_ditayangkan(): void
    {
        PenggunaanGereja::factory()->disetujui()->create([
            'nama_kegiatan' => 'Latihan Koor Paduan Suara',
            'nama_pemohon' => 'H. Tampubolon',
            'keterangan' => 'Persiapan ibadah Natal',
        ]);

        $this->get(route('penggunaan-gereja'))
            ->assertOk()
            ->assertSee('Latihan Koor Paduan Suara')
            ->assertSee('H. Tampubolon')
            ->assertSee('Persiapan ibadah Natal');
    }

    #[Test]
    public function daftar_publik_menyembunyikan_jadwal_yang_sudah_lewat(): void
    {
        PenggunaanGereja::factory()->disetujui()->create([
            'nama_kegiatan' => 'Kegiatan Kemarin',
            'tanggal' => today()->subDay(),
        ]);

        $this->get(route('penggunaan-gereja'))
            ->assertOk()
            ->assertDontSee('Kegiatan Kemarin');
    }

    #[Test]
    public function permohonan_dibatasi_lajunya(): void
    {
        for ($i = 0; $i < 6; $i++) {
            $this->post(route('penggunaan-gereja.store'), $this->permohonan([
                'nama_kegiatan' => 'Kegiatan '.$i,
                'waktu_mulai' => sprintf('%02d:00', 8 + $i),
                'waktu_selesai' => sprintf('%02d:30', 8 + $i),
            ]));
        }

        $this->post(route('penggunaan-gereja.store'), $this->permohonan())
            ->assertStatus(429);
    }
}
