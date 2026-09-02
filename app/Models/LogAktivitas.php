<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int|null $user_id
 * @property string $aksi
 * @property string $subjek_tipe
 * @property int|null $subjek_id
 * @property string|null $ringkasan
 * @property array<string, mixed>|null $perubahan
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property CarbonImmutable $created_at
 */
class LogAktivitas extends Model
{
    public const UPDATED_AT = null;

    protected $table = 'log_aktivitas';

    protected $fillable = [
        'user_id',
        'aksi',
        'subjek_tipe',
        'subjek_id',
        'ringkasan',
        'perubahan',
        'ip_address',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'perubahan' => 'array',
            'created_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
