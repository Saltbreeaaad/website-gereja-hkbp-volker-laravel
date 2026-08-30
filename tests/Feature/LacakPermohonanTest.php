<?php

namespace Tests\Feature;

use App\Models\PenggunaanGereja;
use App\Models\User;
use App\Notifications\PermohonanGedungMasuk;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class LacakPermohonanTest extends TestCase
{
    use RefreshDatabase;

    private function permohonan(array $ubah = []): array
    {
        return array_merge([
            'nama_kegiatan' => 'Latihan Koor Remaja',
            'nama_pemohon' => 'Budi Sitorus',
            'kontak' => '081234567890',
            'tanggal' => today()->addDays(7)->toDateString(),
            'waktu_mulai' => '18:00',
            'waktu_selesai' => '20:00',
        ], $ubah);
    }

    #[Test]
    public function setiap_permohonan_mendapat_kode_unik_berawalan_wg(): void
    {
        $a = PenggunaanGereja::factory()->create();
        $b = PenggunaanGereja::factory()->create();

        $this->assertMatchesRegularExpression('/^WG-[23456789A-Z]{8}$/', $a->kode);
        $this->assertNotSame($a->kode, $b->kode);
    }

    #[Test]
    public function kode_tidak_memakai_karakter_yang_mudah_tertukar(): void
    {
        // 0/O dan 1/I/L tertukar saat kode dibacakan lewat telepon atau
        // disalin ulang dari catatan tangan.
        foreach (range(1, 30) as $ignored) {
            $this->assertDoesNotMatchRegularExpression(
                '/[01OIL]/',
                substr(PenggunaanGereja::factory()->create()->kode, 3)
            );
        }
    }

    #[Test]
    public function pemohon_dapat_melihat_status_permohonannya(): void
    {
        $permohonan = PenggunaanGereja::factory()->create([
            'nama_kegiatan' => 'Rapat Naposobulung',
            'status' => PenggunaanGereja::MENUNGGU,
        ]);

        $this->get(route('penggunaan-gereja.lacak', ['kode' => $permohonan->kode]))
            ->assertOk()
            ->assertSee('Rapat Naposobulung')
            ->assertSee($permohonan->kode)
            ->assertSee('menunggu peninjauan pengurus');
    }

    #[Test]
    public function alasan_penolakan_sampai_ke_pemohon(): void
    {
        // Inti perbaikan ini: `catatan_admin` dulu diisi pengurus di panel tetapi
        // tidak pernah ditampilkan ke mana pun.
        $permohonan = PenggunaanGereja::factory()->create([
            'status' => PenggunaanGereja::DITOLAK,
            'catatan_admin' => 'Bentrok dengan Sekolah Minggu gabungan.',
        ]);

        $this->get(route('penggunaan-gereja.lacak', ['kode' => $permohonan->kode]))
            ->assertOk()
            ->assertSee('Ditolak')
            ->assertSee('Catatan dari pengurus')
            ->assertSee('Bentrok dengan Sekolah Minggu gabungan.');
    }

    #[Test]
    public function kode_dimaafkan_dari_huruf_kecil_spasi_dan_tanda_hubung(): void
    {
        $permohonan = PenggunaanGereja::factory()->create(['kode' => 'WG-A2B3C4D5']);

        foreach (['wg-a2b3c4d5', 'WGA2B3C4D5', ' a2b3c4d5 ', 'A2B3-C4D5'] as $ketikan) {
            $this->get(route('penggunaan-gereja.lacak', ['kode' => $ketikan]))
                ->assertOk()
                ->assertSee($permohonan->nama_kegiatan);
        }
    }

    #[Test]
    public function kode_yang_tidak_ada_memberi_pesan_jelas_bukan_galat(): void
    {
        $this->get(route('penggunaan-gereja.lacak', ['kode' => 'WG-ZZZZZZZZ']))
            ->assertOk()
            ->assertSee('tidak ditemukan')
            ->assertSee(config('gereja.telepon'));
    }

    #[Test]
    public function halaman_tanpa_kode_hanya_menampilkan_formulir(): void
    {
        $this->get(route('penggunaan-gereja.lacak'))
            ->assertOk()
            ->assertSee('Kode penelusuran')
            ->assertDontSee('tidak ditemukan');
    }

    #[Test]
    public function kontak_pemohon_tidak_pernah_ditampilkan_di_halaman_publik(): void
    {
        // Halaman ini hanya butuh kode untuk dibuka, jadi ia tidak boleh
        // membocorkan nomor telepon pemohon kepada siapa pun yang memegangnya.
        $permohonan = PenggunaanGereja::factory()->create(['kontak' => '081298765432']);

        $this->get(route('penggunaan-gereja.lacak', ['kode' => $permohonan->kode]))
            ->assertOk()
            ->assertDontSee('081298765432');
    }

    #[Test]
    public function pengurus_diberi_tahu_saat_permohonan_masuk(): void
    {
        Notification::fake();

        $pengurus = User::factory()->count(2)->create();

        $this->post(route('penggunaan-gereja.store'), $this->permohonan());

        Notification::assertSentTo($pengurus, PermohonanGedungMasuk::class);
    }

    #[Test]
    public function penelusuran_dibatasi_lajunya_agar_kode_tidak_bisa_ditebak_beruntun(): void
    {
        foreach (range(1, 20) as $ignored) {
            $this->get(route('penggunaan-gereja.lacak', ['kode' => 'WG-AAAAAAAA']))->assertOk();
        }

        $this->get(route('penggunaan-gereja.lacak', ['kode' => 'WG-AAAAAAAA']))
            ->assertStatus(429);
    }
}
