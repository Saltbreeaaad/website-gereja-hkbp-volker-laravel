<?php

namespace App\Filament\Widgets;

use Carbon\CarbonImmutable;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class StatusCadangan extends BaseWidget
{
    protected static ?int $sort = 20;

    /**
     * Hanya administrator.
     *
     * Isinya keterangan infrastruktur — nama berkas cadangan dan sisa ruang
     * disk — bukan data pelayanan. PengurusPolicy sengaja membiarkan semua
     * peran *melihat* modul gereja, tetapi itu alasan yang berlaku untuk kas
     * dan jadwal, bukan untuk keadaan server; bendahara maupun sekretaris tidak
     * bisa berbuat apa pun terhadapnya. Sejalan dengan LogAktivitasPolicy.
     */
    public static function canView(): bool
    {
        return Auth::user()?->isAdmin() ?? false;
    }

    protected function getStats(): array
    {
        $direktori = config('gereja.direktori_cadangan', storage_path('app/backups'));
        $terbaru = collect(glob($direktori.'/*.sql') ?: [])->sortByDesc(fn (string $path): int => filemtime($path))->first();

        if (! is_string($terbaru)) {
            return [Stat::make('Cadangan Basis Data', 'Belum ada')->description('Jalankan hkbp:cadangkan')->color('danger')];
        }

        $waktu = CarbonImmutable::createFromTimestamp(filemtime($terbaru));
        $sehat = $waktu->gte(now()->subHours(36)) && (filesize($terbaru) ?: 0) >= 1024;
        $bebas = disk_free_space($direktori);

        return [
            Stat::make('Cadangan Terakhir', $waktu->diffForHumans())
                ->description(basename($terbaru).' • '.round((filesize($terbaru) ?: 0) / 1024).' kB')
                ->descriptionIcon($sehat ? 'heroicon-m-check-circle' : 'heroicon-m-exclamation-triangle')
                ->color($sehat ? 'success' : 'danger'),
            Stat::make('Ruang Cadangan', $bebas === false ? 'Tidak diketahui' : number_format($bebas / 1_073_741_824, 1, ',', '.').' GB bebas')
                ->description('Peringatan muncul di bawah 512 MB')
                ->descriptionIcon('heroicon-m-circle-stack')
                ->color($bebas !== false && $bebas < 512 * 1024 * 1024 ? 'danger' : 'success'),
        ];
    }
}
