<?php

namespace App\Models\Concerns;

use App\Support\CacheKonten;

/**
 * Setiap perubahan isi dari panel admin langsung membatalkan cache halaman
 * publik, supaya pengurus tidak perlu menunggu TTL habis untuk melihat
 * hasil suntingannya di situs.
 *
 * Dipasang lewat trait, bukan Observer terpisah per model: yang dikerjakan
 * sama persis untuk ketujuh model, dan satu baris `use` di model jauh lebih
 * sulit terlupakan daripada satu baris pendaftaran di service provider.
 */
trait MenyegarkanCacheKonten
{
    public static function bootMenyegarkanCacheKonten(): void
    {
        // `saved` mencakup create dan update sekaligus; `deleted` menutup
        // penghapusan satuan maupun massal lewat Filament.
        static::saved(fn () => CacheKonten::segarkan());
        static::deleted(fn () => CacheKonten::segarkan());
    }
}
