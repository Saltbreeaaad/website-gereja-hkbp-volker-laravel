<?php

namespace App\Console\Commands;

use App\Support\PencadangMysql;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class PulihkanBasisData extends Command
{
    protected $signature = 'hkbp:pulihkan
                            {berkas : Nama berkas .sql di storage/app/backups}
                            {--force : Lewati konfirmasi interaktif}
                            {--tanpa-potret : Lanjutkan walau potret pra-pemulihan gagal dibuat}';

    protected $description = 'Pulihkan basis data MySQL dari cadangan lokal yang tervalidasi';

    public function handle(): int
    {
        $koneksi = DB::connection();

        if ($koneksi->getDriverName() !== 'mysql') {
            $this->error('Perintah ini hanya untuk MySQL/MariaDB.');

            return self::FAILURE;
        }

        $akarCadangan = config('gereja.direktori_cadangan', storage_path('app/backups'));
        $direktori = realpath($akarCadangan);
        $berkas = realpath($akarCadangan.'/'.basename((string) $this->argument('berkas')));

        if ($direktori === false || $berkas === false || dirname($berkas) !== $direktori || ! str_ends_with($berkas, '.sql')) {
            $this->error('Berkas cadangan tidak sah atau tidak ditemukan.');

            return self::FAILURE;
        }

        if (! $this->option('force') && ! $this->confirm('Pemulihan akan mengganti isi basis data saat ini. Lanjutkan?')) {
            $this->warn('Pemulihan dibatalkan.');

            return self::SUCCESS;
        }

        $konfigurasi = $koneksi->getConfig();
        $proses = $this->prosesImpor($konfigurasi);

        $sql = file_get_contents($berkas);

        if ($sql === false) {
            $this->error('Berkas cadangan tidak dapat dibaca.');

            return self::FAILURE;
        }

        // Dump dari MySQL baru dapat membawa pengaturan replikasi global.
        // Akun aplikasi memang tidak (dan tidak seharusnya) memiliki privilese
        // SUPER; pengaturan ini tidak diperlukan untuk memulihkan satu database.
        $sql = preg_replace(
            '/^SET (?:@MYSQLDUMP_TEMP_LOG_BIN|@@SESSION\.SQL_LOG_BIN|@@GLOBAL\.GTID_PURGED).*;\R?/m',
            '',
            $sql,
        );

        if (! is_string($sql) || strlen($sql) < 1024 || ! str_contains($sql, 'Table structure for table `users`')) {
            $this->error('Isi cadangan tidak lengkap; basis data tidak diubah.');

            return self::FAILURE;
        }

        // Potret keadaan sekarang SEBELUM apa pun dihapus.
        //
        // Langkah berikutnya menjatuhkan seluruh tabel, dan sejak titik itu
        // satu-satunya salinan data hari ini adalah yang dibuat di sini. Impor
        // bisa mati di tengah jalan karena banyak hal yang biasa terjadi —
        // koneksi putus, timeout 600 detik terlampaui, dump yang rusak di
        // bagian belakang sehingga lolos pemeriksaan di atas — dan tanpa
        // potret ini hasilnya adalah basis data kosong tanpa jalan pulang.
        $potret = $this->potretPraPemulihan($konfigurasi);

        if ($potret === null && ! $this->option('tanpa-potret')) {
            $this->error('Potret pra-pemulihan gagal dibuat; basis data tidak diubah.');
            $this->line('Jalankan ulang dengan --tanpa-potret bila Anda memang ingin melanjutkan tanpa jaring pengaman.');

            return self::FAILURE;
        }

        // Impor di atas tabel yang ada meninggalkan tabel baru yang tidak ada
        // di cadangan. Kosongkan seluruh skema agar hasilnya benar-benar sama
        // dengan titik waktu cadangan, bukan campuran dua keadaan.
        $koneksi->getSchemaBuilder()->dropAllTables();

        $proses->setInput($sql);

        try {
            $proses->mustRun();
        } catch (ProcessFailedException $e) {
            $pesan = trim($proses->getErrorOutput() ?: $e->getMessage());
            $this->error('Pemulihan gagal: '.$pesan);

            return $this->kembalikanPotret($konfigurasi, $potret);
        }

        $this->info('Basis data berhasil dipulihkan dari '.basename($berkas).'.');

        if ($potret !== null) {
            $this->line('Potret keadaan sebelumnya disimpan di '.$potret.'.');
        }

        return self::SUCCESS;
    }

    /**
     * Proses `mysql` yang membaca SQL dari stdin.
     *
     * Kata sandi lewat MYSQL_PWD, bukan opsi `-p`: opsi baris perintah terlihat
     * oleh siapa pun yang menjalankan `ps` di server yang sama.
     *
     * @param  array<string, mixed>  $konfigurasi
     */
    private function prosesImpor(array $konfigurasi): Process
    {
        return new Process([
            'mysql',
            '--host='.$konfigurasi['host'],
            '--port='.$konfigurasi['port'],
            '--user='.$konfigurasi['username'],
            '--default-character-set=utf8mb4',
            $konfigurasi['database'],
        ], env: ['MYSQL_PWD' => (string) $konfigurasi['password']], timeout: 600);
    }

    /**
     * Dump keadaan saat ini ke berkas bernama khas, atau null bila gagal.
     *
     * Namanya sengaja tidak mengikuti pola `{db}-{Ymd-His}.sql` milik cadangan
     * terjadwal: `hkbp:cadangkan --sekali-sehari` memeriksa pola itu untuk tahu
     * apakah hari ini sudah dicadangkan, dan potret pemulihan tidak boleh
     * membuatnya melewatkan cadangan harian yang sesungguhnya.
     *
     * @param  array<string, mixed>  $konfigurasi
     */
    private function potretPraPemulihan(array $konfigurasi): ?string
    {
        $direktori = config('gereja.direktori_cadangan', storage_path('app/backups'));

        if (! is_dir($direktori) && ! mkdir($direktori, 0755, recursive: true) && ! is_dir($direktori)) {
            $this->error("Tidak dapat membuat direktori {$direktori}.");

            return null;
        }

        $berkas = sprintf(
            '%s/%s-sebelum-pulih-%s.sql',
            $direktori,
            $konfigurasi['database'],
            CarbonImmutable::now()->format('Ymd-His'),
        );

        $galat = PencadangMysql::jalankanDenganCadanganMode(
            $konfigurasi,
            $berkas,
            fn (string $pesan) => $this->warn($pesan),
        );

        if ($galat !== null) {
            // Berkas separuh jadi lebih berbahaya daripada tidak ada berkas.
            if (is_file($berkas)) {
                unlink($berkas);
            }

            $this->warn('mysqldump untuk potret gagal: '.$galat);

            return null;
        }

        $this->line('Potret pra-pemulihan: '.basename($berkas).'.');

        return $berkas;
    }

    /**
     * Kembalikan basis data ke potret setelah pemulihan gagal.
     *
     * Selalu mengembalikan FAILURE: perintahnya memang tidak berhasil. Yang
     * dibedakan hanyalah apakah pengurus ditinggal dengan basis data yang utuh
     * atau dengan yang kosong — dan bila pemulangannya pun gagal, ia harus
     * diberi tahu di mana berkasnya berada, bukan dibiarkan menebak.
     *
     * @param  array<string, mixed>  $konfigurasi
     */
    private function kembalikanPotret(array $konfigurasi, ?string $potret): int
    {
        if ($potret === null) {
            $this->error('Tidak ada potret untuk dikembalikan; basis data kemungkinan kosong.');

            return self::FAILURE;
        }

        $this->warn('Mengembalikan basis data ke keadaan sebelum pemulihan...');

        $isi = file_get_contents($potret);

        if ($isi === false) {
            $this->error('Potret tidak dapat dibaca. Pulihkan manual dari '.$potret.'.');

            return self::FAILURE;
        }

        $proses = $this->prosesImpor($konfigurasi);
        $proses->setInput($isi);

        try {
            $proses->mustRun();
            $this->info('Basis data dikembalikan ke keadaan sebelum pemulihan.');
        } catch (ProcessFailedException $e) {
            $this->error('Pengembalian juga gagal: '.trim($proses->getErrorOutput() ?: $e->getMessage()));
            $this->error('Pulihkan manual dari '.$potret.'.');
        }

        return self::FAILURE;
    }
}
