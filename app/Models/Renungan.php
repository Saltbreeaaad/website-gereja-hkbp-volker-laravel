<?php

namespace App\Models;

use App\Models\Concerns\MembersihkanBerkas;
use App\Models\Concerns\MenyegarkanCacheKonten;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Renungan extends Model
{
    use HasFactory, MembersihkanBerkas, MenyegarkanCacheKonten;

    protected $fillable = ['judul', 'tanggal', 'penulis', 'foto', 'isi'];

    protected function casts(): array
    {
        return ['tanggal' => 'date'];
    }

    public function scopeTerbaru(Builder $query): Builder
    {
        return $query->orderByDesc('tanggal')->orderByDesc('id');
    }

    /** Ringkasan untuk kartu di beranda dan meta description. */
    public function ringkasan(int $panjang = 160): string
    {
        return Str::limit(trim(preg_replace('/\s+/', ' ', (string) $this->isi)), $panjang);
    }

    /** @return list<string> */
    protected function kolomBerkas(): array
    {
        return ['foto'];
    }
}
