<?php

namespace Tests\Feature\User;

use App\Models\User;
use App\Enums\PlatformEnum;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class DeviceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);
    }

    public function test_user_can_register_a_device(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/user/devices', [
            'push_token' => 'token_123',
            'platform' => PlatformEnum::Android->value
        ]);

        $response->assertOk()
            ->assertJsonPath('data.push_token', 'token_123');

        $this->assertDatabaseHas('user_devices', [
            'user_id' => $user->id,
            'push_token' => 'token_123'
        ]);
    }

    public function test_user_can_list_his_devices(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $user->userDevices()->create([
            'push_token' => 'token_A',
            'platform' => PlatformEnum::iOS
        ]);

        $response = $this->getJson('/api/v1/user/devices');

        $response->assertOk()
            ->assertJsonCount(1, 'data');
    }
}
