<?php

namespace Database\Factories;

use App\Models\GrowthSector;
use Illuminate\Database\Eloquent\Factories\Factory;

class GrowthSectorFactory extends Factory
{
    protected $model = GrowthSector::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->jobTitle(),
            'description' => $this->faker->paragraph(),
            'color_hex' => $this->faker->hexColor(),
            'icon_code' => 'star',
            'annual_growth' => $this->faker->randomFloat(2, 2, 15),
        ];
    }
}
