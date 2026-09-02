<?php

namespace Tests\Feature;

use App\Models\PenggunaanGereja;
use App\Models\User;
use App\Notifications\PermohonanGedungMasuk;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Notifikasi pengurus tidak boleh ditulis di dalam permintaan pengunjung.
 *
 * Formulir publik memicunya, dan menulis satu baris per pengurus sebelum
 * membalas pemohon adalah pekerjaan yang tidak ia tunggu. Konsekuensinya:
 * antreannya harus benar-benar punya pekerja, dan notifikasinya harus selamat
 * melewati serialisasi.
 */
class NotifikasiAntreanTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function permohonan_gedung_menaruh_notifikasi_di_antrean_bukan_menulisnya_langsung(): void
    {
        config(['queue.default' => 'database']);
        User::factory()->count(3)->create();

        $this->post(route('penggunaan-gereja.store'), $this->permohonanValid())
            ->assertSessionHasNoErrors();

        // Belum ada notifikasi tertulis: yang ada baru pekerjaannya.
        $this->assertSame(0, DB::table('notifications')->count());
        $this->assertGreaterThan(0, DB::table('jobs')->count());
    }

    #[Test]
    public function pekerja_antrean_mengirimkan_notifikasinya(): void
    {
        config(['queue.default' => 'database']);
        User::factory()->count(3)->create();

        $this->post(route('penggunaan-gereja.store'), $this->permohonanValid())
            ->assertSessionHasNoErrors();

        Artisan::call('queue:work', ['--stop-when-empty' => true, '--tries' => 1]);

        $this->assertSame(0, DB::table('failed_jobs')->count(), Artisan::output());
        $this->assertSame(3, DB::table('notifications')->count());
    }

    /**
     * Hanya yang berwenang meninjau yang diberi tahu.
     *
     * Bendahara tidak dapat menyetujui permohonan gedung (lihat
     * PenggunaanGerejaPolicy), jadi memenuhi loncengnya dengan permohonan yang
     * tidak bisa ia tindak lanjuti hanya membuat notifikasi jadi kebisingan
     * yang lama-lama diabaikan semua orang.
     */
    #[Test]
    public function hanya_peran_peninjau_yang_menerima_notifikasi(): void
    {
        config(['queue.default' => 'database']);

        $admin = User::factory()->create();
        $sekretaris = User::factory()->sekretaris()->create();
        $bendahara = User::factory()->bendahara()->create();

        $this->post(route('penggunaan-gereja.store'), $this->permohonanValid())
            ->assertSessionHasNoErrors();

        Artisan::call('queue:work', ['--stop-when-empty' => true, '--tries' => 1]);

        $this->assertSame(1, $admin->notifications()->count());
        $this->assertSame(1, $sekretaris->notifications()->count());
        $this->assertSame(0, $bendahara->notifications()->count());
    }

    /**
     * `SerializesModels` menyimpan model sebagai id lalu memuatnya kembali.
     * Properti permohonan di sini `private readonly` dan dipromosikan lewat
     * konstruktor — bentuk yang paling mudah patah saat dibangun ulang.
     */
    #[Test]
    public function notifikasi_selamat_melewati_serialisasi(): void
    {
        $permohonan = PenggunaanGereja::factory()->create(['nama_kegiatan' => 'Latihan Koor']);

        $bangkit = unserialize(serialize(new PermohonanGedungMasuk($permohonan)));

        $isi = $bangkit->toArray(User::factory()->create());

        $this->assertStringContainsString('Latihan Koor', json_encode($isi));
    }

    /** @return array<string, string> */
    private function permohonanValid(): array
    {
        return [
            'nama_kegiatan' => 'Latihan Koor',
            'nama_pemohon' => 'Budi',
            'kontak' => '081234567890',
            'tanggal' => today()->addDays(3)->toDateString(),
            'waktu_mulai' => '18:00',
            'waktu_selesai' => '20:00',
        ];
    }
}
