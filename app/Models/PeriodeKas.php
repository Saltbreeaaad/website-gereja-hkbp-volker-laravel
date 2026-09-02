<?php

namespace App\Models;

use App\Models\Concerns\MencatatAktivitas;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property string $periode
 * @property int $saldo_awal
 * @property CarbonImmutable|null $ditutup_at
 * @property int|null $ditutup_oleh
 */
class PeriodeKas extends Model
{
    use MencatatAktivitas;

    protected $table = 'periode_kas';

    protected $fillable = ['periode', 'saldo_awal', 'ditutup_at', 'ditutup_oleh'];

    protected function casts(): array
    {
        return ['ditutup_at' => 'datetime'];
    }

    /** @return BelongsTo<User, $this> */
    public function penutup(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ditutup_oleh');
    }

    public function sudahDitutup(): bool
    {
        return $this->ditutup_at !== null;
    }
}
