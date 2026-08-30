<?php

namespace Database\Factories;

use App\Models\JadwalIbadah;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<JadwalIbadah> */
class JadwalIbadahFactory extends Factory
{
    protected $model = JadwalIbadah::class;

    public function definition(): array
    {
        return [
            'nama_ibadah' => 'Ibadah Minggu',
            'tanggal' => today(),
            'waktu' => '09:00',
            'pelayan_firman' => $this->faker->name(),
            'keterangan' => null,
        ];
    }
}
