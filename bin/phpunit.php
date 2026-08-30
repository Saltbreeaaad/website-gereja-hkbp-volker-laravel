<?php

declare(strict_types=1);
use PHPUnit\TextUI\Application;

/*
|--------------------------------------------------------------------------
| Pelari PHPUnit
|--------------------------------------------------------------------------
|
| Jalankan lewat `composer test`.
|
| Berkas ini ada karena `vendor/bin/phpunit` menolak start bila ekstensi
| mbstring tidak termuat, padahal PHPUnit sendiri berjalan baik-baik saja di
| atas polyfill (symfony/polyfill-mbstring ditambah bootstrap/polyfill-mbstring.php
| milik proyek ini). Di mesin yang mbstring-nya diblokir kebijakan keamanan
| Windows, gerbang itu membuat seluruh suite tidak bisa dijalankan sama sekali.
|
| Yang dilakukan di sini BUKAN mematikan pemeriksaan itu. Pemeriksaannya
| diulang; yang dilonggarkan hanya mbstring, dan hanya bila setiap fungsi mb_*
| yang dipakai PHPUnit dan Laravel terbukti tersedia lewat polyfill. Ekstensi
| lain yang hilang tetap menghentikan proses seperti biasa.
|
*/

require __DIR__.'/../vendor/autoload.php';

/** Ekstensi yang diperiksa vendor/bin/phpunit sebelum menjalankan aplikasi. */
$wajib = ['dom', 'filter', 'json', 'libxml', 'mbstring', 'tokenizer', 'xmlwriter'];

/** Fungsi mb_* yang harus ada sebelum mbstring boleh dianggap tergantikan. */
$fungsiPengganti = [
    'mb_check_encoding', 'mb_convert_case', 'mb_convert_encoding', 'mb_internal_encoding',
    'mb_str_split', 'mb_strcut', 'mb_strimwidth', 'mb_strlen', 'mb_strpos', 'mb_strrpos',
    'mb_strtolower', 'mb_strtoupper', 'mb_strwidth', 'mb_substr',
];

$hilang = array_values(array_filter($wajib, static fn (string $ext): bool => ! extension_loaded($ext)));

if ($hilang === ['mbstring']) {
    $belumAda = array_values(array_filter($fungsiPengganti, static fn (string $f): bool => ! function_exists($f)));

    if ($belumAda === []) {
        $hilang = [];

        fwrite(STDERR, 'Catatan: ekstensi mbstring tidak termuat; suite berjalan di atas polyfill.'.PHP_EOL.PHP_EOL);
    } else {
        fwrite(
            STDERR,
            'Ekstensi mbstring tidak termuat dan polyfill belum lengkap — fungsi yang masih hilang: '
            .implode(', ', $belumAda).PHP_EOL
        );

        exit(1);
    }
}

if ($hilang !== []) {
    fwrite(
        STDERR,
        sprintf(
            'PHPUnit membutuhkan ekstensi "%s", tetapi "%s" tidak tersedia.'.PHP_EOL,
            implode('", "', $wajib),
            implode('", "', $hilang)
        )
    );

    exit(1);
}

exit((new Application)->run($_SERVER['argv']));
