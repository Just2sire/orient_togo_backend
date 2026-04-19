<?php

namespace Tests\Feature;

use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TagTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_can_list_tags(): void
    {
        Tag::factory()->count(3)->create();

        $this->getJson('/api/v1/tags')
            ->assertOk()
            ->assertJsonStructure([
                'success', 'message',
                'data',
                'meta' => ['pagination'],
            ]);
    }

    public function test_can_create_tag(): void
    {
        $data = [
            'label' => 'Nouveau Tag',
            'category' => 'secteur',
            'description' => 'Description du tag',
        ];

        $this->postJson('/api/v1/tags', $data)
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.label', 'Nouveau Tag');
    }

    public function test_can_show_tag(): void
    {
        $tag = Tag::factory()->create();

        $this->getJson("/api/v1/tags/{$tag->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $tag->id);
    }

    public function test_can_update_tag(): void
    {
        $tag = Tag::factory()->create();

        $this->putJson("/api/v1/tags/{$tag->id}", ['label' => 'Modifié'])
            ->assertOk()
            ->assertJsonPath('data.label', 'Modifié');
    }

    public function test_can_delete_tag(): void
    {
        $tag = Tag::factory()->create();

        $this->deleteJson("/api/v1/tags/{$tag->id}")
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseMissing('tags', ['id' => $tag->id]);
    }

    public function test_returns_404_for_non_existent_tag(): void
    {
        $this->getJson('/api/v1/tags/00000000-0000-0000-0000-000000000000')
            ->assertNotFound();
    }

    public function test_fails_creation_without_required_data(): void
    {
        $this->postJson('/api/v1/tags', [])
            ->assertUnprocessable()
            ->assertJsonPath('success', false);
    }
}
