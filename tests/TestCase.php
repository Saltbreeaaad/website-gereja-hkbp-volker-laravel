<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Cache;

abstract class TestCase extends BaseTestCase
{
    /**
     * Cache isi halaman publik sengaja dibiarkan menyala saat tes supaya jalur
     * yang diuji sama dengan jalur produksi. Yang perlu dijaga hanya satu hal:
     * RefreshDatabase mengosongkan tabel tanpa memicu event model, sehingga
     * versi cache tidak ikut naik dan sisa kasus uji sebelumnya bisa terbawa.
     * Membuang isi cache di sini menutup celah itu.
     */
    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();

        // Manifest Vite adalah hasil `npm run build`, bukan bagian dari kode.
        //
        // Membiarkan tes bergantung padanya berarti hasilnya ditentukan oleh
        // apakah seseorang kebetulan sudah membangun aset di mesin itu. Di CI
        // aset dibangun di job terpisah tanpa berbagi berkas, jadi 94 tes
        // gagal serentak dengan "Vite manifest not found" sementara di laptop
        // pengembang semuanya hijau -- kelas kegagalan yang paling mahal,
        // karena ia hanya muncul di tempat yang paling sulit ditelusuri.
        //
        // Kasus yang memang menguji URL aset menyalakannya kembali sendiri
        // dengan manifest buatannya sendiri; lihat DiBelakangProxyTest.
        $this->withoutVite();
    }
}
