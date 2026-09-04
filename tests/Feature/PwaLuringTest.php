<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Penjaga untuk service worker.
 *
 * Ini pemeriksaan berkas, bukan pengujian perilaku: PHPUnit tidak menjalankan
 * service worker, dan tidak ada peramban di sini. Yang dijaga adalah dua
 * bentuk yang pernah salah dan tidak akan ketahuan lagi sampai ada jemaat yang
 * benar-benar kehilangan sinyal:
 *
 * 1. Permintaan aset dilayani cache-first dari cache yang tidak pernah ditulis
 *    apa pun selain HTML, sehingga halaman tersimpan terbuka tanpa CSS.
 * 2. Nama cache tidak pernah berganti, sehingga aset lama terus dilayani
 *    setelah situs naik versi.
 */
class PwaLuringTest extends TestCase
{
    private function serviceWorker(): string
    {
        $isi = file_get_contents(public_path('sw.js'));

        $this->assertIsString($isi, 'public/sw.js tidak terbaca.');

        return $isi;
    }

    #[Test]
    public function aset_ikut_ditulis_ke_cache_bukan_hanya_dibaca_darinya(): void
    {
        $sw = $this->serviceWorker();

        $this->assertMatchesRegularExpression(
            '/ASET\s*=\s*\[[^\]]*\bstyle\b[^\]]*\bscript\b[^\]]*\]/',
            $sw,
            'Berkas gaya dan skrip harus termasuk yang disimpan; tanpa keduanya halaman luring tampil tanpa CSS.',
        );

        // Bentuk lama: cabang non-navigasi berakhir pada `cached || fetch(request)`
        // tanpa apa pun yang menulis hasilnya kembali ke cache.
        $this->assertStringNotContainsString(
            'cached || fetch(request));',
            $sw,
            'Permintaan aset dilayani cache-first tetapi hasilnya tidak pernah disimpan.',
        );
    }

    /**
     * `activate` menghapus setiap cache yang namanya bukan nama versi
     * sekarang, jadi nama itulah satu-satunya mekanisme pembersihan yang
     * dimiliki situs ini. Nama yang tidak ikut naik berarti aset lama
     * dilayani selamanya.
     */
    #[Test]
    public function nama_cache_mengikuti_versi_rilis_terakhir(): void
    {
        preg_match("/const VERSI = '([^']+)'/", $this->serviceWorker(), $sw);
        $this->assertArrayHasKey(1, $sw, 'public/sw.js tidak lagi mengumumkan VERSI.');

        $changelog = file_get_contents(base_path('CHANGELOG.md'));
        $this->assertIsString($changelog);

        preg_match('/^## \[(\d+\.\d+\.\d+)\]/m', $changelog, $rilis);
        $this->assertArrayHasKey(1, $rilis, 'CHANGELOG.md tidak memuat versi rilis bernomor.');

        $this->assertSame(
            $rilis[1],
            $sw[1],
            'Naikkan VERSI di public/sw.js bersama versi rilis di CHANGELOG.md.',
        );
    }
}
