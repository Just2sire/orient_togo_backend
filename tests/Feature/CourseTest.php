<?php

namespace Tests\Feature;

use App\Enums\LevelEnum;
use App\Models\Course;
use App\Models\Establishment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CourseTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_can_list_courses(): void
    {
        Course::factory()->count(3)->create();

        $this->getJson('/api/v1/courses')
            ->assertOk()
            ->assertJsonStructure([
                'success', 'message',
                'data',
                'meta' => ['pagination'],
            ]);
    }

    public function test_can_create_course(): void
    {
        $establishment = Establishment::factory()->create();
        $data = [
            'establishment_id' => $establishment->id,
            'name' => 'Nouveau Course',
            'level' => LevelEnum::Licence->value,
            'description' => 'Description du cours',
            'duration_months' => 36,
            'annual_fees' => 50000.0,
            'accreditation' => 'Accréditation 2024',
        ];

        $this->postJson('/api/v1/courses', $data)
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.name', 'Nouveau Course');
    }

    public function test_can_show_course(): void
    {
        $course = Course::factory()->create();

        $this->getJson("/api/v1/courses/{$course->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $course->id);
    }

    public function test_can_update_course(): void
    {
        $course = Course::factory()->create();

        $this->putJson("/api/v1/courses/{$course->id}", ['name' => 'Modifié'])
            ->assertOk()
            ->assertJsonPath('data.name', 'Modifié');
    }

    public function test_can_delete_course(): void
    {
        $course = Course::factory()->create();

        $this->deleteJson("/api/v1/courses/{$course->id}")
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseMissing('courses', ['id' => $course->id]);
    }

    public function test_returns_404_for_non_existent_course(): void
    {
        $this->getJson('/api/v1/courses/00000000-0000-0000-0000-000000000000')
            ->assertNotFound();
    }

    public function test_fails_creation_without_required_data(): void
    {
        $this->postJson('/api/v1/courses', [])
            ->assertUnprocessable()
            ->assertJsonPath('success', false);
    }
}
