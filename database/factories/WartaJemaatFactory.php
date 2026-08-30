<?php

namespace Database\Factories;

use App\Models\WartaJemaat;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<WartaJemaat> */
class WartaJemaatFactory extends Factory
{
    protected $model = WartaJemaat::class;

    public function definition(): array
    {
        return [
            'judul' => 'Warta Jemaat '.$this->faker->word(),
            'tanggal' => today(),
            'file_warta' => 'warta-jemaat/contoh.pdf',
        ];
    }

    public function tanpaBerkas(): static
    {
        return $this->state(fn () => ['file_warta' => null]);
    }
}
