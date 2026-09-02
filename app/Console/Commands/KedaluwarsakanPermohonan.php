<?php

namespace App\Console\Commands;

use App\Models\PenggunaanGereja;
use Illuminate\Console\Command;

class KedaluwarsakanPermohonan extends Command
{
    protected $signature = 'hkbp:kedaluwarsakan-permohonan {--hari=30 : Batas umur permohonan menunggu}';

    protected $description = 'Tutup otomatis permohonan menunggu yang sudah lewat atau terlalu lama';

    public function handle(): int
    {
        $batas = now()->subDays(max(1, (int) $this->option('hari')));
        $jumlah = 0;

        PenggunaanGereja::query()
            ->where('status', PenggunaanGereja::MENUNGGU)
            ->where(fn ($query) => $query
                ->whereDate('tanggal', '<', today())
                ->orWhere('created_at', '<=', $batas))
            ->eachById(function (PenggunaanGereja $permohonan) use (&$jumlah): void {
                $permohonan->update([
                    'status' => PenggunaanGereja::DITOLAK,
                    'catatan_admin' => 'Permohonan ditutup otomatis karena masa peninjauan telah berakhir.',
                ]);
                $jumlah++;
            });

        $this->info("{$jumlah} permohonan kedaluwarsa ditutup.");

        return self::SUCCESS;
    }
}
