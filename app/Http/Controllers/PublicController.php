<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePenggunaanGerejaRequest;
use App\Http\Requests\StorePermohonanDoaRequest;
use App\Models\Galeri;
use App\Models\JadwalIbadah;
use App\Models\KasGereja;
use App\Models\Parhalado;
use App\Models\PenggunaanGereja;
use App\Models\PengumumanPenting;
use App\Models\PermohonanDoa;
use App\Models\Renungan;
use App\Models\User;
use App\Models\WartaJemaat;
use App\Notifications\PermohonanDoaMasuk;
use App\Notifications\PermohonanGedungMasuk;
use App\Support\CacheKonten;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PublicController extends Controller
{
    /** Batas atas nomor halaman yang dilayani; lihat halamanDiminta(). */
    private const HALAMAN_MAKSIMAL = 200;

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

        $pengumuman = CacheKonten::ingatModel('beranda:pengumuman', PengumumanPenting::class, fn () => PengumumanPenting::query()
            ->select(['id', 'judul', 'isi', 'tautan', 'label_tautan'])
            ->tayang()
            ->limit(2)
            ->get());

        // Angka kas seluruhnya skalar, jadi aman disimpan apa adanya.
        $kas = CacheKonten::ingat('beranda:kas', function (): array {
            // Satu query dengan conditional aggregation, menggantikan dua query
            // terpisah. `toBase()` karena hasilnya dua bilangan, bukan sebuah
            // transaksi: menghidrasinya jadi KasGereja hanya melahirkan model
            // tanpa id dengan kolom yang tidak ada di tabelnya.
            $jumlah = KasGereja::query()
                ->toBase()
                ->selectRaw("
                    COALESCE(SUM(CASE WHEN jenis = 'Pemasukan' THEN nominal ELSE 0 END), 0) as total_pemasukan,
                    COALESCE(SUM(CASE WHEN jenis = 'Pengeluaran' THEN nominal ELSE 0 END), 0) as total_pengeluaran
                ")
                ->first();

            return [
                'pemasukan' => (int) ($jumlah->total_pemasukan ?? 0),
                'pengeluaran' => (int) ($jumlah->total_pengeluaran ?? 0),
                // Tren 12 bulan terakhir: yang sebenarnya ingin diketahui jemaat
                // adalah kondisi kas belakangan ini, bukan akumulasi sepanjang
                // masa. `->all()` supaya yang tersimpan array biasa.
                'tren' => KasGereja::ringkasanBulanan(12)->all(),
            ];
        });

        return view('beranda', [
            'parhalados' => $parhalados,
            'jadwal_ibadah' => $jadwal_ibadah,
            'warta' => $warta,
            'renungans' => $renungans,
            'galeris' => $galeris,
            'pengumuman' => $pengumuman,
            'total_pemasukan' => $kas['pemasukan'],
            'total_pengeluaran' => $kas['pengeluaran'],
            'tren_kas' => collect($kas['tren']),
            'saldo_akhir' => $kas['pemasukan'] - $kas['pengeluaran'],
        ]);
    }

    public function agenda(): View
    {
        $agenda = CacheKonten::ingatModel('agenda:mendatang', JadwalIbadah::class, fn () => JadwalIbadah::query()
            ->select(['id', 'nama_ibadah', 'tanggal', 'waktu', 'pelayan_firman', 'keterangan'])
            ->mendatang()
            ->limit(24)
            ->get());

        return view('agenda', compact('agenda'));
    }

    public function kalenderAgenda(): Response
    {
        $agenda = JadwalIbadah::query()->mendatang()->get();
        $baris = ['BEGIN:VCALENDAR', 'VERSION:2.0', 'PRODID:-//HKBP Volker//Agenda//ID', 'CALSCALE:GREGORIAN', 'METHOD:PUBLISH'];

        foreach ($agenda as $item) {
            $waktu = $item->waktu?->format('His') ?? '000000';
            $baris = [...$baris,
                'BEGIN:VEVENT',
                'UID:ibadah-'.$item->id.'@'.parse_url(config('app.url'), PHP_URL_HOST),
                'DTSTAMP:'.now('UTC')->format('Ymd\\THis\\Z'),
                'DTSTART;TZID=Asia/Jakarta:'.$item->tanggal->format('Ymd').'T'.$waktu,
                'SUMMARY:'.$this->escapeKalender($item->nama_ibadah),
                'DESCRIPTION:'.$this->escapeKalender($item->keterangan ?: 'Agenda HKBP Volker'),
                'LOCATION:'.$this->escapeKalender(config('gereja.alamat.jalan').', '.config('gereja.alamat.kota')),
                'END:VEVENT',
            ];
        }

        $baris[] = 'END:VCALENDAR';

        return response(implode("\r\n", $this->lipatKalender($baris))."\r\n", 200, [
            'Content-Type' => 'text/calendar; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="agenda-hkbp-volker.ics"',
        ]);
    }

    public function doa(): View
    {
        return view('doa');
    }

    public function storeDoa(StorePermohonanDoaRequest $request): RedirectResponse
    {
        $permohonan = PermohonanDoa::query()->create($request->dataTersimpan());

        // Tanpa ini pokok doa hanya terbaca bila seseorang kebetulan membuka
        // menu Permohonan Doa di panel. Notifikasinya tidak membawa isi doa
        // maupun nama pengirim — lihat catatan di PermohonanDoaMasuk.
        Notification::send(
            User::query()->whereIn('role', [User::ADMIN, User::SEKRETARIS])->get(),
            new PermohonanDoaMasuk($permohonan),
        );

        return to_route('doa')->with('success', 'Pokok doa telah diterima secara privat. Tim pelayanan akan mendoakannya.');
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

        $halaman = $this->halamanDiminta($request);
        $cari = $this->kataPencarian($request);

        $wartas = CacheKonten::ingatHalaman(
            $this->kunciDaftar('warta', $cari, sprintf('%s:hal-%d', $tahun ?? 'semua', $halaman)),
            WartaJemaat::class,
            fn () => WartaJemaat::query()
                ->select(['id', 'judul', 'tanggal', 'file_warta'])
                ->when($tahun, fn ($query) => $query->whereYear('tanggal', $tahun))
                ->when($cari, fn ($query) => $this->cocokkan($query, ['judul'], $cari))
                ->terbaru()
                ->paginate(perPage: 12, page: $halaman)
                ->withPath(route('warta'))
                // Aman disimpan di cache karena tahunnya sudah ikut ke kunci:
                // tautan halaman tidak bisa membawa filter pengunjung lain.
                ->appends(array_filter(['tahun' => $tahun, 'q' => $cari])),
        );

        return view('warta', compact('wartas', 'tahun', 'tahunTersedia', 'cari'));
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
                ->toBase()
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
        $halaman = $this->halamanDiminta($request);
        $cari = $this->kataPencarian($request);
        $kategoriTersedia = CacheKonten::ingat('galeri:kategori', fn (): array => Galeri::query()
            ->distinct()
            ->orderBy('kategori')
            ->pluck('kategori')
            ->filter()
            ->values()
            ->all());
        $kategori = in_array($request->string('kategori')->toString(), $kategoriTersedia, true)
            ? $request->string('kategori')->toString()
            : null;

        $galeris = CacheKonten::ingatHalaman(
            $this->kunciDaftar('galeri', $cari, sprintf('%s:%s:hal-%d', $tahun ?? 'semua', $kategori ?? 'semua', $halaman)),
            Galeri::class,
            fn () => Galeri::query()
                ->select(['id', 'judul', 'kategori', 'foto', 'tanggal'])
                ->when($tahun, fn ($query) => $query->whereYear('tanggal', $tahun))
                ->when($kategori, fn ($query) => $query->where('kategori', $kategori))
                ->when($cari, fn ($query) => $this->cocokkan($query, ['judul'], $cari))
                ->terbaru()
                ->paginate(perPage: 12, page: $halaman)
                ->withPath(route('galeri'))
                ->appends(array_filter(['tahun' => $tahun, 'kategori' => $kategori, 'q' => $cari])),
        );

        return view('galeri', compact('galeris', 'tahun', 'tahunTersedia', 'kategori', 'kategoriTersedia', 'cari'));
    }

    public function arsipRenungan(Request $request): View
    {
        $cari = $this->kataPencarian($request);
        $halaman = $this->halamanDiminta($request);
        $tahunTersedia = $this->tahunTersedia(Renungan::class, 'renungan:tahun');
        $tahun = in_array($request->integer('tahun'), $tahunTersedia, true) ? $request->integer('tahun') : null;

        $renungans = CacheKonten::ingatHalaman(
            $this->kunciDaftar('renungan:arsip', $cari, sprintf('%s:hal-%d', $tahun ?? 'semua', $halaman)),
            Renungan::class,
            fn () => Renungan::query()
                ->select(['id', 'judul', 'tanggal', 'penulis', 'isi'])
                ->when($tahun, fn ($query) => $query->whereYear('tanggal', $tahun))
                ->when($cari, fn ($query) => $this->cocokkan($query, ['judul', 'penulis', 'isi'], $cari))
                ->terbaru()
                ->paginate(perPage: 12, page: $halaman)
                ->withPath(route('renungan.arsip'))
                ->appends(array_filter(['tahun' => $tahun, 'q' => $cari])),
        );

        return view('renungan-arsip', compact('renungans', 'tahun', 'tahunTersedia', 'cari'));
    }

    public function penggunaanGereja(Request $request): View
    {
        $bulanTersedia = collect(range(0, 11))->map(fn (int $selisih): array => [
            'nilai' => today()->startOfMonth()->addMonths($selisih)->format('Y-m'),
            'label' => today()->startOfMonth()->addMonths($selisih)->translatedFormat('F Y'),
        ]);

        // Dicocokkan ke daftar bulan yang memang ditawarkan, bukan sekadar ke
        // pola YYYY-MM: nilai apa pun yang berbentuk benar akan menjadi kunci
        // cache tersendiri, dan pola itu saja mengizinkan sejuta kunci.
        $bulan = $bulanTersedia->pluck('nilai')->contains($request->string('bulan')->toString())
            ? $request->string('bulan')->toString()
            : null;

        // `kontak` dan `catatan_admin` sengaja tidak ikut diambil: keduanya tidak
        // pernah ditampilkan ke publik, jadi tidak perlu ada di memori halaman.
        $penggunaans = CacheKonten::ingatModel('penggunaan-gereja:'.($bulan ?? 'semua'), PenggunaanGereja::class, fn () => PenggunaanGereja::query()
            ->select(['id', 'nama_kegiatan', 'nama_pemohon', 'tanggal', 'waktu_mulai', 'waktu_selesai', 'keterangan', 'status'])
            ->tampilPublik()
            ->when($bulan, fn ($query) => $query
                ->whereYear('tanggal', (int) substr($bulan, 0, 4))
                ->whereMonth('tanggal', (int) substr($bulan, 5, 2)))
            ->get());

        return view('penggunaan-gereja', compact('penggunaans', 'bulan', 'bulanTersedia'));
    }

    public function kalenderPenggunaanGereja(): Response
    {
        $jadwal = PenggunaanGereja::query()
            ->where('status', PenggunaanGereja::DISETUJUI)
            ->whereDate('tanggal', '>=', today())
            ->orderBy('tanggal')
            ->orderBy('waktu_mulai')
            ->get();

        $baris = ['BEGIN:VCALENDAR', 'VERSION:2.0', 'PRODID:-//HKBP Volker//Jadwal Gedung//ID', 'CALSCALE:GREGORIAN', 'METHOD:PUBLISH'];

        foreach ($jadwal as $item) {
            $baris = [...$baris,
                'BEGIN:VEVENT',
                'UID:'.$item->kode.'@'.parse_url(config('app.url'), PHP_URL_HOST),
                'DTSTAMP:'.now('UTC')->format('Ymd\THis\Z'),
                'DTSTART;TZID=Asia/Jakarta:'.$item->tanggal->format('Ymd').'T'.$item->waktu_mulai->format('His'),
                'DTEND;TZID=Asia/Jakarta:'.$item->tanggal->format('Ymd').'T'.$item->waktu_selesai->format('His'),
                'SUMMARY:'.$this->escapeKalender($item->nama_kegiatan),
                'DESCRIPTION:'.$this->escapeKalender($item->keterangan ?: 'Penggunaan gedung gereja'),
                'LOCATION:'.$this->escapeKalender(config('gereja.alamat.jalan').', '.config('gereja.alamat.kota')),
                'END:VEVENT',
            ];
        }

        $baris[] = 'END:VCALENDAR';

        return response(implode("\r\n", $this->lipatKalender($baris))."\r\n", 200, [
            'Content-Type' => 'text/calendar; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="jadwal-penggunaan-gereja.ics"',
        ]);
    }

    private function escapeKalender(string $nilai): string
    {
        return str_replace(['\\', ';', ',', "\r\n", "\n", "\r"], ['\\\\', '\\;', '\\,', '\\n', '\\n', '\\n'], $nilai);
    }

    /**
     * Lipat baris panjang sesuai RFC 5545 §3.1.
     *
     * Satu baris konten iCalendar tidak boleh melebihi 75 oktet; sisanya
     * disambung ke baris berikutnya yang diawali satu spasi. `keterangan`
     * permohonan gedung boleh sampai 1.000 karakter dan selama ini ditulis
     * sebagai satu baris DESCRIPTION yang jauh melampaui batas itu. Google
     * Calendar dan Apple memaafkannya; pengurai yang taat menolak berkasnya.
     *
     * Dihitung dalam oktet, bukan karakter: judul berhuruf non-ASCII memakan
     * lebih dari satu bita, dan memotong per karakter tetap bisa melahirkan
     * baris yang terlalu panjang. mb_str_split menjaga agar potongannya tidak
     * pernah jatuh di tengah sebuah karakter UTF-8.
     *
     * @param  list<string>  $baris
     * @return list<string>
     */
    private function lipatKalender(array $baris): array
    {
        $hasil = [];

        foreach ($baris as $satuBaris) {
            if (strlen($satuBaris) <= 75) {
                $hasil[] = $satuBaris;

                continue;
            }

            $sisa = $satuBaris;
            // Potongan pertama 75 oktet; lanjutannya 74, karena satu oktet
            // sudah terpakai oleh spasi penanda sambungan.
            $batas = 75;

            while (strlen($sisa) > $batas) {
                $potongan = '';

                foreach (mb_str_split($sisa) as $karakter) {
                    if (strlen($potongan) + strlen($karakter) > $batas) {
                        break;
                    }

                    $potongan .= $karakter;
                }

                $hasil[] = ($batas === 75 ? '' : ' ').$potongan;
                $sisa = substr($sisa, strlen($potongan));
                $batas = 74;
            }

            $hasil[] = ' '.$sisa;
        }

        return $hasil;
    }

    public function storePenggunaanGereja(StorePenggunaanGerejaRequest $request): RedirectResponse
    {
        $permohonan = PenggunaanGereja::create([
            ...$request->dataTersimpan(),
            'status' => PenggunaanGereja::MENUNGGU,
        ]);

        // Pengurus tidak lagi harus kebetulan membuka panel untuk tahu ada
        // permohonan masuk.
        //
        // Dikirim ke yang benar-benar meninjaunya — sekretaris dan administrator,
        // sesuai PenggunaanGerejaPolicy — bukan ke seluruh akun. Bendahara tidak
        // punya wewenang menyetujui permohonan gedung, jadi lonceng notifikasinya
        // hanya terisi hal yang tidak bisa ia tindak lanjuti.
        Notification::send(
            User::query()->whereIn('role', [User::ADMIN, User::SEKRETARIS])->get(),
            new PermohonanGedungMasuk($permohonan),
        );

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
     * Nomor halaman yang diminta, minimal 1.
     *
     * Diambil sendiri dari query, bukan diserahkan ke `Paginator` untuk
     * menebaknya. Livewire mengganti `Paginator::currentPageResolver` dan
     * `currentPathResolver` secara global begitu sebuah komponen berpaginasi
     * dijalankan, dan tidak mengembalikannya. Halaman publik tidak memuat
     * Livewire, tetapi bergantung pada keadaan global yang bisa diubah paket
     * lain membuat nomor halaman dan tautan paginasi bisa berubah tanpa ada
     * yang menyentuh kode ini — dan hasilnya ikut tersimpan ke cache.
     */
    private function halamanDiminta(Request $request): int
    {
        // Dibatasi di atas juga. Nomor halaman ikut ke kunci cache, jadi
        // `?page=` yang tidak dibatasi adalah jalan yang sama untuk membanjiri
        // tabel cache dengan baris sekali-pakai. Dua belas butir per halaman
        // membuat batas ini setara ~2.400 baris — jauh di atas isi terbanyak
        // yang pernah dimiliki situs ini, dan di luar itu paginator memang
        // hanya mengembalikan halaman kosong.
        return max(1, min($request->integer('page', 1), self::HALAMAN_MAKSIMAL));
    }

    /**
     * Karakter yang menandai escape pada klausa LIKE ... ESCAPE.
     *
     * Bukan backslash. Backslash adalah pilihan yang tampak wajar tetapi tidak
     * portabel: MySQL memproses escape di dalam string literal, sehingga
     * `ESCAPE ''` tidak terbaca sebagai satu backslash di sana, sementara
     * SQLite membacanya apa adanya. Tanda seru tidak istimewa bagi keduanya.
     */
    private const ESCAPE_LIKE = '!';

    /**
     * Cocokkan kata pencarian ke beberapa kolom sekaligus.
     *
     * `%` dan `_` di dalam masukan pengunjung di-escape, bukan diteruskan
     * mentah. Keduanya adalah wildcard LIKE: mencari "diskon 50%" sebelumnya
     * berarti mencari "diskon 50" diikuti apa saja, dan "A_B" cocok dengan
     * "AXB". Bukan lubang keamanan — nilainya tetap terikat sebagai parameter —
     * tetapi hasil yang diterima jemaat bukan yang mereka minta.
     *
     * @template TModel of Model
     *
     * @param  Builder<TModel>  $query
     * @param  list<string>  $kolom
     * @return Builder<TModel>
     */
    private function cocokkan(Builder $query, array $kolom, string $cari): Builder
    {
        $pola = '%'.$this->escapeLike($cari).'%';

        return $query->where(function (Builder $q) use ($kolom, $pola): void {
            foreach ($kolom as $satuKolom) {
                $q->orWhereRaw("{$satuKolom} LIKE ? ESCAPE '".self::ESCAPE_LIKE."'", [$pola]);
            }
        });
    }

    /** Netralkan wildcard LIKE — dan karakter escape-nya sendiri lebih dulu. */
    private function escapeLike(string $nilai): string
    {
        $e = self::ESCAPE_LIKE;

        return str_replace([$e, '%', '_'], [$e.$e, $e.'%', $e.'_'], $nilai);
    }

    private function kataPencarian(Request $request): string
    {
        return Str::limit(trim($request->string('q')->toString()), 60, '');
    }

    /**
     * Kunci cache untuk satu halaman daftar, atau `null` bila hasilnya tidak
     * layak disimpan.
     *
     * Kunci yang mengandung kata pencarian bebas tidak pernah dipakai ulang:
     * tiap kata baru menulis satu baris cache yang hidup enam jam, dan
     * pengunjung mana pun bisa menggelembungkan tabel cache dengan menembakkan
     * `?q=` acak. Hasil pencarian karena itu selalu diambil langsung dari
     * basis data — jumlahnya kecil dibanding daftar biasa, dan indeksnya sama.
     */
    private function kunciDaftar(string $awalan, string $cari, string $sisa): ?string
    {
        return $cari === '' ? "{$awalan}:{$sisa}" : null;
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
