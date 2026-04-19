<?php

namespace Tests\Feature;

use App\Models\Serie;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SerieTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_can_list_series(): void
    {
        Serie::factory()->count(3)->create();

        $this->getJson('/api/v1/series')
            ->assertOk()
            ->assertJsonStructure([
                'success', 'message',
                'data',
                'meta' => ['pagination'],
            ]);
    }

    public function test_can_create_serie(): void
    {
        $data = [
            'code' => 'T1',
            'label' => 'New Serie',
            'description' => 'Description',
            'minimum_average' => 10.0,
            'order' => 10,
        ];

        $this->postJson('/api/v1/series', $data)
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.code', 'T1');
    }

    public function test_can_show_serie(): void
    {
        $serie = Serie::factory()->create();

        $this->getJson("/api/v1/series/{$serie->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $serie->id);
    }

    public function test_can_update_serie(): void
    {
        $serie = Serie::factory()->create();

        $this->putJson("/api/v1/series/{$serie->id}", ['label' => 'Updated Label'])
            ->assertOk()
            ->assertJsonPath('data.label', 'Updated Label');
    }

    public function test_can_delete_serie(): void
    {
        $serie = Serie::factory()->create();

        $this->deleteJson("/api/v1/series/{$serie->id}")
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseMissing('series', ['id' => $serie->id]);
    }

    public function test_returns_404_for_non_existent_serie(): void
    {
        $this->getJson('/api/v1/series/00000000-0000-0000-0000-000000000000')
            ->assertNotFound();
    }
}
