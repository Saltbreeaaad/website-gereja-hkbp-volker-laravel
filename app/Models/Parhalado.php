<?php

namespace App\Models;

use App\Models\Concerns\MembersihkanBerkas;
use App\Models\Concerns\MenyegarkanCacheKonten;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Parhalado extends Model
{
    use HasFactory, MembersihkanBerkas, MenyegarkanCacheKonten;

    public const KATEGORI = ['Pendeta', 'Parhalado', 'Kategorial'];

    protected $fillable = [
        'nama',
        'foto',
        'kategori',
        'jabatan',
        'bidang',
        'keterangan',
        'telepon',
    ];

    /**
     * Urutan tampil: Pendeta dulu, lalu Parhalado, lalu Kategorial — di dalam
     * tiap kategori diurut menurut bidang dan nama.
     */
    public function scopeUrutTampil(Builder $query): Builder
    {
        return $query
            ->orderByRaw("CASE kategori WHEN 'Pendeta' THEN 0 WHEN 'Parhalado' THEN 1 ELSE 2 END")
            ->orderByRaw("COALESCE(bidang, '')")
            ->orderBy('nama');
    }

    public function scopeKategori(Builder $query, string $kategori): Builder
    {
        return $query->where('kategori', $kategori);
    }

    /** Inisial untuk avatar cadangan saat foto belum diunggah. */
    public function inisial(): string
    {
        return Str::upper(Str::substr(trim((string) $this->nama), 0, 2));
    }

    /** @return list<string> */
    protected function kolomBerkas(): array
    {
        return ['foto'];
    }
}
