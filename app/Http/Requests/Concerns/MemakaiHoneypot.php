<?php

namespace App\Http\Requests\Concerns;

use Illuminate\Support\Arr;

/**
 * Umpan bot pada formulir publik.
 *
 * Input `website` disembunyikan dari manusia lewat CSS, jadi bot yang mengisi
 * semua input otomatis akan tertahan aturan `prohibited`.
 *
 * Jebakannya ada di sisi sebaliknya: peramban manusia tetap MENGIRIM input itu
 * — kosong — dan `validated()` ikut mengembalikannya karena ia punya aturan.
 * Meneruskan hasilnya apa adanya ke `Model::create()` berarti mengoper kolom
 * `website` yang tidak ada di `$fillable` mana pun, dan
 * `Model::shouldBeStrict()` di AppServiceProvider mengubah itu menjadi
 * MassAssignmentException — formulir balas dengan 500 di lokal dan staging,
 * sementara di produksi diam-diam lolos. Karena itu kolom umpan dibuang di
 * sini, satu tempat, bukan diingat-ingat di tiap controller.
 */
trait MemakaiHoneypot
{
    private const KOLOM_HONEYPOT = 'website';

    /** @return array<string, list<string>> */
    protected function aturanHoneypot(): array
    {
        return [self::KOLOM_HONEYPOT => ['prohibited']];
    }

    /**
     * Data tervalidasi yang siap disimpan: tanpa kolom umpan.
     *
     * @return array<string, mixed>
     */
    public function dataTersimpan(): array
    {
        return Arr::except($this->validated(), self::KOLOM_HONEYPOT);
    }
}
