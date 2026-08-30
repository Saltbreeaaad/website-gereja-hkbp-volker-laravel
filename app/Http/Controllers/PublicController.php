<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePenggunaanGerejaRequest;
use App\Models\Galeri;
use App\Models\JadwalIbadah;
use App\Models\KasGereja;
use App\Models\Parhalado;
use App\Models\PenggunaanGereja;
use App\Models\Renungan;
use App\Models\User;
use App\Models\WartaJemaat;
use App\Notifications\PermohonanGedungMasuk;
use App\Support\CacheKonten;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\View\View;

class PublicController extends Controller
{
    public function home(): View
    {
        // Beranda merangkum enam query ke data yang jarang berubah. Hasilnya
        // disimpan utuh dan dibatalkan begitu pengurus menyunting apa pun dari
        // panel admin (lihat App\Models\Concerns\MenyegarkanCacheKonten).
        // Hanya kolom yang benar-benar dipakai kartu beranda — bukan SELECT *.
        $parhalados = CacheKonten::ingatModel('beranda:parhalado', Parhalado::class, fn () => Parhalado::query()
            ->select(['id', 'nama', 'foto', 'kategori', 'jabatan'])
            ->urutTampil()
            ->limit(12)
            ->get());

        // Sebelumnya seluruh jadwal ikut tampil, termasuk yang sudah lewat.
        $jadwal_ibadah = CacheKonten::ingatModel('beranda:jadwal', JadwalIbadah::class, fn () => JadwalIbadah::query()
            ->select(['id', 'nama_ibadah', 'tanggal', 'waktu', 'pelayan_firman'])
            ->mendatang()
            ->limit(6)
            ->get());

        $warta = CacheKonten::ingatModel('beranda:warta', WartaJemaat::class, fn () => WartaJemaat::query()
            ->select(['id', 'judul', 'tanggal', 'file_warta'])
            ->terbaru()
            ->limit(3)
            ->get());

        $renungans = CacheKonten::ingatModel('beranda:renungan', Renungan::class, fn () => Renungan::query()
            ->select(['id', 'judul', 'tanggal', 'penulis', 'isi'])
            ->terbaru()
            ->limit(3)
            ->get());

        $galeris = CacheKonten::ingatModel('beranda:galeri', Galeri::class, fn () => Galeri::query()
            ->select(['id', 'judul', 'foto', 'tanggal'])
            ->terbaru()
            ->limit(8)
            ->get());

        // Angka kas seluruhnya skalar, jadi aman disimpan apa adanya.
        $kas = CacheKonten::ingat('beranda:kas', function (): array {
            // Satu query dengan conditional aggregation, menggantikan dua query terpisah.
            $jumlah = KasGereja::query()
                ->selectRaw("
                    COALESCE(SUM(CASE WHEN jenis = 'Pemasukan' THEN nominal ELSE 0 END), 0) as total_pemasukan,
                    COALESCE(SUM(CASE WHEN jenis = 'Pengeluaran' THEN nominal ELSE 0 END), 0) as total_pengeluaran
                ")
                ->first();

            return [
                'pemasukan' => (int) $jumlah->total_pemasukan,
                'pengeluaran' => (int) $jumlah->total_pengeluaran,
                // Tren 12 bulan terakhir: yang sebenarnya ingin diketahui jemaat
                // adalah kondisi kas belakangan ini, bukan akumulasi sepanjang
                // masa. `->all()` supaya yang tersimpan array biasa.
                'tren' => KasGereja::ringkasanBulanan(12)->all(),
            ];
        });

        return view('welcome', [
            'parhalados' => $parhalados,
            'jadwal_ibadah' => $jadwal_ibadah,
            'warta' => $warta,
            'renungans' => $renungans,
            'galeris' => $galeris,
            'total_pemasukan' => $kas['pemasukan'],
            'total_pengeluaran' => $kas['pengeluaran'],
            'tren_kas' => collect($kas['tren']),
            'saldo_akhir' => $kas['pemasukan'] - $kas['pengeluaran'],
        ]);
    }

    public function pelayan(): View
    {
        // Satu query untuk ketiga kelompok, dipilah di memori — sebelumnya tiga
        // query terpisah ke tabel yang sama. Kolomnya dibatasi pada yang dipakai
        // kartu pelayan; `telepon` sengaja tidak ikut karena tidak ditampilkan.
        $semua = CacheKonten::ingatModel('pelayan', Parhalado::class, fn () => Parhalado::query()
            ->select(['id', 'nama', 'foto', 'kategori', 'jabatan', 'bidang', 'keterangan'])
            ->urutTampil()
            ->get());

        return view('pelayan', [
            'pendeta' => $semua->where('kategori', 'Pendeta'),
            'parhalado' => $semua->where('kategori', 'Parhalado')->groupBy('bidang'),
            'kategorial' => $semua->where('kategori', 'Kategorial')->groupBy('bidang'),
        ]);
    }

    public function profil(): View
    {
        return view('profil');
    }

    public function renungan(Request $request): View
    {
        $selected_date = $this->tanggalDiminta($request->query('tanggal'));

        $renungan = Renungan::query()->whereDate('tanggal', $selected_date)->first();

        $recent_renungans = CacheKonten::ingatModel('renungan:terbaru', Renungan::class, fn () => Renungan::query()
            ->select(['id', 'judul', 'tanggal'])
            ->terbaru()
            ->limit(6)
            ->get());

        // Navigasi ke edisi sebelum/sesudah tanggal yang sedang dibuka.
        $sebelumnya = Renungan::query()
            ->select(['id', 'judul', 'tanggal'])
            ->whereDate('tanggal', '<', $selected_date)
            ->orderByDesc('tanggal')
            ->first();

        $berikutnya = Renungan::query()
            ->select(['id', 'judul', 'tanggal'])
            ->whereDate('tanggal', '>', $selected_date)
            ->orderBy('tanggal')
            ->first();

        return view('renungan', compact(
            'renungan',
            'selected_date',
            'recent_renungans',
            'sebelumnya',
            'berikutnya',
        ));
    }

    public function warta(Request $request): View
    {
        $tahunTersedia = $this->tahunTersedia(WartaJemaat::class, 'warta:tahun');

        // Tahun di luar daftar diabaikan diam-diam, bukan dijadikan galat: URL
        // lama yang dibagikan jemaat tetap membuka arsip, hanya tanpa filter.
        $tahun = in_array($request->integer('tahun'), $tahunTersedia, strict: true)
            ? $request->integer('tahun')
            : null;

        $wartas = CacheKonten::ingatHalaman(
            sprintf('warta:%s:hal-%d', $tahun ?? 'semua', $request->integer('page', 1)),
            WartaJemaat::class,
            fn () => WartaJemaat::query()
                ->select(['id', 'judul', 'tanggal', 'file_warta'])
                ->when($tahun, fn ($query) => $query->whereYear('tanggal', $tahun))
                ->terbaru()
                ->paginate(12)
                // Aman disimpan di cache karena tahunnya sudah ikut ke kunci:
                // tautan halaman tidak bisa membawa filter pengunjung lain.
                ->appends(array_filter(['tahun' => $tahun])),
        );

        return view('warta', compact('wartas', 'tahun', 'tahunTersedia'));
    }

    /**
     * Daftar tahun yang punya isi, terbaru dulu.
     *
     * Diturunkan dari tanggal paling awal dan paling akhir, bukan dari
     * `SELECT DISTINCT YEAR(tanggal)`: fungsi tahun berbeda nama di MySQL
     * (`YEAR`) dan SQLite (`strftime`), dan proyek ini memakai keduanya —
     * MySQL di produksi, SQLite di pengujian.
     *
     * @param  class-string<Model>  $model
     * @return list<int>
     */
    private function tahunTersedia(string $model, string $kunciCache): array
    {
        return CacheKonten::ingat($kunciCache, function () use ($model): array {
            $rentang = $model::query()
                ->selectRaw('MIN(tanggal) as awal, MAX(tanggal) as akhir')
                ->first();

            if (blank($rentang?->awal)) {
                return [];
            }

            $awal = (int) CarbonImmutable::parse($rentang->awal)->format('Y');
            $akhir = (int) CarbonImmutable::parse($rentang->akhir)->format('Y');

            return range($akhir, $awal);
        });
    }

    public function galeri(Request $request): View
    {
        $tahunTersedia = $this->tahunTersedia(Galeri::class, 'galeri:tahun');

        $tahun = in_array($request->integer('tahun'), $tahunTersedia, strict: true)
            ? $request->integer('tahun')
            : null;

        // Tahun dan nomor halaman ikut ke kunci: tiap kombinasi punya isi
        // sendiri. Query string di luar keduanya sengaja tidak diteruskan ke
        // tautan halaman — menyimpan paginator yang membawa query string satu
        // pengunjung ke cache bersama akan membocorkannya ke pengunjung lain.
        $galeris = CacheKonten::ingatHalaman(
            sprintf('galeri:%s:hal-%d', $tahun ?? 'semua', $request->integer('page', 1)),
            Galeri::class,
            fn () => Galeri::query()
                ->select(['id', 'judul', 'foto', 'tanggal'])
                ->when($tahun, fn ($query) => $query->whereYear('tanggal', $tahun))
                ->terbaru()
                ->paginate(12)
                ->appends(array_filter(['tahun' => $tahun])),
        );

        return view('galeri', compact('galeris', 'tahun', 'tahunTersedia'));
    }

    public function penggunaanGereja(): View
    {
        // `kontak` dan `catatan_admin` sengaja tidak ikut diambil: keduanya tidak
        // pernah ditampilkan ke publik, jadi tidak perlu ada di memori halaman.
        $penggunaans = CacheKonten::ingatModel('penggunaan-gereja', PenggunaanGereja::class, fn () => PenggunaanGereja::query()
            ->select(['id', 'nama_kegiatan', 'nama_pemohon', 'tanggal', 'waktu_mulai', 'waktu_selesai', 'keterangan', 'status'])
            ->tampilPublik()
            ->get());

        return view('penggunaan-gereja', compact('penggunaans'));
    }

    public function storePenggunaanGereja(StorePenggunaanGerejaRequest $request): RedirectResponse
    {
        $permohonan = PenggunaanGereja::create([
            ...$request->validated(),
            'status' => PenggunaanGereja::MENUNGGU,
        ]);

        // Pengurus tidak lagi harus kebetulan membuka panel untuk tahu ada
        // permohonan masuk.
        Notification::send(User::all(), new PermohonanGedungMasuk($permohonan));

        return redirect()
            ->route('penggunaan-gereja.lacak', ['kode' => $permohonan->kode])
            ->with('success', 'Permohonan berhasil dikirim. Simpan kode di bawah ini untuk memeriksa statusnya nanti.');
    }

    /**
     * Halaman penelusuran status permohonan.
     *
     * Sebelumnya permohonan adalah jalur satu arah: pemohon mengirim formulir
     * lalu tidak pernah tahu hasilnya, dan alasan penolakan yang ditulis
     * pengurus di `catatan_admin` tidak pernah sampai ke siapa pun.
     */
    public function lacakPenggunaanGereja(Request $request): View
    {
        $kode = trim((string) $request->query('kode', ''));

        // Sengaja tidak lewat cache: pemohon membuka halaman ini justru untuk
        // melihat keputusan terbaru, dan jumlah kunjungannya kecil.
        $permohonan = $kode === '' ? null : PenggunaanGereja::cariKode($kode);

        return view('penggunaan-gereja-lacak', [
            'kode' => $kode,
            'permohonan' => $permohonan,
            'tidakDitemukan' => $kode !== '' && $permohonan === null,
            'telepon_gereja' => 'tel:'.preg_replace('/[^0-9+]/', '', (string) config('gereja.telepon')),
            'status_disetujui' => PenggunaanGereja::DISETUJUI,
            'status_ditolak' => PenggunaanGereja::DITOLAK,
        ]);
    }

    /**
     * Terima ?tanggal=YYYY-MM-DD; nilai kosong atau tidak valid jatuh ke hari ini
     * alih-alih memunculkan error ke pengunjung.
     */
    private function tanggalDiminta(mixed $input): string
    {
        if (blank($input)) {
            return today()->toDateString();
        }

        try {
            return CarbonImmutable::createFromFormat('Y-m-d', (string) $input)->toDateString();
        } catch (\Throwable) {
            return today()->toDateString();
        }
    }
}
