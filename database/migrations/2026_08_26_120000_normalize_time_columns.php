<?php

use Carbon\CarbonImmutable;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Baris lama menyimpan jam dalam format campur: "07:00" dari input awal, dan
 * "2026-08-20 07:00:00" dari baris yang sempat tersimpan lewat cast `datetime`.
 * Normalkan semuanya ke "H:i:s" supaya perbandingan jam di SQL bisa dipercaya.
 */
return new class extends Migration
{
    private const KOLOM = [
        'jadwal_ibadahs' => ['waktu'],
        'penggunaan_gerejas' => ['waktu_mulai', 'waktu_selesai'],
    ];

    public function up(): void
    {
        foreach (self::KOLOM as $tabel => $kolom) {
            foreach ($kolom as $nama) {
                DB::table($tabel)
                    ->whereNotNull($nama)
                    ->orderBy('id')
                    ->each(function (object $baris) use ($tabel, $nama) {
                        $nilai = $baris->{$nama};
                        $normal = $this->keFormatJam($nilai);

                        if ($normal !== null && $normal !== $nilai) {
                            DB::table($tabel)->where('id', $baris->id)->update([$nama => $normal]);
                        }
                    });
            }
        }
    }

    public function down(): void
    {
        // Normalisasi format bersifat idempoten; tidak ada yang perlu dikembalikan.
    }

    private function keFormatJam(mixed $nilai): ?string
    {
        try {
            return CarbonImmutable::parse((string) $nilai)->format('H:i:s');
        } catch (Throwable) {
            return null;
        }
    }
};
