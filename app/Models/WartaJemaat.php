<?php

namespace App\Models;

use App\Models\Concerns\MembersihkanBerkas;
use App\Models\Concerns\MenyegarkanCacheKonten;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class WartaJemaat extends Model
{
    use HasFactory, MembersihkanBerkas, MenyegarkanCacheKonten;

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
