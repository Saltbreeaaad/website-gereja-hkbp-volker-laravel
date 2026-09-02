<?php

namespace App\Http\Controllers;

use App\Models\KasGereja;
use App\Models\PeriodeKas;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LaporanKasController extends Controller
{
    public function tampil(Request $request): View
    {
        Gate::authorize('viewAny', KasGereja::class);
        $data = $this->data($request);

        return view('admin.laporan-kas', $data);
    }

    public function csv(Request $request): StreamedResponse
    {
        Gate::authorize('viewAny', KasGereja::class);
        $data = $this->data($request);
        $nama = "laporan-kas-{$data['dari']->format('Ymd')}-{$data['sampai']->format('Ymd')}.csv";

        return response()->streamDownload(function () use ($data): void {
            $output = fopen('php://output', 'wb');
            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, ['Tanggal', 'Jenis', 'Keterangan', 'Nominal'], ';');

            foreach ($data['transaksi'] as $item) {
                fputcsv($output, [
                    $item->tanggal->format('d/m/Y'),
                    $item->jenis,
                    $item->keterangan,
                    $item->nominal,
                ], ';');
            }

            fputcsv($output, []);
            fputcsv($output, ['Saldo awal', '', '', $data['saldoAwal']], ';');
            fputcsv($output, ['Total pemasukan', '', '', $data['pemasukan']], ';');
            fputcsv($output, ['Total pengeluaran', '', '', $data['pengeluaran']], ';');
            fputcsv($output, ['Saldo akhir', '', '', $data['saldoAkhir']], ';');
            fclose($output);
        }, $nama, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /** @return array{transaksi: Collection<int, KasGereja>, dari: CarbonImmutable, sampai: CarbonImmutable, saldoAwal: int, pemasukan: int, pengeluaran: int, saldoAkhir: int} */
    private function data(Request $request): array
    {
        $dari = $this->tanggal($request->query('dari')) ?? CarbonImmutable::today()->startOfMonth();
        $sampai = $this->tanggal($request->query('sampai')) ?? $dari->endOfMonth();

        if ($sampai->lt($dari)) {
            [$dari, $sampai] = [$sampai, $dari];
        }

        $transaksi = KasGereja::query()
            // whereDate menjaga kompatibilitas dengan baris lama yang pernah
            // tersimpan sebagai datetime sebelum kolom dinormalalkan ke DATE.
            ->whereDate('tanggal', '>=', $dari->toDateString())
            ->whereDate('tanggal', '<=', $sampai->toDateString())
            ->orderBy('tanggal')
            ->orderBy('id')
            ->get();
        $saldoAwal = $this->saldoAwal($dari);
        $pemasukan = (int) $transaksi->where('jenis', 'Pemasukan')->sum('nominal');
        $pengeluaran = (int) $transaksi->where('jenis', 'Pengeluaran')->sum('nominal');

        return compact('transaksi', 'dari', 'sampai', 'saldoAwal', 'pemasukan', 'pengeluaran') + [
            'saldoAkhir' => $saldoAwal + $pemasukan - $pengeluaran,
        ];
    }

    /**
     * Saldo kas tepat sebelum hari pertama laporan.
     *
     * Versi sebelumnya hanya membaca `saldo_awal` bulan yang memuat $dari, dan
     * itu benar hanya bila $dari kebetulan tanggal 1. Untuk rentang yang mulai
     * di tengah bulan — "1–15 Maret", rentang yang paling sering dicetak untuk
     * rapat — transaksi tanggal 1 sampai sebelum $dari tidak masuk daftar,
     * tetapi saldo awalnya tetap saldo awal bulan itu. Akibatnya baris "Saldo
     * akhir" tidak sama dengan saldo kas yang sebenarnya, dan selisihnya diam:
     * tidak ada yang tampak salah pada laporannya.
     *
     * Yang dihitung di sini: saldo awal periode terdekat yang tercatat pada
     * atau sebelum bulan $dari, ditambah selisih seluruh transaksi sejak awal
     * periode itu sampai sehari sebelum $dari.
     *
     * Periode yang belum pernah dicatat pengurus dianggap bersaldo awal nol —
     * itu asumsi yang sama dengan sebelumnya, hanya kini ia mundur ke periode
     * tercatat terakhir alih-alih langsung menyerah ke nol.
     */
    private function saldoAwal(CarbonImmutable $dari): int
    {
        $periode = PeriodeKas::query()
            ->where('periode', '<=', $dari->format('Y-m'))
            ->orderByDesc('periode')
            ->first();

        $saldo = (int) ($periode->saldo_awal ?? 0);
        $mulai = $periode !== null
            ? CarbonImmutable::createFromFormat('Y-m-d', $periode->periode.'-01')->startOfMonth()
            : null;

        // Tidak ada periode tercatat sama sekali: hitung dari transaksi paling
        // awal, supaya riwayat sebelum $dari tetap terhitung.
        $sebelum = KasGereja::query()
            ->toBase()
            ->when($mulai, fn ($query) => $query->whereDate('tanggal', '>=', $mulai->toDateString()))
            ->whereDate('tanggal', '<', $dari->toDateString())
            ->selectRaw("
                COALESCE(SUM(CASE WHEN jenis = 'Pemasukan' THEN nominal ELSE 0 END), 0) as masuk,
                COALESCE(SUM(CASE WHEN jenis = 'Pengeluaran' THEN nominal ELSE 0 END), 0) as keluar
            ")
            ->first();

        return $saldo + (int) ($sebelum->masuk ?? 0) - (int) ($sebelum->keluar ?? 0);
    }

    private function tanggal(mixed $nilai): ?CarbonImmutable
    {
        if (! is_string($nilai) || ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $nilai)) {
            return null;
        }

        try {
            return CarbonImmutable::createFromFormat('Y-m-d', $nilai)->startOfDay();
        } catch (\Throwable) {
            return null;
        }
    }
}
