<?php

/*
|--------------------------------------------------------------------------
| Polyfill mb_strimwidth() & mb_strcut()
|--------------------------------------------------------------------------
|
| Dimuat lewat autoload `files` di composer.json, sebelum apa pun berjalan.
|
| `symfony/polyfill-mbstring` sudah menutup hampir seluruh fungsi mb_* saat
| ekstensi mbstring tidak tersedia, tetapi dua fungsi ini dinyatakan sendiri
| olehnya sebagai "Not implemented" (lihat daftar di kepala berkas
| vendor/symfony/polyfill-mbstring/Mbstring.php). Sialnya justru
| mb_strimwidth() yang dipakai Str::limit() Laravel — sehingga beranda, yang
| memanggilnya lewat Renungan::ringkasan(), membalas 500 sementara halaman
| lain tetap normal. mb_strcut() dipakai Monolog, CommonMark, dan perender
| halaman galat Laravel.
|
| Keduanya dijaga function_exists(): begitu ekstensi mbstring tersedia,
| berkas ini tidak melakukan apa pun. Jadi ini aman ikut ke produksi dan
| tidak menyembunyikan apa pun dari mesin yang sehat.
|
*/

if (! function_exists('mb_strimwidth')) {
    /**
     * Potong string ke lebar tampilan tertentu.
     *
     * Lebar dihitung dengan mb_strwidth(): karakter Asia Timur berlebar penuh
     * dihitung 2, sisanya 1. Karena setiap karakter berlebar minimal 1, cukup
     * mengambil sebanyak $width karakter pertama sebagai kandidat — tidak perlu
     * memecah seluruh string yang bisa saja panjang.
     *
     * Catatan: ValueError yang dilempar PHP asli ketika $trim_marker lebih lebar
     * daripada $width tidak ditiru; di sini penanda cukup dipangkas.
     */
    function mb_strimwidth(string $string, int $start, int $width, string $trim_marker = '', ?string $encoding = null): string
    {
        $encoding ??= mb_internal_encoding();

        $string = mb_substr($string, $start, null, $encoding);
        $lebarTotal = mb_strwidth($string, $encoding);

        // $width negatif berarti "sekian satuan lebar dari ujung".
        if ($width < 0) {
            $width = max(0, $lebarTotal + $width);
        }

        if ($lebarTotal <= $width) {
            return $string;
        }

        $sasaran = max(0, $width - mb_strwidth($trim_marker, $encoding));

        $hasil = '';
        $lebar = 0;

        foreach (mb_str_split(mb_substr($string, 0, $sasaran, $encoding), 1, $encoding) as $karakter) {
            $lebarKarakter = mb_strwidth($karakter, $encoding);

            if ($lebar + $lebarKarakter > $sasaran) {
                break;
            }

            $hasil .= $karakter;
            $lebar += $lebarKarakter;
        }

        return $hasil.$trim_marker;
    }
}

if (! function_exists('mb_strcut')) {
    /**
     * Potong string per bita, tanpa pernah membelah karakter multi-bita.
     *
     * Berbeda dari mb_substr() yang menghitung karakter, offset dan panjang di
     * sini dihitung dalam bita — lalu kedua ujungnya dimundurkan ke batas
     * karakter terdekat. Penyesuaian batas hanya dilakukan untuk UTF-8; pada
     * enkode satu-bita, substr() biasa memang sudah benar.
     */
    function mb_strcut(string $string, int $start, ?int $length = null, ?string $encoding = null): string
    {
        $encoding ??= mb_internal_encoding();
        $utf8 = in_array(strtoupper($encoding), ['UTF-8', 'UTF8'], true);

        $bita = strlen($string);

        if ($start < 0) {
            $start = max(0, $bita + $start);
        }

        if ($start >= $bita) {
            return '';
        }

        if ($length === null) {
            $akhir = $bita;
        } elseif ($length < 0) {
            $akhir = max($start, $bita + $length);
        } else {
            $akhir = min($bita, $start + $length);
        }

        if ($utf8) {
            // Bita lanjutan UTF-8 selalu berpola 10xxxxxx.
            $lanjutan = static fn (int $posisi): bool => (ord($string[$posisi]) & 0xC0) === 0x80;

            // Awal mundur ke bita pertama karakternya.
            while ($start > 0 && $lanjutan($start)) {
                $start--;
            }

            // Akhir mundur supaya karakter yang tidak utuh tidak ikut terbawa.
            while ($akhir > $start && $akhir < $bita && $lanjutan($akhir)) {
                $akhir--;
            }
        }

        return substr($string, $start, $akhir - $start);
    }
}
