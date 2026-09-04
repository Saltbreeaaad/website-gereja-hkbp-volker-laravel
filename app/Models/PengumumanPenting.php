<?php

namespace App\Models;

use App\Models\Concerns\MencatatAktivitas;
use App\Models\Concerns\MenyegarkanCacheKonten;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $judul
 * @property string $isi
 * @property string|null $tautan
 * @property string|null $label_tautan
 * @property CarbonImmutable|null $mulai_tayang
 * @property CarbonImmutable|null $selesai_tayang
 * @property bool $aktif
 */
class PengumumanPenting extends Model
{
    /**
     * Disebut eksplisit: tebakan Laravel menjamakkannya dengan `-s` Inggris.
     * Lihat migrasi samakan_ejaan_nama_tabel.
     */
    protected $table = 'pengumuman_penting';

    use HasFactory, MencatatAktivitas, MenyegarkanCacheKonten;

    protected $fillable = ['judul', 'isi', 'tautan', 'label_tautan', 'mulai_tayang', 'selesai_tayang', 'aktif'];

    protected function casts(): array
    {
        return ['aktif' => 'boolean', 'mulai_tayang' => 'datetime', 'selesai_tayang' => 'datetime'];
    }

    public function scopeTayang(Builder $query): Builder
    {
        return $query->where('aktif', true)
            ->where(fn (Builder $query) => $query->whereNull('mulai_tayang')->orWhere('mulai_tayang', '<=', now()))
            ->where(fn (Builder $query) => $query->whereNull('selesai_tayang')->orWhere('selesai_tayang', '>=', now()))
            ->orderByDesc('created_at');
    }
}
