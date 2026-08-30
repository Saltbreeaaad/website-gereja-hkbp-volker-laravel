<?php

namespace Database\Factories;

use App\Models\KasGereja;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<KasGereja> */
class KasGerejaFactory extends Factory
{
    protected $model = KasGereja::class;

    public function definition(): array
    {
        return [
            'tanggal' => today(),
            'jenis' => 'Pemasukan',
            'keterangan' => 'Persembahan Minggu',
            'nominal' => 1_000_000,
        ];
    }

    public function pengeluaran(int $nominal = 250_000): static
    {
        return $this->state(fn () => ['jenis' => 'Pengeluaran', 'nominal' => $nominal]);
    }
}
