<?php

namespace Tests\Feature\User;

use App\Models\User;
use App\Enums\SchoolLevelEnum;
use App\Enums\RegionEnum;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);
    }

    public function test_user_can_get_his_profile_auto_created(): void
    {
        $user = User::factory()->create(['email' => 'test@example.com']);
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/user/profile');

        $response->assertOk()
            ->assertJsonPath('data.username', 'test')
            ->assertJsonPath('data.onboarding_done', false);
    }

    public function test_user_can_update_his_profile(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->putJson('/api/v1/user/profile', [
            'username' => 'new_username',
            'level' => SchoolLevelEnum::HighSchool->value,
            'region' => RegionEnum::Kara->value,
            'city' => 'Kara'
        ]);

        $response->assertOk()
            ->assertJsonPath('data.username', 'new_username')
            ->assertJsonPath('data.region', RegionEnum::Kara->value);
    }

    public function test_user_can_complete_onboarding(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/user/profile'); // Create it

        $response = $this->patchJson('/api/v1/user/profile/onboarding');

        $response->assertOk()
            ->assertJsonPath('data.onboarding_done', true);
    }
}
