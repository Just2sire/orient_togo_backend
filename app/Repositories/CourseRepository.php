<?php

namespace App\Repositories;

use App\Models\Course;
use App\Repositories\Contracts\CourseRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class CourseRepository implements CourseRepositoryInterface
{
    public function __construct(private readonly Course $model) {}

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->query()
            ->when($filters['search'] ?? null, fn ($q, $v) => $q->where('name', 'ilike', "%{$v}%"))
            ->when($filters['establishment_id'] ?? null, fn ($q, $v) => $q->where('establishment_id', $v))
            ->when($filters['level'] ?? null, fn ($q, $v) => $q->where('level', $v))
            ->latest()
            ->paginate($perPage);
    }

    public function all(array $filters = []): Collection
    {
        return $this->model->query()
            ->when($filters['search'] ?? null, fn ($q, $v) => $q->where('name', 'ilike', "%{$v}%"))
            ->latest()
            ->get();
    }

    public function findOrFail(string|int $id): Course
    {
        return $this->model->findOrFail($id);
    }

    public function create(array $data): Course
    {
        return DB::transaction(fn () => $this->model->create($data));
    }

    public function update(Course $course, array $data): Course
    {
        DB::transaction(fn () => $course->update($data));

        return $course->fresh();
    }

    public function delete(Course $course): bool
    {
        return DB::transaction(fn () => (bool) $course->delete());
    }
}
