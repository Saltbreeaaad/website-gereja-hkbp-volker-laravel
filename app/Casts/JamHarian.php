<?php

namespace App\Casts;

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

/**
 * Cast untuk kolom bertipe `time`.
 *
 * Cast bawaan `datetime` menyimpan jam sebagai datetime penuh ("2026-08-20 07:00:00")
 * ke dalam kolom `time`, sehingga nilai di database jadi campur aduk dengan baris lama
 * yang tersimpan sebagai "07:00". Akibatnya perbandingan jam antar-baris di SQL tidak
 * bisa dipercaya — dua jadwal yang bentrok bisa lolos karena tanggalnya berbeda.
 *
 * Cast ini menormalkan penyimpanan ke "H:i:s" dan selalu mengembalikan Carbon yang
 * ditambatkan ke hari ini, supaya dua nilai jam selalu bisa dibandingkan langsung.
 */
class JamHarian implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): ?CarbonImmutable
    {
        if (blank($value)) {
            return null;
        }

        if ($value instanceof \DateTimeInterface) {
            $value = $value->format('H:i:s');
        }

        // Terima "07:00", "07:00:00", maupun baris lama "2026-08-20 07:00:00".
        return CarbonImmutable::today()->setTimeFromTimeString(
            str_contains((string) $value, ' ')
                ? CarbonImmutable::parse($value)->format('H:i:s')
                : (string) $value
        );
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        if (blank($value)) {
            return null;
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->format('H:i:s');
        }

        return CarbonImmutable::parse($value)->format('H:i:s');
    }
}
