<?php

namespace App\Console\Commands;

use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

/**
 * Cadangkan basis data ke berkas .sql.
 *
 * Checklist produksi menyebut "siapkan cadangan mysqldump terjadwal", tetapi
 * tidak ada apa pun yang menjalankannya — jadi tidak ada cadangan sama sekali.
 * Perintah ini dijadwalkan tiap dini hari di routes/console.php.
 *
 * Kata sandi diserahkan lewat variabel lingkungan MYSQL_PWD, bukan opsi
 * `-p`: opsi baris perintah terlihat oleh siapa pun yang menjalankan `ps` di
 * server yang sama.
 */
class CadangkanBasisData extends Command
{
    protected $signature = 'hkbp:cadangkan
                            {--simpan=14 : Jumlah hari cadangan lama dipertahankan}
                            {--sekali-sehari : Lewati bila hari ini sudah punya cadangan}';

    protected $description = 'Cadangkan basis data ke storage/app/backups dan buang cadangan lama';

    public function handle(): int
    {
        $koneksi = DB::connection();

        if ($koneksi->getDriverName() !== 'mysql') {
            $this->error('Perintah ini hanya untuk MySQL/MariaDB. Koneksi saat ini: '.$koneksi->getDriverName().'.');

            return self::FAILURE;
        }

        $konfigurasi = $koneksi->getConfig();
        $direktori = storage_path('app/backups');

        if (! is_dir($direktori) && ! mkdir($direktori, 0755, recursive: true) && ! is_dir($direktori)) {
            $this->error("Tidak dapat membuat direktori {$direktori}.");

            return self::FAILURE;
        }

        // Dijadwalkan tiap jam, bukan sekali pada dini hari, supaya mesin yang
        // tidak menyala 24 jam — laptop pengembangan, atau server yang sempat
        // mati — tetap mendapat cadangan pada jam pertama ia hidup. Pemeriksaan
        // di bawah yang menjaga agar tetap satu cadangan per hari.
        if ($this->option('sekali-sehari') && $this->sudahAdaCadanganHariIni($direktori, $konfigurasi['database'])) {
            $this->line('Cadangan hari ini sudah ada; dilewati.');

            return self::SUCCESS;
        }

        $berkas = sprintf(
            '%s/%s-%s.sql',
            $direktori,
            $konfigurasi['database'],
            CarbonImmutable::now()->format('Ymd-His'),
        );

        // Percobaan pertama memakai --single-transaction (dump konsisten tanpa
        // mengunci tabel). Bila akunnya tidak berprivilese cukup, dicoba lagi
        // dengan penguncian tabel biasa — lihat catatan di bawah.
        $galat = $this->jalankanDump($konfigurasi, $berkas, konsisten: true);

        if ($galat !== null && $this->kurangPrivilese($galat)) {
            $this->warn('Akun basis data tidak berprivilese RELOAD/FLUSH_TABLES; beralih ke penguncian tabel.');
            $galat = $this->jalankanDump($konfigurasi, $berkas, konsisten: false);
        }

        if ($galat !== null) {
            // Berkas separuh jadi lebih berbahaya daripada tidak ada berkas:
            // ia terlihat seperti cadangan yang sah sampai saat dipulihkan.
            if (is_file($berkas)) {
                unlink($berkas);
            }

            $this->error('mysqldump gagal: '.$galat);

            return self::FAILURE;
        }

        $this->info(sprintf('Cadangan tersimpan: %s (%s).', $berkas, $this->ukuran($berkas)));

        $this->buangYangLama($direktori);

        return self::SUCCESS;
    }

