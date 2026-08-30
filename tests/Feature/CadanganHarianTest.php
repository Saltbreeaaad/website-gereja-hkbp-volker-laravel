<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Pengaman "satu cadangan per hari".
 *
 * Sengaja TANPA RefreshDatabase: kasus di sini mengarahkan koneksi default ke
 * mysql supaya perintahnya lolos dari pemeriksaan driver, dan RefreshDatabase
 * yang membungkus tiap tes dalam transaksi akan pecah begitu koneksinya
 * berganti. Tidak ada PDO yang dibuka — pemeriksaan "sudah ada" hanya membaca
 * nama berkas di direktori cadangan, dan mengembalikan hasil sebelum mysqldump
 * sempat dipanggil.
 */
class CadanganHarianTest extends TestCase
{
    private string $direktori;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'mysql',
            'database.connections.mysql.database' => 'uji_cadangan',
        ]);

        $this->direktori = storage_path('app/backups');

        if (! is_dir($this->direktori)) {
            mkdir($this->direktori, 0755, recursive: true);
        }
    }

    private function berkasCadangan(string $tanggal, string $jam = '010101'): string
    {
        return sprintf('%s/uji_cadangan-%s-%s.sql', $this->direktori, $tanggal, $jam);
    }

    #[Test]
    public function melewati_bila_hari_ini_sudah_punya_cadangan(): void
    {
        $tiruan = $this->berkasCadangan(today()->format('Ymd'));
        file_put_contents($tiruan, '-- cadangan tiruan');

        try {
            $this->artisan('hkbp:cadangkan', ['--sekali-sehari' => true])
                ->expectsOutputToContain('sudah ada')
                ->assertExitCode(0);
        } finally {
            unlink($tiruan);
        }
    }

    #[Test]
    public function cadangan_kemarin_tidak_menghalangi_cadangan_hari_ini(): void
    {
        // Pemeriksaannya harus per tanggal, bukan sekadar "ada berkas di sana".
        $kemarin = $this->berkasCadangan(today()->subDay()->format('Ymd'));
        file_put_contents($kemarin, '-- cadangan kemarin');

        try {
            // Lolos pemeriksaan "sudah ada", lalu benar-benar mencoba mysqldump
            // ke basis data yang tidak bisa diakses — gagal, dan itu justru
            // membuktikan ia tidak berhenti lebih awal.
            $this->artisan('hkbp:cadangkan', ['--sekali-sehari' => true])
                ->doesntExpectOutputToContain('sudah ada')
                ->assertExitCode(1);
        } finally {
            unlink($kemarin);
        }
    }

    #[Test]
    public function tanpa_bendera_pengaman_cadangan_manual_tidak_pernah_dilewati(): void
    {
        // Pengurus harus selalu bisa memaksa cadangan, berapa pun yang sudah ada
        // hari itu — misalnya tepat sebelum menjalankan migrasi.
        $tiruan = $this->berkasCadangan(today()->format('Ymd'));
        file_put_contents($tiruan, '-- cadangan tiruan');

        try {
            $this->artisan('hkbp:cadangkan')
                ->doesntExpectOutputToContain('sudah ada')
                ->assertExitCode(1);
        } finally {
            unlink($tiruan);
        }
    }

    #[Test]
    public function berkas_cadangan_basis_data_lain_tidak_ikut_dihitung(): void
    {
        // Satu direktori bisa menampung cadangan dari beberapa basis data.
        $lain = sprintf('%s/basis_data_lain-%s-010101.sql', $this->direktori, today()->format('Ymd'));
        file_put_contents($lain, '-- milik basis data lain');

        try {
            $this->artisan('hkbp:cadangkan', ['--sekali-sehari' => true])
                ->doesntExpectOutputToContain('sudah ada')
                ->assertExitCode(1);
        } finally {
            unlink($lain);
        }
    }
}
