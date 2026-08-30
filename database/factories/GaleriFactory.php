<?php

namespace Database\Factories;

use App\Models\Galeri;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Galeri> */
class GaleriFactory extends Factory
{
    protected $model = Galeri::class;

    public function definition(): array
    {
        return [
            'judul' => $this->faker->sentence(3),
            'foto' => null,
            'tanggal' => today(),
        ];
    }
}