    /**
     * Jalankan mysqldump sekali. Mengembalikan null bila berhasil, atau pesan
     * galatnya bila gagal.
     *
     * `$konsisten` memilih di antara dua cara mendapatkan dump yang utuh:
     *
     * - **true** — `--single-transaction`: membaca seluruh tabel dalam satu
     *   snapshot transaksi, tanpa memblokir siapa pun. Ini yang diinginkan.
     *   Sejak MySQL 8.0.32 mysqldump ikut menjalankan `FLUSH TABLES` di sini,
     *   sehingga menuntut privilese RELOAD atau FLUSH_TABLES — dan itu tidak
     *   diberikan ke akun aplikasi di shared hosting maupun di mesin
     *   pengembangan proyek ini. Tidak ada flag untuk mematikan flush tersebut.
     *
     * - **false** — `--lock-tables` (bawaan mysqldump): mengunci tabel untuk
     *   dibaca selama dump. Hanya butuh privilese LOCK TABLES, yang lazim
     *   dimiliki pemilik basis data. Harganya: penulisan tertahan selama dump
     *   berjalan. Untuk basis data sekecil ini, pada pukul 02.15, itu hitungan
     *   detik.
     *
     * @param  array<string, mixed>  $konfigurasi
     */
    private function jalankanDump(array $konfigurasi, string $berkas, bool $konsisten): ?string
    {
        $proses = new Process(
            [
                'mysqldump',
                '--host='.$konfigurasi['host'],
                '--port='.$konfigurasi['port'],
                '--user='.$konfigurasi['username'],
                ...($konsisten ? ['--single-transaction', '--skip-lock-tables'] : []),
                // Membaca INFORMATION_SCHEMA.FILES menuntut privilese PROCESS,
                // yang juga tidak diberikan ke akun aplikasi biasa.
                '--no-tablespaces',
                '--quick',
                '--default-character-set=utf8mb4',
                '--result-file='.$berkas,
                $konfigurasi['database'],
            ],
            // Kata sandi lewat variabel lingkungan, bukan opsi `-p`: opsi baris
            // perintah terlihat oleh siapa pun yang menjalankan `ps`.
            env: ['MYSQL_PWD' => (string) $konfigurasi['password']],
            timeout: 600,
        );

        try {
            $proses->mustRun();

            return null;
        } catch (ProcessFailedException $e) {
            return trim($proses->getErrorOutput() ?: $e->getMessage());
        }
    }

    /** Galat 1227 = privilese kurang; itulah yang bisa ditolong percobaan ulang. */
    private function kurangPrivilese(string $galat): bool
    {
        return str_contains($galat, '1227')
            || str_contains($galat, 'RELOAD')
            || str_contains($galat, 'FLUSH_TABLES');
    }

    /**
     * Apakah sudah ada cadangan bertanggal hari ini?
     *
     * Diperiksa dari nama berkas (`namadb-YYYYmmdd-HHiiss.sql`), bukan dari
     * waktu ubah berkas: menyalin atau memulihkan direktori cadangan mengubah
     * mtime dan akan membuat cadangan hari itu terlewat.
     */
    private function sudahAdaCadanganHariIni(string $direktori, string $namaBasisData): bool
    {
        $pola = sprintf('%s/%s-%s-*.sql', $direktori, $namaBasisData, CarbonImmutable::today()->format('Ymd'));

        return glob($pola) !== [];
    }

    private function buangYangLama(string $direktori): void
    {
        $batas = CarbonImmutable::now()->subDays(max(1, (int) $this->option('simpan')));
        $dibuang = 0;

        foreach (glob($direktori.'/*.sql') ?: [] as $berkas) {
            if (CarbonImmutable::createFromTimestamp(filemtime($berkas))->lt($batas)) {
                unlink($berkas);
                $dibuang++;
            }
        }

        if ($dibuang > 0) {
            $this->line("{$dibuang} cadangan lama dibuang.");
        }
    }

    private function ukuran(string $berkas): string
    {
        $bita = filesize($berkas) ?: 0;

        return $bita > 1_048_576
            ? round($bita / 1_048_576, 1).' MB'
            : round($bita / 1024).' kB';
    }
}
