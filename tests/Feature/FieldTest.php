<?php

namespace Tests\Feature;

use App\Models\Field;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FieldTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_can_list_fields(): void
    {
        Field::factory()->count(3)->create();

        $this->getJson('/api/v1/fields')
            ->assertOk()
            ->assertJsonStructure([
                'success', 'message',
                'data',
                'meta' => ['pagination'],
            ]);
    }

    public function test_can_create_field(): void
    {
        $data = [
            'name' => 'Nouvelle Filière',
            'description' => 'Description de la filière',
            'estimated_duration_years' => 3.0,
            'main_domain' => 'sante',
            'is_selected' => true,
        ];

        $this->postJson('/api/v1/fields', $data)
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.name', 'Nouvelle Filière');
    }

    public function test_can_show_field(): void
    {
        $field = Field::factory()->create();

        $this->getJson("/api/v1/fields/{$field->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $field->id);
    }

    public function test_can_update_field(): void
    {
        $field = Field::factory()->create();

        $this->putJson("/api/v1/fields/{$field->id}", ['name' => 'Modifié'])
            ->assertOk()
            ->assertJsonPath('data.name', 'Modifié');
    }

    public function test_can_delete_field(): void
    {
        $field = Field::factory()->create();

        $this->deleteJson("/api/v1/fields/{$field->id}")
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseMissing('fields', ['id' => $field->id]);
    }

    public function test_returns_404_for_non_existent_field(): void
    {
        $this->getJson('/api/v1/fields/00000000-0000-0000-0000-000000000000')
            ->assertNotFound();
    }

    public function test_fails_creation_without_required_data(): void
    {
        $this->postJson('/api/v1/fields', [])
            ->assertUnprocessable()
            ->assertJsonPath('success', false);
    }
}
