<?php

namespace App\Repositories;

use App\Models\Career;
use App\Repositories\Contracts\CareerRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class CareerRepository implements CareerRepositoryInterface
{
    public function __construct(private readonly Career $model) {}

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->query()
            ->when($filters['search'] ?? null, fn ($q, $v) => $q->where('name', 'ilike', "%{$v}%"))
            ->when($filters['growth_sector_id'] ?? null, fn ($q, $v) => $q->where('growth_sector_id', $v))
            ->when($filters['market_demand'] ?? null, fn ($q, $v) => $q->where('market_demand', '>=', $v))
            ->when(isset($filters['is_promising']), fn ($q) => $q->where('is_promising', $filters['is_promising']))
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

    public function findOrFail(string|int $id): Career
    {
        return $this->model->findOrFail($id);
    }

    public function create(array $data): Career
    {
        return DB::transaction(fn () => $this->model->create($data));
    }

    public function update(Career $career, array $data): Career
    {
        DB::transaction(fn () => $career->update($data));

        return $career->fresh();
    }

    public function delete(Career $career): bool
    {
        return DB::transaction(fn () => (bool) $career->delete());
    }
}
