<?php

namespace App\Repositories;

use App\Models\UserDevice;
use App\Repositories\Contracts\UserDeviceRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class UserDeviceRepository implements UserDeviceRepositoryInterface
{
    public function __construct(private readonly UserDevice $model) {}

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

    public function findOrFail(string|int $id): UserDevice
    {
        return $this->model->findOrFail($id);
    }

    public function create(array $data): UserDevice
    {
        return DB::transaction(fn () => $this->model->create($data));
    }

    public function update(UserDevice $userDevice, array $data): UserDevice
    {
        DB::transaction(fn () => $userDevice->update($data));

        return $userDevice->fresh();
    }

    public function delete(UserDevice $userDevice): bool
    {
        return DB::transaction(fn () => (bool) $userDevice->delete());
    }
}