<?php

namespace App\Repositories;

use App\Models\SubjectCoefficient;
use App\Repositories\Contracts\SubjectCoefficientRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class SubjectCoefficientRepository implements SubjectCoefficientRepositoryInterface
{
    public function __construct(private readonly SubjectCoefficient $model) {}

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->query()
            ->when($filters['search'] ?? null, fn ($q, $v) => $q->where('name', 'like', "%{$v}%"))
            // Ajoutez d'autres filtres ici
            ->latest()
            ->paginate($perPage);
    }

    public function all(array $filters = []): Collection
    {
        return $this->model->query()
            ->when($filters['search'] ?? null, fn ($q, $v) => $q->where('name', 'like', "%{$v}%"))
            ->latest()
            ->get();
    }

    public function findOrFail(string|int $id): SubjectCoefficient
    {
        return $this->model->findOrFail($id);
    }

    public function create(array $data): SubjectCoefficient
    {
        return DB::transaction(fn () => $this->model->create($data));
    }

    public function update(SubjectCoefficient $subjectCoefficient, array $data): SubjectCoefficient
    {
        DB::transaction(fn () => $subjectCoefficient->update($data));

        return $subjectCoefficient->fresh();
    }

    public function delete(SubjectCoefficient $subjectCoefficient): bool
    {
        return DB::transaction(fn () => (bool) $subjectCoefficient->delete());
    }
}
