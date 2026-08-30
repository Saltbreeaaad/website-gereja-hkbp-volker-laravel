<?php

namespace Database\Factories;

use App\Models\Parhalado;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Parhalado> */
class ParhaladoFactory extends Factory
{
    protected $model = Parhalado::class;

    public function definition(): array
    {
        return [
            'nama' => $this->faker->name(),
            'foto' => null,
            'kategori' => 'Parhalado',
            'jabatan' => 'Sintua',
            'bidang' => 'Dewan Koinonia',
            'keterangan' => null,
            'telepon' => null,
        ];
    }
}
