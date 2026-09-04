<?php

namespace Tests\Feature;

use App\Filament\Pages\KeamananAkun;
use App\Filament\Resources;
use App\Models\Galeri;
use App\Models\JadwalIbadah;
use App\Models\KasGereja;
use App\Models\LogAktivitas;
use App\Models\Parhalado;
use App\Models\PenggunaanGereja;
use App\Models\PengumumanPenting;
use App\Models\PeriodeKas;
use App\Models\PermohonanDoa;
use App\Models\Renungan;
use App\Models\User;
use App\Models\WartaJemaat;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Setiap halaman panel harus benar-benar dirender.
 *
 * Tidak ada satu pun tes yang pernah membuka halaman Filament sebelumnya, dan
 * itu membiarkan seluruh kelas kegagalan lolos: ext-intl yang tidak terpasang
 * membuat SETIAP tabel Filament melempar RuntimeException dari Number::format()
 * — dipakai blade indikator seleksi, jadi bukan hanya kolom uang — sementara
 * tes yang ada semuanya hijau. Peninjauan kode tidak bisa menangkap yang
 * seperti itu; hanya menjalankannya yang bisa.
 */
class RenderHalamanPanelTest extends TestCase
{
    use RefreshDatabase;

    /** @return array<string, array{class-string}> */
    public static function halamanDaftar(): array
    {
        return [
            'galeri' => [Resources\GaleriResource\Pages\ListGaleri::class],
            'jadwal ibadah' => [Resources\JadwalIbadahResource\Pages\ListJadwalIbadah::class],
            'kas gereja' => [Resources\KasGerejaResource\Pages\ListKasGereja::class],
            'log aktivitas' => [Resources\LogAktivitasResource\Pages\ListLogAktivitas::class],
            'parhalado' => [Resources\ParhaladoResource\Pages\ListParhalado::class],
            'penggunaan gereja' => [Resources\PenggunaanGerejaResource\Pages\ListPenggunaanGereja::class],
            'pengumuman penting' => [Resources\PengumumanPentingResource\Pages\ListPengumumanPenting::class],
            'periode kas' => [Resources\PeriodeKasResource\Pages\ListPeriodeKas::class],
            'permohonan doa' => [Resources\PermohonanDoaResource\Pages\ListPermohonanDoa::class],
            'renungan' => [Resources\RenunganResource\Pages\ListRenungan::class],
            'pengguna' => [Resources\UserResource\Pages\ListUser::class],
            'warta jemaat' => [Resources\WartaJemaatResource\Pages\ListWartaJemaat::class],
        ];
    }

    /** @return array<string, array{class-string}> */
    public static function halamanBuat(): array
    {
        return [
            'galeri' => [Resources\GaleriResource\Pages\CreateGaleri::class],
            'jadwal ibadah' => [Resources\JadwalIbadahResource\Pages\CreateJadwalIbadah::class],
            'kas gereja' => [Resources\KasGerejaResource\Pages\CreateKasGereja::class],
            'parhalado' => [Resources\ParhaladoResource\Pages\CreateParhalado::class],
            'penggunaan gereja' => [Resources\PenggunaanGerejaResource\Pages\CreatePenggunaanGereja::class],
            'pengumuman penting' => [Resources\PengumumanPentingResource\Pages\CreatePengumumanPenting::class],
            'periode kas' => [Resources\PeriodeKasResource\Pages\CreatePeriodeKas::class],
            'renungan' => [Resources\RenunganResource\Pages\CreateRenungan::class],
            'pengguna' => [Resources\UserResource\Pages\CreateUser::class],
            'warta jemaat' => [Resources\WartaJemaatResource\Pages\CreateWartaJemaat::class],
        ];
    }

    /** @param  class-string  $halaman */
    #[Test]
    #[DataProvider('halamanDaftar')]
    public function halaman_daftar_dapat_dirender(string $halaman): void
    {
        $this->actingAs(User::factory()->create());
        $this->isiContohData();

        Livewire::test($halaman)->assertOk();
    }

    /** @param  class-string  $halaman */
    #[Test]
    #[DataProvider('halamanBuat')]
    public function halaman_buat_dapat_dirender(string $halaman): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test($halaman)->assertOk();
    }

    #[Test]
    public function halaman_sunting_dapat_dirender(): void
    {
        $this->actingAs(User::factory()->create());
        $this->isiContohData();

        $pasangan = [
            [Resources\GaleriResource\Pages\EditGaleri::class, Galeri::query()->first()],
            [Resources\JadwalIbadahResource\Pages\EditJadwalIbadah::class, JadwalIbadah::query()->first()],
            [Resources\KasGerejaResource\Pages\EditKasGereja::class, KasGereja::query()->first()],
            [Resources\ParhaladoResource\Pages\EditParhalado::class, Parhalado::query()->first()],
            [Resources\PenggunaanGerejaResource\Pages\EditPenggunaanGereja::class, PenggunaanGereja::query()->first()],
            [Resources\PengumumanPentingResource\Pages\EditPengumumanPenting::class, PengumumanPenting::query()->first()],
            [Resources\PeriodeKasResource\Pages\EditPeriodeKas::class, PeriodeKas::query()->first()],
            [Resources\PermohonanDoaResource\Pages\EditPermohonanDoa::class, PermohonanDoa::query()->first()],
            [Resources\RenunganResource\Pages\EditRenungan::class, Renungan::query()->first()],
            [Resources\WartaJemaatResource\Pages\EditWartaJemaat::class, WartaJemaat::query()->first()],
        ];

        foreach ($pasangan as [$halaman, $record]) {
            Livewire::test($halaman, ['record' => $record->getRouteKey()])
                ->assertOk();
        }
    }

    #[Test]
    public function halaman_lihat_log_aktivitas_dapat_dirender(): void
    {
        $this->actingAs(User::factory()->create());
        $this->isiContohData();

        Livewire::test(
            Resources\LogAktivitasResource\Pages\ViewLogAktivitas::class,
            ['record' => LogAktivitas::query()->first()->getRouteKey()],
        )->assertOk();
    }

    #[Test]
    public function halaman_keamanan_akun_dapat_dirender(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(KeamananAkun::class)->assertOk();
    }

    #[Test]
    public function dasbor_dengan_seluruh_widget_dapat_dirender(): void
    {
        $this->isiContohData();

        $this->actingAs(User::factory()->create())
            ->get('/admin')
            ->assertOk();
    }

    /** Satu baris per modul supaya tabel, badge, dan kolom benar-benar terisi. */
    private function isiContohData(): void
    {
        Galeri::factory()->create();
        JadwalIbadah::factory()->create();
        KasGereja::factory()->create();
        Parhalado::factory()->create();
        PenggunaanGereja::factory()->create();
        Renungan::factory()->create();
        WartaJemaat::factory()->create();

        PengumumanPenting::query()->create(['judul' => 'Uji', 'isi' => 'Isi pengumuman.', 'aktif' => true]);
        PeriodeKas::query()->create(['periode' => today()->format('Y-m'), 'saldo_awal' => 0]);
        PermohonanDoa::query()->create(['isi' => 'Mohon didoakan keluarga kami.', 'status' => PermohonanDoa::BARU]);
    }
}
