<?php

namespace App\Models;

use App\Casts\JamHarian;
use App\Models\Concerns\MencatatAktivitas;
use App\Models\Concerns\MenyegarkanCacheKonten;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $nama_ibadah
 * @property CarbonImmutable $tanggal
 * @property CarbonImmutable|null $waktu
 * @property string|null $pelayan_firman
 * @property string|null $keterangan
 */
class JadwalIbadah extends Model
{
    /**
     * Disebut eksplisit: tebakan Laravel menjamakkannya dengan `-s` Inggris.
     * Lihat migrasi samakan_ejaan_nama_tabel.
     */
    protected $table = 'jadwal_ibadah';

    use HasFactory, MencatatAktivitas, MenyegarkanCacheKonten;

    protected $fillable = ['nama_ibadah', 'tanggal', 'waktu', 'pelayan_firman', 'keterangan'];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
            'waktu' => JamHarian::class,
        ];
    }

    /** Jadwal hari ini dan seterusnya, terurut dari yang paling dekat. */
    public function scopeMendatang(Builder $query): Builder
    {
        return $query->whereDate('tanggal', '>=', today())
            ->orderBy('tanggal')
            ->orderBy('waktu');
    }
}
