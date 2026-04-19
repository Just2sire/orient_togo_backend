<?php

namespace Database\Factories;

use App\Models\Field;
use Illuminate\Database\Eloquent\Factories\Factory;

class FieldFactory extends Factory
{
    protected $model = Field::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->words(3, true),
            'description' => $this->faker->paragraph(),
            'estimated_duration_years' => $this->faker->randomFloat(1, 2, 8),
            'main_domain' => $this->faker->randomElement(['sante', 'ingenierie', 'droit', 'commerce']),
            'is_selected' => $this->faker->boolean(),
        ];
    }
}
