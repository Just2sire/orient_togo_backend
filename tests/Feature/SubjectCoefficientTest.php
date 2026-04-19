<?php

namespace Tests\Feature;

use App\Models\Serie;
use App\Models\SubjectCoefficient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubjectCoefficientTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_can_list_subject_coefficients(): void
    {
        SubjectCoefficient::factory()->count(3)->create();

        $this->getJson('/api/v1/subject-coefficients')
            ->assertOk()
            ->assertJsonStructure([
                'success', 'message',
                'data',
                'meta' => ['pagination'],
            ]);
    }

    public function test_can_create_subject_coefficient(): void
    {
        $serie = Serie::factory()->create();
        $data = [
            'serie_id' => $serie->id,
            'subject_name' => 'Mathématiques',
            'coefficient' => 7,
            'minimum_grade' => 10.0,
        ];

        $this->postJson('/api/v1/subject-coefficients', $data)
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.subject_name', 'Mathématiques');
    }

    public function test_can_show_subject_coefficient(): void
    {
        $subjectCoefficient = SubjectCoefficient::factory()->create();

        $this->getJson("/api/v1/subject-coefficients/{$subjectCoefficient->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $subjectCoefficient->id);
    }

    public function test_can_update_subject_coefficient(): void
    {
        $subjectCoefficient = SubjectCoefficient::factory()->create();

        $this->putJson("/api/v1/subject-coefficients/{$subjectCoefficient->id}", ['subject_name' => 'Physique'])
            ->assertOk()
            ->assertJsonPath('data.subject_name', 'Physique');
    }

    public function test_can_delete_subject_coefficient(): void
    {
        $subjectCoefficient = SubjectCoefficient::factory()->create();

        $this->deleteJson("/api/v1/subject-coefficients/{$subjectCoefficient->id}")
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseMissing('subject_coefficients', ['id' => $subjectCoefficient->id]);
    }

    public function test_returns_404_for_non_existent_subject_coefficient(): void
    {
        $this->getJson('/api/v1/subject-coefficients/00000000-0000-0000-0000-000000000000')
            ->assertNotFound();
    }

    public function test_fails_creation_without_required_data(): void
    {
        $this->postJson('/api/v1/subject-coefficients', [])
            ->assertUnprocessable()
            ->assertJsonPath('success', false);
    }
}
