<?php

namespace Database\Factories;

use App\Enums\EstablishmentTypeEnum;
use App\Enums\RegionEnum;
use App\Models\Establishment;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class EstablishmentFactory extends Factory
{
    protected $model = Establishment::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->company(),
            'slug' => fn (array $attributes) => Str::slug($attributes['name']),
            'type' => $this->faker->randomElement(EstablishmentTypeEnum::cases()),
            'region' => $this->faker->randomElement(RegionEnum::cases()),
            'city' => $this->faker->city(),
            'address' => $this->faker->address(),
            'description' => $this->faker->paragraph(),
            'is_public' => $this->faker->boolean(),
            'is_verified' => true,
            'verification_status' => 'approved',
            'is_selected' => $this->faker->boolean(),
        ];
    }
}
