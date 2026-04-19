<?php

namespace Tests\Feature;

use App\Models\Career;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CareerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_can_list_careers(): void
    {
        Career::factory()->count(3)->create();

        $this->getJson('/api/v1/careers')
            ->assertOk()
            ->assertJsonStructure([
                'success', 'message',
                'data',
                'meta' => ['pagination'],
            ]);
    }

    public function test_can_create_career(): void
    {
        $data = [
            'name' => 'Nouveau Career',
            'description' => 'Description courte du métier',
            'required_skills' => ['Compétence 1', 'Compétence 2'],
            'market_demand' => 7,
            'salary_min' => 200000,
            'salary_max' => 500000,
            'long_description' => 'Description longue du métier avec plus de détails.',
            'is_promising' => true,
        ];

        $this->postJson('/api/v1/careers', $data)
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.name', 'Nouveau Career');
    }

    public function test_can_show_career(): void
    {
        $career = Career::factory()->create();

        $this->getJson("/api/v1/careers/{$career->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $career->id);
    }

    public function test_can_update_career(): void
    {
        $career = Career::factory()->create();

        $this->putJson("/api/v1/careers/{$career->id}", ['name' => 'Modifié'])
            ->assertOk()
            ->assertJsonPath('data.name', 'Modifié');
    }

    public function test_can_delete_career(): void
    {
        $career = Career::factory()->create();

        $this->deleteJson("/api/v1/careers/{$career->id}")
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertSoftDeleted('careers', ['id' => $career->id]);
    }

    public function test_returns_404_for_non_existent_career(): void
    {
        $this->getJson('/api/v1/careers/00000000-0000-0000-0000-000000000000')
            ->assertNotFound();
    }

    public function test_fails_creation_without_required_data(): void
    {
        $this->postJson('/api/v1/careers', [])
            ->assertUnprocessable()
            ->assertJsonPath('success', false);
    }
}
