<?php

namespace App\Support;

use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

/**
 * Pembungkus mysqldump.
 *
 * Dipakai dua perintah: `hkbp:cadangkan` untuk cadangan terjadwal, dan
 * `hkbp:pulihkan` untuk memotret keadaan saat ini sebelum menimpanya. Keduanya
 * membutuhkan daftar flag yang persis sama — dan tiap flag di sini ada karena
 * satu kegagalan nyata di hosting berprivilese terbatas, jadi menyalinnya ke
 * dua tempat adalah cara tercepat agar salah satunya tertinggal saat diperbaiki.
 */
final class PencadangMysql
{
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
     *   berjalan. Untuk basis data sekecil ini itu hitungan detik.
     *
     * @param  array<string, mixed>  $konfigurasi
     */
    public static function jalankan(array $konfigurasi, string $berkas, bool $konsisten): ?string
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
                // Cadangan satu basis data tidak memerlukan posisi replikasi.
                // Tanpa ini, dump tidak bisa dipulihkan akun aplikasi biasa
                // karena SET @@GLOBAL.GTID_PURGED membutuhkan privilese SUPER.
                '--set-gtid-purged=OFF',
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

    /**
     * Dump dengan percobaan ulang: bila gagal karena privilese kurang, ulangi
     * tanpa `--single-transaction`.
     *
     * @param  array<string, mixed>  $konfigurasi
     * @param  null|callable(string): void  $catat  Dipanggil bila beralih mode.
     */
    public static function jalankanDenganCadanganMode(array $konfigurasi, string $berkas, ?callable $catat = null): ?string
    {
        $galat = self::jalankan($konfigurasi, $berkas, konsisten: true);

        if ($galat !== null && self::kurangPrivilese($galat)) {
            if ($catat !== null) {
                $catat('Akun basis data tidak berprivilese RELOAD/FLUSH_TABLES; beralih ke penguncian tabel.');
            }

            $galat = self::jalankan($konfigurasi, $berkas, konsisten: false);
        }

        return $galat;
    }

    /** Galat 1227 = privilese kurang; itulah yang bisa ditolong percobaan ulang. */
    public static function kurangPrivilese(string $galat): bool
    {
        return str_contains($galat, '1227')
            || str_contains($galat, 'RELOAD')
            || str_contains($galat, 'FLUSH_TABLES');
    }
}
