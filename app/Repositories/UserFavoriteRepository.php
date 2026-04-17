<?php

namespace App\Repositories;

use App\Models\UserFavorite;
use App\Repositories\Contracts\UserFavoriteRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class UserFavoriteRepository implements UserFavoriteRepositoryInterface
{
    public function __construct(private readonly UserFavorite $model) {}

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

    public function findOrFail(string|int $id): UserFavorite
    {
        return $this->model->findOrFail($id);
    }

    public function create(array $data): UserFavorite
    {
        return DB::transaction(fn () => $this->model->create($data));
    }

    public function update(UserFavorite $userFavorite, array $data): UserFavorite
    {
        DB::transaction(fn () => $userFavorite->update($data));

        return $userFavorite->fresh();
    }

    public function delete(UserFavorite $userFavorite): bool
    {
        return DB::transaction(fn () => (bool) $userFavorite->delete());
    }
}