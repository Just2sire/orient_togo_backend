<?php

namespace App\Repositories;

use App\Models\UserProfile;
use App\Repositories\Contracts\UserProfileRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class UserProfileRepository implements UserProfileRepositoryInterface
{
    public function __construct(private readonly UserProfile $model) {}

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

    public function findOrFail(string|int $id): UserProfile
    {
        return $this->model->findOrFail($id);
    }

    public function create(array $data): UserProfile
    {
        return DB::transaction(fn () => $this->model->create($data));
    }

    public function update(UserProfile $userProfile, array $data): UserProfile
    {
        DB::transaction(fn () => $userProfile->update($data));

        return $userProfile->fresh();
    }

    public function delete(UserProfile $userProfile): bool
    {
        return DB::transaction(fn () => (bool) $userProfile->delete());
    }
}