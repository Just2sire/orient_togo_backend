<?php

namespace Tests\Feature;

use App\Models\Career;
use App\Models\GrowthSector;
use App\Models\SectorData;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SectorDataTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_can_list_sector_datas(): void
    {
        SectorData::factory()->count(3)->create();

        $this->getJson('/api/v1/sector-datas')
            ->assertOk()
            ->assertJsonStructure([
                'success', 'message',
                'data',
                'meta' => ['pagination'],
            ]);
    }

    public function test_can_create_sector_data(): void
    {
        $career = Career::factory()->create();
        $growthSector = GrowthSector::factory()->create();
        $data = [
            'career_id' => $career->id,
            'growth_sector_id' => $growthSector->id,
            'year' => 2024,
            'average_salary' => 350000,
            'employment_rate' => 85.5,
            'offers_per_year' => 120,
            'notes' => 'Notes sur les données',
        ];

        $this->postJson('/api/v1/sector-datas', $data)
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.year', 2024);
    }

    public function test_can_show_sector_data(): void
    {
        $sectorData = SectorData::factory()->create();

        $this->getJson("/api/v1/sector-datas/{$sectorData->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $sectorData->id);
    }

    public function test_can_update_sector_data(): void
    {
        $sectorData = SectorData::factory()->create();

        $this->putJson("/api/v1/sector-datas/{$sectorData->id}", ['year' => 2025])
            ->assertOk()
            ->assertJsonPath('data.year', 2025);
    }

    public function test_can_delete_sector_data(): void
    {
        $sectorData = SectorData::factory()->create();

        $this->deleteJson("/api/v1/sector-datas/{$sectorData->id}")
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseMissing('sector_data', ['id' => $sectorData->id]);
    }

    public function test_returns_404_for_non_existent_sector_data(): void
    {
        $this->getJson('/api/v1/sector-datas/00000000-0000-0000-0000-000000000000')
            ->assertNotFound();
    }

    public function test_fails_creation_without_required_data(): void
    {
        $this->postJson('/api/v1/sector-datas', [])
            ->assertUnprocessable()
            ->assertJsonPath('success', false);
    }
}
