<?php

namespace Database\Factories;

use App\Enums\PlatformEnum;
use App\Models\User;
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
            'user_id' => User::factory(),
            'push_token' => fake()->unique()->sha256(),
            'platform' => fake()->randomElement(PlatformEnum::cases()),
            'user_agent' => fake()->userAgent(),
            'last_active_at' => now(),
        ];
    }

    public function android(): static
    {
        return $this->state(fn (array $attributes) => [
            'platform' => PlatformEnum::Android,
        ]);
    }

    public function ios(): static
    {
        return $this->state(fn (array $attributes) => [
            'platform' => PlatformEnum::iOS,
        ]);
    }

    public function web(): static
    {
        return $this->state(fn (array $attributes) => [
            'platform' => PlatformEnum::Web,
        ]);
    }
}
