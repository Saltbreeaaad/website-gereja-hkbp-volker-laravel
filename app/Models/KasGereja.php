<?php

namespace App\Models;

use App\Models\Concerns\MenyegarkanCacheKonten;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class KasGereja extends Model
{
    use HasFactory, MenyegarkanCacheKonten;

    protected $fillable = [
        'tanggal',
        'jenis',
        'keterangan',
        'nominal',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    /**
     * Pemasukan dan pengeluaran per bulan untuk `$jumlahBulan` bulan terakhir,
     * termasuk bulan yang tidak punya transaksi sama sekali.
     *
     * Beranda sebelumnya hanya memperlihatkan total sepanjang masa. Angka itu
     * makin lama makin tidak berarti: jemaat tidak bisa melihat apakah kas
     * sedang sehat bulan ini, hanya akumulasi bertahun-tahun. Tren bulanan
     * menjawab pertanyaan yang sebenarnya ingin dijawab transparansi keuangan.
     *
     * Bulan yang kosong tetap dimunculkan dengan nilai nol supaya sumbu waktu
     * grafik tidak melompat dan menyamarkan bulan tanpa pemasukan.
     *
     * @return Collection<int, array{periode: string, label: string, pemasukan: int, pengeluaran: int}>
     */
    public static function ringkasanBulanan(int $jumlahBulan = 12): Collection
    {
        $mulai = CarbonImmutable::today()->startOfMonth()->subMonths($jumlahBulan - 1);

        $baris = static::query()
            ->selectRaw(static::ekspresiPeriode().' as periode')
            ->selectRaw("COALESCE(SUM(CASE WHEN jenis = 'Pemasukan' THEN nominal ELSE 0 END), 0) as pemasukan")
            ->selectRaw("COALESCE(SUM(CASE WHEN jenis = 'Pengeluaran' THEN nominal ELSE 0 END), 0) as pengeluaran")
            ->whereDate('tanggal', '>=', $mulai->toDateString())
            ->groupBy('periode')
            ->get()
            ->keyBy('periode')
            ->map(fn (self $baris): array => [
                'pemasukan' => (int) $baris->pemasukan,
                'pengeluaran' => (int) $baris->pengeluaran,
            ]);

        return collect(range(0, $jumlahBulan - 1))
            ->map(function (int $selisih) use ($mulai, $baris): array {
                $bulan = $mulai->addMonths($selisih);
                $periode = $bulan->format('Y-m');

                return [
                    'periode' => $periode,
                    'label' => $bulan->translatedFormat('M Y'),
                    'pemasukan' => (int) ($baris[$periode]['pemasukan'] ?? 0),
                    'pengeluaran' => (int) ($baris[$periode]['pengeluaran'] ?? 0),
                ];
            });
    }

    /**
     * Ekspresi SQL untuk memadatkan tanggal menjadi "YYYY-MM".
     *
     * Tidak ada bentuk yang berlaku di semua mesin: proyek ini memakai MySQL di
     * produksi dan SQLite saat pengujian, dan keduanya menamai fungsinya
     * berbeda. Agregasinya sengaja tetap dikerjakan di SQL, bukan dengan
     * menarik seluruh baris ke PHP.
     */
    private static function ekspresiPeriode(): string
    {
        return match (DB::connection()->getDriverName()) {
            'sqlite' => "strftime('%Y-%m', tanggal)",
            'pgsql' => "to_char(tanggal, 'YYYY-MM')",
            default => "DATE_FORMAT(tanggal, '%Y-%m')",
        };
    }
}
