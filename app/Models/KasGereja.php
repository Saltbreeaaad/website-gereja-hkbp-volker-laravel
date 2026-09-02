<?php

namespace App\Models;

use App\Models\Concerns\MencatatAktivitas;
use App\Models\Concerns\MenyegarkanCacheKonten;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

/**
 * @property int $id
 * @property CarbonImmutable $tanggal
 * @property string $jenis
 * @property string $keterangan
 * @property int $nominal
 * @property string|null $bukti
 */
class KasGereja extends Model
{
    use HasFactory, MencatatAktivitas, MenyegarkanCacheKonten;

    protected $fillable = [
        'tanggal',
        'jenis',
        'keterangan',
        'nominal',
        'bukti',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $kas): void {
            // Tanggal LAMA ikut diperiksa, bukan hanya yang baru. Tanpa itu satu
            // transaksi dapat digeser keluar dari bulan yang sudah ditutup:
            // tanggal barunya lolos karena periodenya masih terbuka, sementara
            // total bulan tertutup berubah tanpa pernah menyentuh pagar ini.
            $periode = collect([$kas->tanggal, $kas->getOriginal('tanggal')])
                ->reject(fn (mixed $tanggal): bool => blank($tanggal))
                ->map(fn (mixed $tanggal): string => CarbonImmutable::parse($tanggal)->format('Y-m'))
                ->unique();

            if ($periode->isNotEmpty() && self::adaPeriodeDitutup($periode->all())) {
                throw ValidationException::withMessages([
                    'tanggal' => 'Periode kas ini sudah ditutup dan tidak dapat diubah.',
                ]);
            }
        });

        static::deleting(function (self $kas): void {
            if (self::periodeDitutup($kas->tanggal)) {
                throw ValidationException::withMessages([
                    'tanggal' => 'Transaksi dari periode yang sudah ditutup tidak dapat dihapus.',
                ]);
            }
        });

        static::updated(function (self $kas): void {
            if ($kas->wasChanged('bukti')) {
                $kas->hapusBukti($kas->getOriginal('bukti'));
            }
        });

        static::deleted(fn (self $kas) => $kas->hapusBukti($kas->bukti));
    }

    public static function periodeDitutup(mixed $tanggal): bool
    {
        if (blank($tanggal)) {
            return false;
        }

        return self::adaPeriodeDitutup([CarbonImmutable::parse($tanggal)->format('Y-m')]);
    }

    /** @param  list<string>  $periode  Daftar "YYYY-MM". */
    private static function adaPeriodeDitutup(array $periode): bool
    {
        return PeriodeKas::query()
            ->whereIn('periode', $periode)
            ->whereNotNull('ditutup_at')
            ->exists();
    }

    private function hapusBukti(mixed $path): void
    {
        if (is_string($path) && filled($path)) {
            Storage::disk('local')->delete($path);
        }
    }

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

        // `toBase()`: barisnya adalah ringkasan per bulan, bukan transaksi.
        // Menghidrasinya menjadi KasGereja menghasilkan model tanpa id dengan
        // kolom yang tidak ada di tabel — dan cast `tanggal` pun tidak berlaku
        // karena kolomnya tidak ikut diambil.
        $baris = static::query()
            ->toBase()
            ->selectRaw(self::ekspresiPeriode().' as periode')
            ->selectRaw("COALESCE(SUM(CASE WHEN jenis = 'Pemasukan' THEN nominal ELSE 0 END), 0) as pemasukan")
            ->selectRaw("COALESCE(SUM(CASE WHEN jenis = 'Pengeluaran' THEN nominal ELSE 0 END), 0) as pengeluaran")
            ->whereDate('tanggal', '>=', $mulai->toDateString())
            ->groupBy('periode')
            ->get()
            ->keyBy('periode')
            ->map(fn (object $baris): array => [
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
