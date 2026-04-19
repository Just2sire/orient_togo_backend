<?php

namespace App\Repositories;

use App\Models\GrowthSector;
use App\Repositories\Contracts\GrowthSectorRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class GrowthSectorRepository implements GrowthSectorRepositoryInterface
{
    public function __construct(private readonly GrowthSector $model) {}

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

    public function findOrFail(string|int $id): GrowthSector
    {
        return $this->model->findOrFail($id);
    }

    public function create(array $data): GrowthSector
    {
        return DB::transaction(fn () => $this->model->create($data));
    }

    public function update(GrowthSector $growthSector, array $data): GrowthSector
    {
        DB::transaction(fn () => $growthSector->update($data));

        return $growthSector->fresh();
    }

    public function delete(GrowthSector $growthSector): bool
    {
        return DB::transaction(fn () => (bool) $growthSector->delete());
    }
}
