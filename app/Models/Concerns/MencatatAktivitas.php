<?php

namespace App\Models\Concerns;

use App\Models\LogAktivitas;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;

/** Mencatat perubahan data penting tanpa menyimpan rahasia akun. */
trait MencatatAktivitas
{
    /** Kolom teknis/rahasia yang tidak pernah disalin ke log. */
    private static array $kolomLogDikecualikan = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'created_at',
        'updated_at',
    ];

    public static function bootMencatatAktivitas(): void
    {
        // Parameternya `self`, bukan `Model`.
        //
        // `tulisLogAktivitas()` privat milik trait ini. Memanggilnya lewat
        // variabel bertipe Model hanya berhasil karena closure-nya kebetulan
        // terikat pada lingkup trait — sesuatu yang tidak terlihat oleh
        // pembaca maupun analisis statis, dan langsung patah begitu pemanggilan
        // yang sama dipindah ke luar closure. `self` menyatakan maksudnya, dan
        // menyamakannya dengan MembersihkanBerkas yang sudah memakai pola itu.
        static::created(fn (self $model) => $model->tulisLogAktivitas('dibuat', $model->getAttributes()));

        static::updated(function (self $model): void {
            $baru = Arr::except($model->getChanges(), self::$kolomLogDikecualikan);

            if ($baru === []) {
                return;
            }

            $lama = collect(array_keys($baru))
                ->mapWithKeys(fn (string $kolom): array => [$kolom => $model->getOriginal($kolom)])
                ->all();

            $model->tulisLogAktivitas('diubah', ['lama' => $lama, 'baru' => $baru]);
        });

        static::deleted(fn (self $model) => $model->tulisLogAktivitas('dihapus', $model->getOriginal()));
    }

    /** @param array<string, mixed> $perubahan */
    private function tulisLogAktivitas(string $aksi, array $perubahan): void
    {
        $perubahan = Arr::except($perubahan, self::$kolomLogDikecualikan);

        LogAktivitas::query()->create([
            'user_id' => Auth::id(),
            'aksi' => $aksi,
            'subjek_tipe' => static::class,
            'subjek_id' => $this->getKey(),
            'ringkasan' => $this->ringkasanLogAktivitas(),
            'perubahan' => $perubahan === [] ? null : $perubahan,
            'ip_address' => app()->runningInConsole() ? null : request()->ip(),
            'user_agent' => app()->runningInConsole() ? null : request()->userAgent(),
        ]);
    }

    private function ringkasanLogAktivitas(): string
    {
        $atribut = $this->getAttributes();

        foreach (['judul', 'nama', 'nama_kegiatan', 'keterangan', 'email', 'kode'] as $kolom) {
            if (! array_key_exists($kolom, $atribut)) {
                continue;
            }

            $nilai = $atribut[$kolom];

            if (is_scalar($nilai) && filled($nilai)) {
                return str($nilai)->limit(250)->toString();
            }
        }

        return class_basename($this).' #'.$this->getKey();
    }
}
