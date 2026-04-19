<?php

namespace Tests\Feature;

use App\Models\GrowthSector;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GrowthSectorTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_can_list_growth_sectors(): void
    {
        GrowthSector::factory()->count(3)->create();

        $this->getJson('/api/v1/growth-sectors')
            ->assertOk()
            ->assertJsonStructure([
                'success', 'message',
                'data',
                'meta' => ['pagination'],
            ]);
    }

    public function test_can_create_growth_sector(): void
    {
        $data = [
            'name' => 'Nouveau Secteur',
            'description' => 'Description du secteur',
            'color_hex' => '#FF0000',
            'icon_code' => 'computer',
            'annual_growth' => 5.5,
            'opportunities_description' => 'Opportunités du secteur',
        ];

        $this->postJson('/api/v1/growth-sectors', $data)
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.name', 'Nouveau Secteur');
    }

    public function test_can_show_growth_sector(): void
    {
        $growthSector = GrowthSector::factory()->create();

        $this->getJson("/api/v1/growth-sectors/{$growthSector->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $growthSector->id);
    }

    public function test_can_update_growth_sector(): void
    {
        $growthSector = GrowthSector::factory()->create();

        $this->putJson("/api/v1/growth-sectors/{$growthSector->id}", ['name' => 'Modifié'])
            ->assertOk()
            ->assertJsonPath('data.name', 'Modifié');
    }

    public function test_can_delete_growth_sector(): void
    {
        $growthSector = GrowthSector::factory()->create();

        $this->deleteJson("/api/v1/growth-sectors/{$growthSector->id}")
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseMissing('growth_sectors', ['id' => $growthSector->id]);
    }

    public function test_returns_404_for_non_existent_growth_sector(): void
    {
        $this->getJson('/api/v1/growth-sectors/00000000-0000-0000-0000-000000000000')
            ->assertNotFound();
    }

    public function test_fails_creation_without_required_data(): void
    {
        $this->postJson('/api/v1/growth-sectors', [])
            ->assertUnprocessable()
            ->assertJsonPath('success', false);
    }
}
