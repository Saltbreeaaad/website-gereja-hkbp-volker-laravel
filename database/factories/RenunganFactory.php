<?php

namespace Database\Factories;

use App\Models\Renungan;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Renungan> */
class RenunganFactory extends Factory
{
    protected $model = Renungan::class;

    public function definition(): array
    {
        return [
            'judul' => $this->faker->sentence(4),
            'tanggal' => today(),
            'penulis' => $this->faker->name(),
            'foto' => null,
            'isi' => $this->faker->paragraphs(3, true),
        ];
    }
}
