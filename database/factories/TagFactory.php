<?php

namespace Database\Factories;

use App\Models\Tag;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class TagFactory extends Factory
{
    protected $model = Tag::class;

    public function definition(): array
    {
        return [
            'label' => $this->faker->unique()->word(),
            'slug' => fn (array $attributes) => Str::slug($attributes['label']),
            'category' => $this->faker->randomElement(['domaine', 'secteur']),
        ];
    }
}
