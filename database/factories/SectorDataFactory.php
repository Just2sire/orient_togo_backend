<?php

namespace Database\Factories;

use App\Models\Career;
use App\Models\GrowthSector;
use App\Models\SectorData;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SectorData>
 */
class SectorDataFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'career_id' => Career::factory(),
            'growth_sector_id' => GrowthSector::factory(),
            'year' => $this->faker->year(),
            'average_salary' => $this->faker->numberBetween(100000, 1000000),
            'employment_rate' => $this->faker->randomFloat(2, 50, 99),
            'offers_per_year' => $this->faker->numberBetween(10, 500),
            'notes' => $this->faker->sentence(),
        ];
    }
}
