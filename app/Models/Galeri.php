<?php

namespace App\Models;

use App\Models\Concerns\MembersihkanBerkas;
use App\Models\Concerns\MencatatAktivitas;
use App\Models\Concerns\MengoptimalkanGambar;
use App\Models\Concerns\MenyegarkanCacheKonten;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $judul
 * @property string $kategori
 * @property string|null $foto
 * @property CarbonImmutable $tanggal
 */
class Galeri extends Model
{
    use HasFactory, MembersihkanBerkas, MencatatAktivitas, MengoptimalkanGambar, MenyegarkanCacheKonten;

    protected $fillable = ['judul', 'kategori', 'foto', 'tanggal'];

    protected function casts(): array
    {
        return ['tanggal' => 'date'];
    }

    public function scopeTerbaru(Builder $query): Builder
    {
        return $query->orderByDesc('tanggal')->orderByDesc('id');
    }

    /** @return list<string> */
    protected function kolomBerkas(): array
    {
        return ['foto'];
    }

    protected function kolomGambar(): array
    {
        return ['foto' => 1600];
    }
}
