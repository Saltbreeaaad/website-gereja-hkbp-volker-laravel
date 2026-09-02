<?php

namespace App\Console\Commands;

use App\Support\PencadangMysql;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

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
        $direktori = config('gereja.direktori_cadangan', storage_path('app/backups'));

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
        // dengan penguncian tabel biasa — lihat App\Support\PencadangMysql.
        $galat = PencadangMysql::jalankanDenganCadanganMode(
            $konfigurasi,
            $berkas,
            fn (string $pesan) => $this->warn($pesan),
        );

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

        $this->salinKeLuar($berkas);
        $this->buangYangLama($direktori);

        return self::SUCCESS;
    }

    /**
     * Salin cadangan ke penyimpanan kedua, bila dikonfigurasi.
     *
     * Cadangan yang tinggal satu disk dengan basis datanya bukan cadangan
     * terhadap kegagalan disk — keduanya hilang bersamaan. Tujuannya dinyatakan
     * lewat CADANGAN_DISK, memakai disk Laravel mana pun yang sudah ada di
     * config/filesystems.php (S3, sebuah mount jaringan, atau apa pun yang
     * dipasang gereja). Tanpa konfigurasi itu langkah ini tidak melakukan
     * apa-apa, jadi pemasangan yang ada sekarang tidak berubah perilakunya.
     *
     * Kegagalan menyalin sengaja tidak membuat perintahnya gagal: cadangan
     * lokalnya sudah ada dan sah, dan menandai seluruh proses gagal hanya akan
     * membuat hkbp:periksa-cadangan ikut berisik soal hal yang berbeda.
     */
    private function salinKeLuar(string $berkas): void
    {
        $disk = config('gereja.cadangan_disk');

        if (blank($disk)) {
            return;
        }

        try {
            $aliran = fopen($berkas, 'rb');

            if ($aliran === false) {
                $this->warn('Cadangan tidak dapat dibaca untuk disalin ke luar.');

                return;
            }

            $tujuan = trim((string) config('gereja.cadangan_disk_direktori', 'cadangan-basis-data'), '/');
            $berhasil = Storage::disk($disk)->put($tujuan.'/'.basename($berkas), $aliran);

            if (is_resource($aliran)) {
                fclose($aliran);
            }

            $berhasil
                ? $this->info("Salinan luar terkirim ke disk [{$disk}].")
                : $this->warn("Salinan luar ke disk [{$disk}] ditolak.");
        } catch (\Throwable $e) {
            $this->warn('Salinan luar gagal: '.$e->getMessage());
        }
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
