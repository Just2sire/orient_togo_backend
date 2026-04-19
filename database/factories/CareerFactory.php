<?php

namespace Database\Factories;

use App\Models\Career;
use App\Models\GrowthSector;
use Illuminate\Database\Eloquent\Factories\Factory;

class CareerFactory extends Factory
{
    protected $model = Career::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->jobTitle(),
            'description' => $this->faker->sentence(),
            'required_skills' => $this->faker->words(5),
            'market_demand' => $this->faker->numberBetween(1, 10),
            'salary_min' => $this->faker->numberBetween(200000, 400000),
            'salary_max' => $this->faker->numberBetween(500000, 1000000),
            'is_promising' => $this->faker->boolean(),
            'growth_sector_id' => GrowthSector::factory(),
        ];
    }
}
