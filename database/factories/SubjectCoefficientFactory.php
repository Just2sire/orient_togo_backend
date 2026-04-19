<?php

namespace Database\Factories;

use App\Models\Serie;
use App\Models\SubjectCoefficient;
use Illuminate\Database\Eloquent\Factories\Factory;

class SubjectCoefficientFactory extends Factory
{
    protected $model = SubjectCoefficient::class;

    public function definition(): array
    {
        return [
            'serie_id' => Serie::factory(),
            'subject_name' => $this->faker->word(),
            'coefficient' => $this->faker->numberBetween(1, 7),
            'minimum_grade' => $this->faker->randomFloat(2, 7, 10),
        ];
    }
}
