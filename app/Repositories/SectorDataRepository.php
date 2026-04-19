<?php

namespace App\Repositories;

use App\Models\SectorData;
use App\Repositories\Contracts\SectorDataRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class SectorDataRepository implements SectorDataRepositoryInterface
{
    public function __construct(private readonly SectorData $model) {}

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

    public function findOrFail(string|int $id): SectorData
    {
        return $this->model->findOrFail($id);
    }

    public function create(array $data): SectorData
    {
        return DB::transaction(fn () => $this->model->create($data));
    }

    public function update(SectorData $sectorData, array $data): SectorData
    {
        DB::transaction(fn () => $sectorData->update($data));

        return $sectorData->fresh();
    }

    public function delete(SectorData $sectorData): bool
    {
        return DB::transaction(fn () => (bool) $sectorData->delete());
    }
}
