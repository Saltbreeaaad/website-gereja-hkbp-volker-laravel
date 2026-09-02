<?php

namespace App\Console\Commands;

use App\Models\Galeri;
use App\Models\Parhalado;
use App\Models\Renungan;
use Illuminate\Console\Command;

class OptimalkanGambar extends Command
{
    protected $signature = 'hkbp:optimalkan-gambar {--dry-run : Hanya hitung tanpa mengubah berkas}';

    protected $description = 'Ubah gambar lama ke WebP dan buat thumbnail responsif';

    public function handle(): int
    {
        $jumlah = 0;
        $dioptimalkan = 0;

        foreach ([Galeri::class, Renungan::class, Parhalado::class] as $model) {
            $model::query()->whereNotNull('foto')->eachById(function ($baris) use (&$jumlah, &$dioptimalkan): void {
                $jumlah++;

                if (! $this->option('dry-run') && $baris->optimalkanGambar()) {
                    $dioptimalkan++;
                }
            });
        }

        $this->info($this->option('dry-run')
            ? "{$jumlah} gambar siap diperiksa."
            : "{$dioptimalkan} dari {$jumlah} gambar berhasil dioptimalkan.");

        return self::SUCCESS;
    }
}
