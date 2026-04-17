<?php

namespace Tests\Feature\User;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;
use Illuminate\Support\Str;

class FavoriteTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);
    }

    public function test_user_can_toggle_favorite(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $favorableId = (string) Str::uuid();

        // 1. Add
        $response = $this->postJson('/api/v1/user/favorites/toggle', [
            'favorable_type' => 'App\Models\Quiz',
            'favorable_id' => $favorableId
        ]);

        $response->assertCreated();
        $this->assertDatabaseHas('user_favorites', ['favorable_id' => $favorableId]);

        // 2. Remove
        $response = $this->postJson('/api/v1/user/favorites/toggle', [
            'favorable_type' => 'App\Models\Quiz',
            'favorable_id' => $favorableId
        ]);

        $response->assertOk();
        $this->assertDatabaseMissing('user_favorites', ['favorable_id' => $favorableId]);
    }

    public function test_user_can_list_favorites(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $user->userFavorites()->create([
            'favorable_type' => 'App\Models\Quiz',
            'favorable_id' => (string) Str::uuid()
        ]);

        $response = $this->getJson('/api/v1/user/favorites');

        $response->assertOk()
            ->assertJsonCount(1, 'data');
    }
}
