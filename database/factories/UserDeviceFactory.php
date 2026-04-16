<?php

namespace Database\Factories;

use App\Models\UserDevice;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UserDevice>
 */
class UserDeviceFactory extends Factory
{
    protected $model = UserDevice::class;

    public function definition(): array
    {
        return [
            'user_id' => UserDevice::factory(),
            'push_token' => fake()->unique()->uuid(),
            'platform' => fake()->randomElement(['android', 'ios', 'web']),
            'user_agent' => fake()->userAgent(),
            'last_active_at' => now(),
        ];
    }

    public function android(): static
    {
        return $this->state(fn (array $attributes) => [
            'platform' => 'android',
        ]);
    }

    public function ios(): static
    {
        return $this->state(fn (array $attributes) => [
            'platform' => 'ios',
        ]);
    }

    public function web(): static
    {
        return $this->state(fn (array $attributes) => [
            'platform' => 'web',
        ]);
    }
}
