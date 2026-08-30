<?php

namespace App\Models\Concerns;

use Illuminate\Support\Facades\Storage;

/**
 * Hapus berkas unggahan yang sudah tidak dirujuk baris mana pun.
 *
 * Filament hanya menulis path hasil unggahan ke kolom; ia tidak pernah
 * menghapus berkas lamanya. Akibatnya setiap foto pelayan yang diganti dan
 * setiap baris galeri yang dihapus meninggalkan berkas yatim di
 * storage/app/public — tidak pernah tampil, tidak pernah dibersihkan, dan
 * terus menumpuk di hosting yang kuotanya terbatas.
 *
 * Model yang memakai trait ini mendeklarasikan kolom berkasnya lewat
 * `kolomBerkas()`.
 */
trait MembersihkanBerkas
{
    /**
     * Nama kolom yang berisi path pada disk `public`.
     *
     * @return list<string>
     */
    abstract protected function kolomBerkas(): array;

    public static function bootMembersihkanBerkas(): void
    {
        // Berkas lama dibuang hanya setelah update berhasil tersimpan; kalau
        // dilakukan di `updating` dan penyimpanan gagal, berkasnya sudah hilang
        // sementara barisnya masih menunjuk ke sana.
        static::updated(function (self $model) {
            foreach ($model->kolomBerkas() as $kolom) {
                if ($model->wasChanged($kolom)) {
                    $model->hapusBerkas($model->getOriginal($kolom));
                }
            }
        });

        static::deleted(function (self $model) {
            foreach ($model->kolomBerkas() as $kolom) {
                $model->hapusBerkas($model->getAttribute($kolom));
            }
        });
    }

    /**
     * Lewati path kosong, dan pastikan tidak ada baris lain yang masih memakai
     * path yang sama — dua baris bisa menunjuk berkas yang sama bila datanya
     * pernah diduplikasi lewat SQL langsung.
     */
    private function hapusBerkas(mixed $path): void
    {
        if (! is_string($path) || trim($path) === '') {
            return;
        }

        $masihDipakai = static::query()
            ->whereKeyNot($this->getKey())
            ->where(function ($query) use ($path) {
                foreach ($this->kolomBerkas() as $kolom) {
                    $query->orWhere($kolom, $path);
                }
            })
            ->exists();

        if ($masihDipakai) {
            return;
        }

        Storage::disk('public')->delete($path);
    }
}
