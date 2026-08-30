<?php

namespace App\Models;

use App\Casts\JamHarian;
use App\Models\Concerns\MenyegarkanCacheKonten;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PenggunaanGereja extends Model
{
    use HasFactory, MenyegarkanCacheKonten;

    public const MENUNGGU = 'Menunggu';

    public const DISETUJUI = 'Disetujui';

    public const DITOLAK = 'Ditolak';

    public const STATUS = [self::MENUNGGU, self::DISETUJUI, self::DITOLAK];

    /**
     * Huruf dan angka yang dipakai kode penelusuran.
     *
     * Tanpa 0/O dan 1/I/L: kode ini dibacakan lewat telepon dan disalin ulang
     * dari catatan tangan, dan pasangan itulah yang paling sering tertukar.
     */
    private const ABJAD_KODE = '23456789ABCDEFGHJKMNPQRSTUVWXYZ';

    protected $fillable = [
        'kode',
        'nama_kegiatan',
        'nama_pemohon',
        'kontak',
        'tanggal',
        'waktu_mulai',
        'waktu_selesai',
        'keterangan',
        'status',
        'catatan_admin',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
            'waktu_mulai' => JamHarian::class,
            'waktu_selesai' => JamHarian::class,
        ];
    }

    /** Jadwal yang layak ditampilkan ke publik: belum lewat dan belum ditolak. */
    public function scopeTampilPublik(Builder $query): Builder
    {
        return $query
            ->where('status', '!=', self::DITOLAK)
            ->whereDate('tanggal', '>=', today())
            ->orderBy('tanggal')
            ->orderBy('waktu_mulai');
    }

    /**
     * Cek apakah rentang waktu tumpang tindih dengan jadwal lain yang berstatus
     * Disetujui pada tanggal yang sama. Hanya jadwal Disetujui yang dianggap
     * "terkunci" — jadwal Menunggu boleh saling tumpang tindih sampai salah
     * satunya diputuskan pengurus.
     *
     * Perbandingan dilakukan di SQL (bukan memuat seluruh baris ke PHP) dengan
     * format jam yang sudah dinormalkan oleh cast JamHarian.
     */
    public static function hasApprovedConflict(
        string $tanggal,
        string $waktuMulai,
        string $waktuSelesai,
        ?int $excludeId = null,
    ): bool {
        $mulai = CarbonImmutable::parse($waktuMulai)->format('H:i:s');
        $selesai = CarbonImmutable::parse($waktuSelesai)->format('H:i:s');

        return static::query()
            ->where('status', self::DISETUJUI)
            ->whereDate('tanggal', $tanggal)
            ->when($excludeId, fn (Builder $query) => $query->whereKeyNot($excludeId))
            // Dua rentang bertumpuk bila masing-masing mulai sebelum yang lain selesai.
            ->whereRaw('TIME(waktu_mulai) < ?', [$selesai])
            ->whereRaw('TIME(waktu_selesai) > ?', [$mulai])
            ->exists();
    }

    /**
     * Kode acak 8 karakter. Ruangnya 31^8 (~8,5 x 10^11), jadi kode orang lain
     * tidak bisa ditebak dengan mencoba-coba — halaman penelusuran juga
     * dibatasi lajunya untuk menutup penebakan beruntun.
     */
    public static function kodeBaru(): string
    {
        do {
            $kode = 'WG-'.collect(range(1, 8))
                ->map(fn (): string => self::ABJAD_KODE[random_int(0, strlen(self::ABJAD_KODE) - 1)])
                ->implode('');
        } while (static::query()->where('kode', $kode)->exists());

        return $kode;
    }

    protected static function booted(): void
    {
        static::creating(function (self $permohonan): void {
            $permohonan->kode ??= static::kodeBaru();
        });
    }

    /**
     * Cari permohonan menurut kode yang diketik pemohon.
     *
     * Spasi, tanda hubung, dan huruf kecil dimaafkan: kode ini disalin ulang
     * dari catatan tangan atau pesan WhatsApp, bukan ditempel apa adanya.
     */
    public static function cariKode(string $kode): ?self
    {
        // Buang segala pemisah, samakan huruf besar, lalu lepas awalan "WG"
        // bila pemohon ikut menyalinnya — awalan itu dipasang kembali di bawah.
        $bersih = Str::upper(preg_replace('/[^A-Za-z0-9]/', '', $kode));
        $bersih = Str::startsWith($bersih, 'WG') ? Str::substr($bersih, 2) : $bersih;

        if ($bersih === '') {
            return null;
        }

        return static::query()->where('kode', 'WG-'.$bersih)->first();
    }

    /** Label ramah untuk status, dipakai halaman penelusuran. */
    public function penjelasanStatus(): string
    {
        return match ($this->status) {
            self::DISETUJUI => 'Permohonan Anda sudah disetujui. Gedung gereja dapat dipakai sesuai tanggal dan jam di atas.',
            self::DITOLAK => 'Permohonan Anda belum dapat disetujui.',
            default => 'Permohonan Anda sudah diterima dan sedang menunggu peninjauan pengurus gereja.',
        };
    }
}
