<?php

namespace Tests\Feature;

use App\Enums\EstablishmentTypeEnum;
use App\Enums\RegionEnum;
use App\Models\Establishment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EstablishmentTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_can_list_establishments(): void
    {
        Establishment::factory()->count(3)->create();

        $this->getJson('/api/v1/establishments')
            ->assertOk()
            ->assertJsonStructure([
                'success', 'message',
                'data',
                'meta' => ['pagination'],
            ]);
    }

    public function test_can_create_establishment(): void
    {
        $data = [
            'name' => 'Nouvel Établissement',
            'type' => EstablishmentTypeEnum::Universite->value,
            'region' => RegionEnum::Maritime->value,
            'city' => 'Lomé',
            'address' => 'Boulevard du Mono',
            'email' => 'contact@univ.tg',
            'phone' => '+22822212345',
            'website' => 'https://univ.tg',
            'description' => 'Description de l\'université',
            'fees_info' => '5000 - 15000 FCFA',
            'specialties' => ['Droit', 'Médecine', 'Informatique'],
            'is_public' => true,
            'is_verified' => false,
            'verification_status' => 'pending',
            'is_selected' => true,
            'latitude' => 6.1234,
            'longitude' => 1.2345,
        ];

        $this->postJson('/api/v1/establishments', $data)
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.name', 'Nouvel Établissement');
    }

    public function test_can_show_establishment_by_slug(): void
    {
        $establishment = Establishment::factory()->create(['name' => 'My Establishment']);

        $this->getJson("/api/v1/establishments/{$establishment->slug}")
            ->assertOk()
            ->assertJsonPath('data.id', $establishment->id);
    }

    public function test_can_show_establishment_by_uuid(): void
    {
        $establishment = Establishment::factory()->create();

        $this->getJson("/api/v1/establishments/{$establishment->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $establishment->id);
    }

    public function test_can_update_establishment(): void
    {
        $establishment = Establishment::factory()->create();

        $this->putJson("/api/v1/establishments/{$establishment->id}", ['name' => 'Updated Name'])
            ->assertOk()
            ->assertJsonPath('data.name', 'Updated Name');
    }

    public function test_can_delete_establishment(): void
    {
        $establishment = Establishment::factory()->create();

        $this->deleteJson("/api/v1/establishments/{$establishment->id}")
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertSoftDeleted('establishments', ['id' => $establishment->id]);
    }

    public function test_can_verify_establishment(): void
    {
        $establishment = Establishment::factory()->create(['is_verified' => false]);

        $this->postJson("/api/v1/establishments/{$establishment->id}/verify")
            ->assertOk()
            ->assertJsonPath('data.is_verified', true);
    }

    public function test_fails_creation_without_required_data(): void
    {
        $this->postJson('/api/v1/establishments', [])
            ->assertUnprocessable()
            ->assertJsonPath('success', false);
    }
}
