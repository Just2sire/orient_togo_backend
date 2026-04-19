<?php

namespace App\Repositories;

use App\Models\Serie;
use App\Repositories\Contracts\SerieRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class SerieRepository implements SerieRepositoryInterface
{
    public function __construct(private readonly Serie $model) {}

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->query()
            ->when($filters['search'] ?? null, fn ($q, $v) => $q->where('label', 'ilike', "%{$v}%")->orWhere('code', 'ilike', "%{$v}%"))
            ->latest()
            ->paginate($perPage);
    }

    public function all(array $filters = []): Collection
    {
        return $this->model->query()
            ->when($filters['search'] ?? null, fn ($q, $v) => $q->where('label', 'ilike', "%{$v}%")->orWhere('code', 'ilike', "%{$v}%"))
            ->latest()
            ->get();
    }

    public function findOrFail(string|int $id): Serie
    {
        return $this->model->findOrFail($id);
    }

    public function create(array $data): Serie
    {
        return DB::transaction(fn () => $this->model->create($data));
    }

    public function update(Serie $serie, array $data): Serie
    {
        DB::transaction(fn () => $serie->update($data));

        return $serie->fresh();
    }

    public function delete(Serie $serie): bool
    {
        return DB::transaction(fn () => (bool) $serie->delete());
    }
}
