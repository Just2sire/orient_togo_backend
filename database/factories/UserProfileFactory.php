<?php

namespace Database\Factories;

use App\Enums\RegionEnum;
use App\Enums\SchoolLevelEnum;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UserProfile>
 */
class UserProfileFactory extends Factory
{
    protected $model = UserProfile::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'username' => fake()->unique()->userName(),
            'level' => fake()->randomElement(SchoolLevelEnum::cases()),
            'class' => 'Terminale D',
            'region' => fake()->randomElement(RegionEnum::cases()),
            'city' => fake()->city(),
            'dark_mode' => fake()->boolean(),
            'notifications_on' => fake()->boolean(80),
            'onboarding_done' => true,
            'quiz_preferences' => [],
            'last_level_seen' => SchoolLevelEnum::HighSchool,
        ];
    }

    public function highSchool(): static
    {
        return $this->state(fn (array $attributes) => [
            'level' => SchoolLevelEnum::HighSchool,
            'class' => fake()->randomElement(['Seconde', 'Première', 'Terminale']),
        ]);
    }

    public function higherEd(): static
    {
        return $this->state(fn (array $attributes) => [
            'level' => SchoolLevelEnum::HigherEd,
            'class' => fake()->randomElement(['L1', 'L2', 'L3', 'M1', 'M2']),
        ]);
    }

    public function onboardingPending(): static
    {
        return $this->state(fn (array $attributes) => [
            'onboarding_done' => false,
        ]);
    }
}
