<?php

namespace App\Filament\Widgets;

use App\Models\Galeri;
use App\Models\JadwalIbadah;
use App\Models\KasGereja;
use App\Models\Parhalado;
use App\Models\PenggunaanGereja;
use App\Models\PengumumanPenting;
use App\Models\PermohonanDoa;
use App\Models\Renungan;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class RingkasanGereja extends BaseWidget
{
    protected static ?int $sort = -1;

    protected function getStats(): array
    {
        // Satu query agregat untuk seluruh angka kas.
        //
        // `toBase()`: yang diambil hanya dua bilangan, bukan sebuah transaksi.
        // Tanpa itu Eloquent menghidrasi KasGereja palsu — model tanpa id, tanpa
        // tanggal, dengan dua kolom yang tidak ada di tabelnya — hanya untuk
        // dibuang setelah dua propertinya dibaca.
        $kas = KasGereja::query()
            ->toBase()
            ->selectRaw("
                COALESCE(SUM(CASE WHEN jenis = 'Pemasukan' THEN nominal ELSE 0 END), 0) as masuk,
                COALESCE(SUM(CASE WHEN jenis = 'Pengeluaran' THEN nominal ELSE 0 END), 0) as keluar
            ")
            ->first();

        $masuk = (int) ($kas->masuk ?? 0);
        $keluar = (int) ($kas->keluar ?? 0);
        $saldo = $masuk - $keluar;

        $menunggu = PenggunaanGereja::query()
            ->where('status', PenggunaanGereja::MENUNGGU)
            ->whereDate('tanggal', '>=', today())
            ->count();

        $jadwalMendatang = JadwalIbadah::query()->mendatang()->count();

        $renunganHariIni = Renungan::query()->whereDate('tanggal', today())->exists();
        $doaBaru = PermohonanDoa::query()->where('status', PermohonanDoa::BARU)->count();
        $pengumumanAktif = PengumumanPenting::query()->tayang()->count();

        return [
            Stat::make('Saldo Kas', 'Rp '.number_format($saldo, 0, ',', '.'))
                ->description('Pemasukan Rp '.number_format($masuk, 0, ',', '.')
                    .' • Pengeluaran Rp '.number_format($keluar, 0, ',', '.'))
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

            Stat::make('Pokok Doa Baru', $doaBaru)
                ->description($doaBaru > 0 ? 'Perlu didoakan atau ditindaklanjuti' : 'Tidak ada yang belum ditinjau')
                ->descriptionIcon('heroicon-m-heart')
                ->color($doaBaru > 0 ? 'warning' : 'success'),

            Stat::make('Pengumuman Aktif', $pengumumanAktif)
                ->description('Tampil di beranda publik')
                ->descriptionIcon('heroicon-m-megaphone')
                ->color('primary'),
        ];
    }
}
