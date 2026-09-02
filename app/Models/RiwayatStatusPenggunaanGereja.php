<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $penggunaan_gereja_id
 * @property int|null $user_id
 * @property string|null $status_lama
 * @property string $status_baru
 * @property string|null $catatan
 * @property CarbonImmutable $created_at
 */
class RiwayatStatusPenggunaanGereja extends Model
{
    public const UPDATED_AT = null;

    protected $table = 'riwayat_status_penggunaan_gerejas';

    protected $fillable = [
        'penggunaan_gereja_id',
        'user_id',
        'status_lama',
        'status_baru',
        'catatan',
    ];

    protected function casts(): array
    {
        return ['created_at' => 'datetime'];
    }

    /** @return BelongsTo<PenggunaanGereja, $this> */
    public function permohonan(): BelongsTo
    {
        return $this->belongsTo(PenggunaanGereja::class, 'penggunaan_gereja_id');
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
