<?php

namespace App\Repositories;

use App\Models\Tag;
use App\Repositories\Contracts\TagRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class TagRepository implements TagRepositoryInterface
{
    public function __construct(private readonly Tag $model) {}

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

    public function findOrFail(string|int $id): Tag
    {
        return $this->model->findOrFail($id);
    }

    public function create(array $data): Tag
    {
        return DB::transaction(fn () => $this->model->create($data));
    }

    public function update(Tag $tag, array $data): Tag
    {
        DB::transaction(fn () => $tag->update($data));

        return $tag->fresh();
    }

    public function delete(Tag $tag): bool
    {
        return DB::transaction(fn () => (bool) $tag->delete());
    }
}
