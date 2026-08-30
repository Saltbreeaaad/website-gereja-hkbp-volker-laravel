<?php

namespace App\Models;

use App\Casts\JamHarian;
use App\Models\Concerns\MenyegarkanCacheKonten;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JadwalIbadah extends Model
{
    use HasFactory, MenyegarkanCacheKonten;

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
