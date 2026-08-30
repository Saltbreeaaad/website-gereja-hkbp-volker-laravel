<?php

namespace App\Filament\Widgets;

use App\Models\Galeri;
use App\Models\JadwalIbadah;
use App\Models\KasGereja;
use App\Models\Parhalado;
use App\Models\PenggunaanGereja;
use App\Models\Renungan;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class RingkasanGereja extends BaseWidget
{
    protected static ?int $sort = -1;

    protected function getStats(): array
    {
        // Satu query agregat untuk seluruh angka kas.
        $kas = KasGereja::query()
            ->selectRaw("
                COALESCE(SUM(CASE WHEN jenis = 'Pemasukan' THEN nominal ELSE 0 END), 0) as masuk,
                COALESCE(SUM(CASE WHEN jenis = 'Pengeluaran' THEN nominal ELSE 0 END), 0) as keluar
            ")
            ->first();

        $saldo = (int) $kas->masuk - (int) $kas->keluar;

        $menunggu = PenggunaanGereja::query()
            ->where('status', PenggunaanGereja::MENUNGGU)
            ->whereDate('tanggal', '>=', today())
            ->count();

        $jadwalMendatang = JadwalIbadah::query()->mendatang()->count();

        $renunganHariIni = Renungan::query()->whereDate('tanggal', today())->exists();

        return [
            Stat::make('Saldo Kas', 'Rp '.number_format($saldo, 0, ',', '.'))
                ->description('Pemasukan Rp '.number_format((int) $kas->masuk, 0, ',', '.')
                    .' • Pengeluaran Rp '.number_format((int) $kas->keluar, 0, ',', '.'))
                ->descriptionIcon('heroicon-m-banknotes')
                ->color($saldo >= 0 ? 'success' : 'danger'),

            Stat::make('Permohonan Menunggu', $menunggu)
                ->description($menunggu > 0 ? 'Perlu ditinjau pengurus' : 'Tidak ada yang tertunda')
                ->descriptionIcon($menunggu > 0 ? 'heroicon-m-exclamation-triangle' : 'heroicon-m-check-circle')
                ->color($menunggu > 0 ? 'warning' : 'success'),

            Stat::make('Renungan Hari Ini', $renunganHariIni ? 'Sudah terbit' : 'Belum ada')
                ->description($renunganHariIni
                    ? 'Jemaat sudah bisa membacanya'
                    : 'Halaman renungan hari ini masih kosong')
                ->descriptionIcon('heroicon-m-book-open')
                ->color($renunganHariIni ? 'success' : 'gray'),

            Stat::make('Jadwal Ibadah Mendatang', $jadwalMendatang)
                ->description($jadwalMendatang > 0
                    ? 'Tampil di beranda website'
                    : 'Beranda tidak menampilkan jadwal apa pun')
                ->descriptionIcon('heroicon-m-calendar')
                ->color($jadwalMendatang > 0 ? 'primary' : 'warning'),

            Stat::make('Pelayan & Pengurus', Parhalado::query()->count())
                ->description('Terdata di halaman Pelayan')
                ->descriptionIcon('heroicon-m-users')
                ->color('primary'),

            Stat::make('Foto Galeri', Galeri::query()->count())
                ->description('Dokumentasi kegiatan')
                ->descriptionIcon('heroicon-m-photo')
                ->color('primary'),
        ];
    }
}
