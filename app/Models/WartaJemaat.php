<?php

namespace App\Models;

use App\Models\Concerns\MembersihkanBerkas;
use App\Models\Concerns\MencatatAktivitas;
use App\Models\Concerns\MenyegarkanCacheKonten;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

/**
 * @property int $id
 * @property string $judul
 * @property CarbonImmutable $tanggal
 * @property string|null $file_warta
 */
class WartaJemaat extends Model
{
    use HasFactory, MembersihkanBerkas, MencatatAktivitas, MenyegarkanCacheKonten;

    protected $fillable = ['judul', 'tanggal', 'file_warta'];

    protected function casts(): array
    {
        return ['tanggal' => 'date'];
    }

    public function scopeTerbaru(Builder $query): Builder
    {
        return $query->orderByDesc('tanggal')->orderByDesc('id');
    }

    /** URL unduhan, atau null bila berkas belum diunggah / sudah terhapus. */
    public function urlUnduhan(): ?string
    {
        if (blank($this->file_warta)) {
            return null;
        }

        return Storage::disk('public')->url($this->file_warta);
    }

    /** @return list<string> */
    protected function kolomBerkas(): array
    {
        return ['file_warta'];
    }
}
