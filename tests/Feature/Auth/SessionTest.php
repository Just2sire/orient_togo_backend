<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SessionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);
    }

    public function test_can_get_authenticated_user_profile(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/auth/me');

        $response->assertOk()
            ->assertJsonPath('data.id', $user->id)
            ->assertJsonPath('message', 'Profil récupéré.');
    }

    public function test_cannot_get_profile_without_authentication(): void
    {
        $response = $this->getJson('/api/v1/auth/me');

        $response->assertStatus(401);
    }

    public function test_can_logout_current_device(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/auth/logout');
        if ($response->status() !== 200) dd($response->json());

        $response->assertOk()
            ->assertJsonPath('message', 'Déconnexion réussie.');

        $this->assertCount(0, $user->tokens);
    }

    public function test_can_logout_all_devices(): void
    {
        $user = User::factory()->create();
        $user->createToken('device1');
        $user->createToken('device2');
        
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/auth/logout', ['all_devices' => true]);

        $response->assertOk();
        $this->assertCount(0, $user->fresh()->tokens);
    }
}
