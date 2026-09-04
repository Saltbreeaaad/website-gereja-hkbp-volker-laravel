<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string|null $nama
 * @property string|null $kontak
 * @property string $isi
 * @property string $status
 * @property string|null $catatan_pengurus
 * @property CarbonImmutable|null $ditindaklanjuti_pada
 */
class PermohonanDoa extends Model
{
    /**
     * Disebut eksplisit: tebakan Laravel menjamakkannya dengan `-s` Inggris.
     * Lihat migrasi samakan_ejaan_nama_tabel.
     */
    protected $table = 'permohonan_doa';

    use HasFactory;

    public const BARU = 'baru';

    public const DITINDAKLANJUTI = 'ditindaklanjuti';

    public const STATUS = [self::BARU => 'Baru', self::DITINDAKLANJUTI => 'Sudah ditindaklanjuti'];

    protected $fillable = ['nama', 'kontak', 'isi', 'status', 'catatan_pengurus', 'ditindaklanjuti_pada'];

    protected function casts(): array
    {
        return ['ditindaklanjuti_pada' => 'datetime'];
    }
}
