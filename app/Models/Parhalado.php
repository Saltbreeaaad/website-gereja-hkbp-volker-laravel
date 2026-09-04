<?php

namespace App\Models;

use App\Models\Concerns\MembersihkanBerkas;
use App\Models\Concerns\MencatatAktivitas;
use App\Models\Concerns\MengoptimalkanGambar;
use App\Models\Concerns\MenyegarkanCacheKonten;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property string $nama
 * @property string|null $foto
 * @property string $kategori
 * @property string|null $jabatan
 * @property string|null $bidang
 * @property string|null $keterangan
 * @property string|null $telepon
 */
class Parhalado extends Model
{
    /**
     * Disebut eksplisit: tebakan Laravel menjamakkannya dengan `-s` Inggris.
     * Lihat migrasi samakan_ejaan_nama_tabel.
     */
    protected $table = 'parhalado';

    use HasFactory, MembersihkanBerkas, MencatatAktivitas, MengoptimalkanGambar, MenyegarkanCacheKonten;

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

    protected function kolomGambar(): array
    {
        return ['foto' => 800];
    }
}
