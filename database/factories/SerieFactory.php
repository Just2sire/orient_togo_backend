<?php

namespace Database\Factories;

use App\Models\Serie;
use Illuminate\Database\Eloquent\Factories\Factory;

class SerieFactory extends Factory
{
    protected $model = Serie::class;

    public function definition(): array
    {
        return [
            'code' => $this->faker->unique()->lexify('??'),
            'label' => $this->faker->words(3, true),
            'description' => $this->faker->paragraph(),
            'minimum_average' => $this->faker->randomFloat(2, 10, 14),
            'is_active' => true,
            'order' => $this->faker->numberBetween(1, 10),
        ];
    }
}
