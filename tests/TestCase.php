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
    }
}
