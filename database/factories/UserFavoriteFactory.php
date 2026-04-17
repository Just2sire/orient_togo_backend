<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\UserFavorite;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UserFavorite>
 */
class UserFavoriteFactory extends Factory
{
    protected $model = UserFavorite::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'favorable_type' => 'App\Models\Quiz', // Exemple par défaut
            'favorable_id' => fake()->uuid(),
        ];
    }
}
