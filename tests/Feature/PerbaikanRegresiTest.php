<?php

namespace Tests\Feature;

use App\Models\KasGereja;
use App\Models\PenggunaanGereja;
use App\Models\PengumumanPenting;
use App\Models\PeriodeKas;
use App\Models\User;
use App\Models\WartaJemaat;
use App\Support\Totp;
use Illuminate\Cache\ArrayStore;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\Test;
use ReflectionMethod;
use Tests\TestCase;

/**
 * Regresi untuk bug yang ditemukan saat peninjauan kode.
 *
 * Tiap kasus di sini benar-benar gagal sebelum perbaikannya dipasang.
 */
class PerbaikanRegresiTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Peramban manusia tetap mengirim input umpan `website` dalam keadaan
     * kosong, dan `validated()` ikut mengembalikannya. Meneruskannya ke
     * `create()` melanggar `$fillable`; dengan Model::shouldBeStrict() —
     * menyala di semua env selain produksi — formulirnya membalas 500.
     */
    #[Test]
    public function formulir_doa_menerima_kiriman_dengan_honeypot_kosong(): void
    {
        Model::preventSilentlyDiscardingAttributes(true);

        $this->post(route('doa.store'), [
            'website' => '',
            'nama' => 'Budi',
            'kontak' => '',
            'isi' => 'Mohon didoakan untuk kesehatan keluarga kami.',
        ])->assertRedirect(route('doa'));

        $this->assertDatabaseHas('permohonan_doas', ['nama' => 'Budi']);
    }

    #[Test]
    public function formulir_penggunaan_gereja_menerima_kiriman_dengan_honeypot_kosong(): void
    {
        Model::preventSilentlyDiscardingAttributes(true);

        $this->post(route('penggunaan-gereja.store'), [
            'website' => '',
            'nama_kegiatan' => 'Latihan Koor',
            'nama_pemohon' => 'Budi',
            'kontak' => '081234567890',
            'tanggal' => today()->addDays(3)->toDateString(),
            'waktu_mulai' => '18:00',
            'waktu_selesai' => '20:00',
            'keterangan' => '',
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('penggunaan_gerejas', ['nama_kegiatan' => 'Latihan Koor']);
    }

    /** Umpannya tetap harus menahan bot yang benar-benar mengisinya. */
    #[Test]
    public function honeypot_terisi_tetap_ditolak(): void
    {
        $this->post(route('doa.store'), [
            'website' => 'https://spam.example',
            'isi' => 'Mohon didoakan untuk kesehatan keluarga kami.',
        ])->assertSessionHasErrors('website');

        $this->assertDatabaseCount('permohonan_doas', 0);
    }

    /**
     * Laporan kas berada di luar panel Filament, jadi middleware panel —
     * termasuk gerbang 2FA — tidak ikut berlaku kecuali disebut ulang.
     */
    #[Test]
    public function laporan_kas_menolak_sesi_yang_belum_melewati_dua_faktor(): void
    {
        $admin = $this->adminBer2FA();

        $this->actingAs($admin)->get(route('admin.kas.laporan'))
            ->assertRedirect(route('two-factor.challenge'));

        $this->actingAs($admin)->get(route('admin.kas.csv'))
            ->assertRedirect(route('two-factor.challenge'));
    }

    #[Test]
    public function laporan_kas_mengantar_tamu_ke_halaman_masuk(): void
    {
        $this->get(route('admin.kas.laporan'))
            ->assertRedirect(route('filament.admin.auth.login'));
    }

    #[Test]
    public function laporan_kas_terbuka_setelah_dua_faktor_terverifikasi(): void
    {
        $admin = $this->adminBer2FA();

        $this->actingAs($admin)
            ->withSession(['two_factor_verified_user_id' => $admin->getKey()])
            ->get(route('admin.kas.laporan'))
            ->assertOk();
    }

    /**
     * Menggeser transaksi keluar dari bulan yang sudah ditutup mengubah total
     * bulan itu, dan pemeriksaan yang hanya melihat tanggal baru melewatkannya.
     */
    #[Test]
    public function transaksi_tidak_dapat_digeser_keluar_dari_periode_tertutup(): void
    {
        $transaksi = KasGereja::query()->create([
            'tanggal' => '2026-01-15',
            'jenis' => 'Pemasukan',
            'keterangan' => 'Persembahan',
            'nominal' => 500000,
        ]);

        PeriodeKas::query()->create(['periode' => '2026-01', 'saldo_awal' => 0, 'ditutup_at' => now()]);

        $this->expectException(ValidationException::class);

        $transaksi->update(['tanggal' => '2026-02-15']);
    }

    #[Test]
    public function transaksi_di_periode_terbuka_tetap_dapat_disunting(): void
    {
        $transaksi = KasGereja::query()->create([
            'tanggal' => '2026-03-10',
            'jenis' => 'Pemasukan',
            'keterangan' => 'Persembahan',
            'nominal' => 500000,
        ]);

        $transaksi->update(['nominal' => 750000]);

        $this->assertDatabaseHas('kas_gerejas', ['id' => $transaksi->id, 'nominal' => 750000]);
    }

    /**
     * Kunci cache yang memuat kata pencarian bebas membuat siapa pun dapat
     * menulis baris cache sebanyak yang ia mau.
     */
    #[Test]
    public function hasil_pencarian_tidak_menulis_baris_cache(): void
    {
        config(['gereja.cache_konten' => true]);
        WartaJemaat::factory()->create(['judul' => 'Warta Minggu Pertama']);
        Cache::flush();

        $this->get(route('warta', ['q' => 'acak-'.uniqid()]))->assertOk();

        $this->assertSame(0, $this->jumlahKunciDaftarWarta(), 'Pencarian tidak boleh meninggalkan kunci cache.');

        $this->get(route('warta'))->assertOk();

        $this->assertGreaterThan(0, $this->jumlahKunciDaftarWarta(), 'Daftar tanpa pencarian tetap harus di-cache.');
    }

    #[Test]
    public function nomor_halaman_dibatasi_agar_tidak_menjadi_kunci_cache_tanpa_batas(): void
    {
        config(['gereja.cache_konten' => true]);
        WartaJemaat::factory()->create();
        Cache::flush();

        $this->get(route('warta', ['page' => 999_999]))->assertOk();
        $this->get(route('warta', ['page' => 888_888]))->assertOk();

        // Keduanya dipangkas ke batas yang sama, jadi hanya satu kunci lahir.
        $this->assertSame(1, $this->jumlahKunciDaftarWarta());
    }

    #[Test]
    public function filter_bulan_di_luar_daftar_diabaikan(): void
    {
        config(['gereja.cache_konten' => true]);
        PenggunaanGereja::factory()->create(['status' => PenggunaanGereja::DISETUJUI, 'tanggal' => today()->addWeek()]);
        Cache::flush();

        $this->get(route('penggunaan-gereja', ['bulan' => '1999-01']))->assertOk();
        $this->get(route('penggunaan-gereja', ['bulan' => '2088-12']))->assertOk();

        $this->assertSame(1, $this->jumlahKunciCache('penggunaan-gereja:'));
    }

    /** Rahasia kosong berarti akun tanpa 2FA — kodenya tidak boleh pernah cocok. */
    #[Test]
    public function totp_menolak_rahasia_kosong(): void
    {
        $kode = $this->kodeSaatIni(Totp::buatRahasia());

        $this->assertFalse(Totp::verifikasi('', $kode));
        $this->assertFalse(Totp::verifikasi('!!!!', $kode));
        $this->assertFalse(Totp::verifikasi('', '000000'));
    }

    #[Test]
    public function totp_menerima_kode_dari_rahasia_yang_sah(): void
    {
        $rahasia = Totp::buatRahasia();

        $this->assertTrue(Totp::verifikasi($rahasia, $this->kodeSaatIni($rahasia)));
    }

    /** Pengumuman tampil di beranda publik; bendahara tidak boleh mengubahnya. */
    #[Test]
    public function pengumuman_hanya_dapat_diubah_peran_yang_berwenang(): void
    {
        $bendahara = User::factory()->bendahara()->create();
        $sekretaris = User::factory()->sekretaris()->create();
        $admin = User::factory()->create();

        $this->assertFalse($bendahara->can('create', PengumumanPenting::class));
        $this->assertFalse($bendahara->can('update', PengumumanPenting::class));
        $this->assertTrue($bendahara->can('viewAny', PengumumanPenting::class));

        $this->assertTrue($sekretaris->can('update', PengumumanPenting::class));
        $this->assertFalse($sekretaris->can('delete', PengumumanPenting::class));

        $this->assertTrue($admin->can('delete', PengumumanPenting::class));
    }

    private function adminBer2FA(): User
    {
        return User::factory()->create([
            'two_factor_secret' => Totp::buatRahasia(),
            'two_factor_confirmed_at' => now(),
        ]);
    }

    /** Hanya kunci daftar berpaginasi; `warta:tahun` bukan bagian dari hitungan. */
    private function jumlahKunciDaftarWarta(): int
    {
        return $this->jumlahKunciCache('warta:semua:hal-');
    }

    /**
     * Store `array` dipakai saat pengujian, jadi isinya dapat dibaca langsung.
     *
     * Diperiksa tipenya, bukan diasumsikan: hanya ArrayStore yang punya `all()`,
     * dan bila phpunit.xml suatu saat berpindah store, tes ini harus berhenti
     * dengan pesan yang jelas alih-alih memanggil metode yang tidak ada.
     */
    private function jumlahKunciCache(string $awalanKonten): int
    {
        $store = Cache::getStore();

        $this->assertInstanceOf(ArrayStore::class, $store, 'Tes ini mengandalkan cache store array.');

        return collect(array_keys($store->all()))
            ->filter(fn (string $kunci): bool => str_contains($kunci, ':'.$awalanKonten))
            ->count();
    }

    private function kodeSaatIni(string $rahasia): string
    {
        return (new ReflectionMethod(Totp::class, 'kode'))->invoke(null, $rahasia, intdiv(time(), 30));
    }
}
