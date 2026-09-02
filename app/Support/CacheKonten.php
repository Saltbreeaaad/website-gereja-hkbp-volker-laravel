<?php

namespace App\Support;

use Closure;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;

/**
 * Cache isi halaman publik.
 *
 * Halaman publik dibaca jauh lebih sering daripada diubah: beranda saja
 * menembak enam query ke database untuk data yang biasanya hanya berganti
 * beberapa kali seminggu. Semua hasil query itu kini disimpan di cache.
 *
 * Pembatalannya memakai nomor versi, bukan penghapusan kunci satu per satu.
 * Alasannya: satu halaman merangkum banyak model, jadi memetakan "model apa
 * mempengaruhi kunci apa" cepat sekali salah. Menaikkan satu penghitung
 * membuat seluruh kunci lama tidak pernah dibaca lagi, dan biayanya tetap satu
 * operasi berapa pun jumlah halamannya. Cara ini juga tidak bergantung pada
 * cache tag — store `database` bawaan proyek ini tidak mendukungnya.
 *
 * Tanggal hari ini ikut masuk ke kunci karena beberapa query bergantung pada
 * `today()` (jadwal mendatang, permohonan yang belum lewat). Tanpa itu, hasil
 * kemarin masih tersaji sampai TTL habis meski tidak ada yang diubah admin.
 *
 * ## Hanya nilai polos yang boleh masuk cache
 *
 * `config('cache.serializable_classes')` bernilai `false` — bawaan Laravel yang
 * menolak meng-unserialize kelas PHP apa pun dari cache, sebagai perlindungan
 * terhadap serangan gadget chain bila APP_KEY bocor. Store `database`, `file`,
 * dan `redis` semuanya menegakkannya.
 *
 * Artinya menyimpan Eloquent Collection langsung ke cache TIDAK bekerja: yang
 * kembali adalah `__PHP_Incomplete_Class`, dan halaman baru meledak pada
 * kunjungan KEDUA (yang pertama masih dilayani hasil query segar). Karena itu
 * `ingatModel()` dan `ingatHalaman()` menyimpan atribut mentah sebagai array
 * biasa lalu merakit ulang modelnya saat dibaca — default keamanan tetap utuh,
 * dan hasilnya bekerja di store mana pun.
 *
 * Jangan melewatkan model, Collection, paginator, atau Carbon ke `ingat()`.
 */
final class CacheKonten
{
    /** Kunci penghitung versi; naik setiap ada perubahan isi dari panel admin. */
    private const KUNCI_VERSI = 'konten:versi';

    /** Enam jam: cukup lama untuk berguna, cukup pendek sebagai jaring pengaman. */
    public const TTL = 6 * 3600;

    /**
     * Kunci `null` berarti "jangan simpan" — pemanggil boleh memutuskan bahwa
     * hasil tertentu tidak layak masuk cache bersama. Dipakai untuk hasil yang
     * kuncinya ikut menyertakan masukan pengunjung: tanpa jalan keluar ini,
     * setiap kata pencarian acak menulis satu baris cache baru yang bertahan
     * enam jam, dan siapa pun dapat menggelembungkan tabel cache sesukanya.
     */
    public static function ingat(?string $kunci, Closure $isi, ?int $detik = null): mixed
    {
        if ($kunci === null || ! config('gereja.cache_konten')) {
            return $isi();
        }

        return Cache::remember(self::kunciPenuh($kunci), $detik ?? self::TTL, $isi);
    }

    /**
     * Cache hasil query Eloquent.
     *
     * Yang disimpan adalah atribut mentah tiap baris — array skalar — bukan
     * modelnya. `hydrate()` merakitnya kembali menjadi model utuh lengkap
     * dengan cast, jadi pemanggilnya tidak melihat perbedaan apa pun.
     *
     * @template TModel of Model
     *
     * @param  class-string<TModel>  $model
     * @param  Closure(): EloquentCollection<int, TModel>  $kueri
     * @return EloquentCollection<int, TModel>
     */
    public static function ingatModel(?string $kunci, string $model, Closure $kueri, ?int $detik = null): EloquentCollection
    {
        $baris = self::ingat(
            $kunci,
            fn (): array => $kueri()->map->getAttributes()->values()->all(),
            $detik,
        );

        /** @var EloquentCollection<int, TModel> $hasil */
        $hasil = $model::hydrate($baris);

        return $hasil;
    }

    /**
     * Cache satu halaman hasil paginasi.
     *
     * Paginator pun sebuah objek, jadi yang disimpan hanya barisnya plus angka
     * yang dibutuhkan untuk membangunnya kembali.
     *
     * @template TModel of Model
     *
     * @param  class-string<TModel>  $model
     * @param  Closure(): LengthAwarePaginator  $kueri
     */
    public static function ingatHalaman(?string $kunci, string $model, Closure $kueri, ?int $detik = null): LengthAwarePaginator
    {
        $tersimpan = self::ingat($kunci, function () use ($kueri): array {
            $halaman = $kueri();

            return [
                'baris' => collect($halaman->items())->map->getAttributes()->values()->all(),
                'total' => $halaman->total(),
                'per_halaman' => $halaman->perPage(),
                'halaman_kini' => $halaman->currentPage(),
                'path' => $halaman->path(),
                'query' => $halaman->getOptions()['query'] ?? [],
            ];
        }, $detik);

        /** @var EloquentCollection<int, TModel> $baris */
        $baris = $model::hydrate($tersimpan['baris']);

        return new LengthAwarePaginator(
            $baris,
            $tersimpan['total'],
            $tersimpan['per_halaman'],
            $tersimpan['halaman_kini'],
            ['path' => $tersimpan['path'], 'query' => $tersimpan['query']],
        );
    }

    /** Batalkan seluruh cache isi dengan menaikkan nomor versi. */
    public static function segarkan(): void
    {
        // `increment` mengembalikan false bila kuncinya belum ada, jadi pastikan
        // dulu nilainya tertulis sebelum dinaikkan.
        self::versi();

        Cache::increment(self::KUNCI_VERSI);
    }

    public static function versi(): int
    {
        return (int) Cache::rememberForever(self::KUNCI_VERSI, fn () => 1);
    }

    private static function kunciPenuh(string $kunci): string
    {
        return sprintf('konten:v%d:%s:%s', self::versi(), today()->toDateString(), $kunci);
    }
}
