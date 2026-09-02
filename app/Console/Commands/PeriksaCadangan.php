<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Notifications\CadanganBermasalah;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Notification;

class PeriksaCadangan extends Command
{
    protected $signature = 'hkbp:periksa-cadangan {--maksimal-umur=36 : Umur maksimum cadangan dalam jam}';

    protected $description = 'Periksa usia, ukuran, dan ruang penyimpanan cadangan';

    public function handle(): int
    {
        $direktori = config('gereja.direktori_cadangan', storage_path('app/backups'));
        $berkas = collect(glob($direktori.'/*.sql') ?: [])->sortByDesc(fn (string $path): int => filemtime($path))->first();
        $galat = null;

        if (! is_string($berkas)) {
            $galat = 'Belum ada berkas cadangan basis data.';
        } elseif ((filesize($berkas) ?: 0) < 1024) {
            $galat = 'Cadangan terbaru terlalu kecil dan kemungkinan tidak lengkap.';
        } elseif (CarbonImmutable::createFromTimestamp(filemtime($berkas))->lt(now()->subHours(max(1, (int) $this->option('maksimal-umur'))))) {
            $galat = 'Cadangan terbaru sudah lebih tua dari batas yang diizinkan.';
        } elseif (($bebas = disk_free_space($direktori)) !== false && $bebas < 512 * 1024 * 1024) {
            $galat = 'Ruang penyimpanan cadangan tersisa kurang dari 512 MB.';
        }

        if ($galat === null) {
            $this->info('Cadangan sehat: '.basename($berkas).'.');

            return self::SUCCESS;
        }

        $this->error($galat);

        // Satu notifikasi per hari agar lonceng admin tidak dibanjiri pesan sama.
        if (Cache::add('cadangan:peringatan:'.today()->toDateString(), true, now()->addDay())) {
            Notification::send(User::query()->where('role', User::ADMIN)->get(), new CadanganBermasalah($galat));
        }

        return self::FAILURE;
    }
}
