<?php

namespace Database\Factories;

use App\Enums\LevelEnum;
use App\Models\Course;
use App\Models\Establishment;
use Illuminate\Database\Eloquent\Factories\Factory;

class CourseFactory extends Factory
{
    protected $model = Course::class;

    public function definition(): array
    {
        return [
            'establishment_id' => Establishment::factory(),
            'name' => $this->faker->words(3, true),
            'level' => $this->faker->randomElement(LevelEnum::cases()),
            'description' => $this->faker->paragraph(),
            'duration_months' => $this->faker->numberBetween(12, 60),
            'annual_fees' => $this->faker->randomFloat(2, 10000, 500000),
        ];
    }
}
