<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;

final class PengoptimalGambar
{
    /** Lebar thumbnail responsif; dipakai juga untuk deskriptor srcset. */
    public const LEBAR_THUMBNAIL = 480;

    /**
     * Batas atas piksel walaupun memori seakan-akan tidak terbatas.
     *
     * 80 MP kira-kira sebuah foto 10000x8000 — jauh di atas apa pun yang wajar
     * diunggah pengurus, dan tetap menahan berkas yang memang dirancang besar.
     */
    private const PIKSEL_MAKSIMAL_MUTLAK = 80_000_000;

    /**
     * Ubah gambar raster ke WebP, batasi dimensi, dan buat thumbnail.
     * Gagal memproses berarti berkas asli tetap dipakai dan tidak dihapus.
     */
    public static function optimalkan(string $path, int $lebarMaksimal, int $lebarThumbnail = self::LEBAR_THUMBNAIL): string
    {
        $disk = Storage::disk('public');

        if (! $disk->exists($path) || ! function_exists('imagecreatefromstring')) {
            return $path;
        }

        $isi = $disk->get($path);

        if (! self::muatDenganAman($isi)) {
            return $path;
        }

        $sumber = @imagecreatefromstring($isi);

        if ($sumber === false) {
            return $path;
        }

        try {
            // Selalu berakhiran '.webp' hasil penggabungan, jadi tidak ada
            // kemungkinan string kosong yang perlu dijaga di sini.
            $tujuan = preg_replace('/\.[^.\/]+$/', '', $path).'.webp';

            [$utama, $lebarUtama] = self::ubahUkuran($sumber, $lebarMaksimal);
            [$thumbnail] = self::ubahUkuran($sumber, min($lebarThumbnail, $lebarUtama));

            $isiUtama = self::keWebp($utama, 82);
            $isiThumbnail = self::keWebp($thumbnail, 78);

            imagedestroy($utama);
            imagedestroy($thumbnail);

            if ($isiUtama === null || $isiThumbnail === null) {
                return $path;
            }

            $pathThumbnail = self::pathThumbnail($tujuan);
            $disk->put($tujuan, $isiUtama);
            $disk->put($pathThumbnail, $isiThumbnail);

            if ($tujuan !== $path) {
                $disk->delete($path);
            }

            return $tujuan;
        } finally {
            imagedestroy($sumber);
        }
    }

    public static function pathThumbnail(string $path): string
    {
        return preg_replace('/\.webp$/i', '', $path).'-thumb.webp';
    }

    /**
     * Apakah gambar ini aman di-decode dengan memori yang tersisa?
     *
     * Ini pemeriksaan yang tidak boleh dilewati. `imagecreatefromstring()`
     * mengalokasikan 4 bita per piksel sekaligus, dan kehabisan memori di sana
     * adalah FATAL ERROR, bukan exception: tanda `@` tidak meredamnya, blok
     * `try/finally` tidak pernah berjalan, dan proses PHP-nya mati begitu saja.
     * Jadi satu-satunya tempat untuk menahannya adalah sebelum decode dimulai.
     *
     * Ancamannya bukan hanya berkas yang sengaja dibuat jahat. PNG 9000x9000
     * berisi warna polos hanya 0,25 MB di disk — lolos jauh di bawah batas 4 MB
     * pada formulir unggah — tetapi menuntut ~324 MB saat dibuka. Foto hasil
     * pindai beresolusi tinggi sampai ke angka yang sama tanpa ada yang berniat
     * buruk. Barisnya pun sudah tersimpan saat proses mati (hook-nya `saved`),
     * dan `hkbp:optimalkan-gambar` yang menyapu semua foto akan menabrak baris
     * itu berulang kali sampai seseorang menghapusnya lewat SQL.
     *
     * `getimagesizefromstring()` hanya membaca kepala berkas, jadi ia aman
     * dipakai untuk menanyakan ukuran tanpa mengalokasikan satu piksel pun.
     */
    private static function muatDenganAman(string $isi): bool
    {
        $ukuran = @getimagesizefromstring($isi);

        if ($ukuran === false) {
            return false;
        }

        [$lebar, $tinggi] = $ukuran;

        if ($lebar < 1 || $tinggi < 1) {
            return false;
        }

        return $lebar * $tinggi <= self::batasPiksel();
    }

    /**
     * Berapa piksel yang muat di sisa memory_limit.
     *
     * Diturunkan dari batas nyata proses, bukan angka tetap: 128M adalah batas
     * yang lazim di hosting bersama, sementara server yang lebih lapang boleh
     * memproses foto yang lebih besar tanpa harus menyunting kode.
     */
    private static function batasPiksel(): int
    {
        // Nilai eksplisit di konfigurasi menang: sebagian hosting melaporkan
        // memory_limit yang tidak mencerminkan jatah sebenarnya.
        $ditetapkan = config('gereja.maksimal_piksel_gambar');

        if (is_int($ditetapkan) && $ditetapkan > 0) {
            return $ditetapkan;
        }

        $batas = self::memoryLimitBita();

        if ($batas === null) {
            return self::PIKSEL_MAKSIMAL_MUTLAK;
        }

        // Sisakan 30% untuk framework, salinan hasil resize, dan buffer WebP;
        // yang dihitung di sini hanyalah bitmap sumbernya (4 bita per piksel).
        $tersisa = $batas - memory_get_usage(true);
        $muat = (int) ($tersisa * 0.7 / 4);

        return max(1_000_000, min($muat, self::PIKSEL_MAKSIMAL_MUTLAK));
    }

    /** Nilai memory_limit dalam bita, atau null bila tidak dibatasi. */
    private static function memoryLimitBita(): ?int
    {
        $nilai = trim((string) ini_get('memory_limit'));

        if ($nilai === '' || $nilai === '-1') {
            return null;
        }

        $angka = (int) $nilai;

        return match (strtolower(substr($nilai, -1))) {
            'g' => $angka * 1024 ** 3,
            'm' => $angka * 1024 ** 2,
            'k' => $angka * 1024,
            default => $angka,
        };
    }

    /** @return array{\GdImage, int} */
    private static function ubahUkuran(\GdImage $sumber, int $lebarMaksimal): array
    {
        $lebar = imagesx($sumber);
        $tinggi = imagesy($sumber);
        $lebarBaru = min($lebar, max(1, $lebarMaksimal));
        $tinggiBaru = max(1, (int) round($tinggi * ($lebarBaru / $lebar)));
        $hasil = imagecreatetruecolor($lebarBaru, $tinggiBaru);

        imagealphablending($hasil, false);
        imagesavealpha($hasil, true);
        imagecopyresampled($hasil, $sumber, 0, 0, 0, 0, $lebarBaru, $tinggiBaru, $lebar, $tinggi);

        return [$hasil, $lebarBaru];
    }

    private static function keWebp(\GdImage $gambar, int $kualitas): ?string
    {
        ob_start();
        $berhasil = imagewebp($gambar, null, $kualitas);
        $isi = ob_get_clean();

        return $berhasil && is_string($isi) ? $isi : null;
    }
}
