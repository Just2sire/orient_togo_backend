<?php

namespace App\Repositories;

use App\Models\Establishment;
use App\Models\User;
use App\Repositories\Contracts\EstablishmentRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class EstablishmentRepository implements EstablishmentRepositoryInterface
{
    public function __construct(private readonly Establishment $model) {}

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->query()
            ->when($filters['search'] ?? null, fn ($q, $v) => $q->where('name', 'ilike', "%{$v}%")->orWhere('city', 'ilike', "%{$v}%"))
            ->when($filters['type'] ?? null, fn ($q, $v) => $q->where('type', $v))
            ->when($filters['region'] ?? null, fn ($q, $v) => $q->where('region', $v))
            ->when(isset($filters['is_verified']), fn ($q) => $q->where('is_verified', $filters['is_verified']))
            ->when(isset($filters['is_selected']), fn ($q) => $q->where('is_selected', $filters['is_selected']))
            ->latest()
            ->paginate($perPage);
    }

    public function all(array $filters = []): Collection
    {
        return $this->model->query()
            ->when($filters['search'] ?? null, fn ($q, $v) => $q->where('name', 'ilike', "%{$v}%"))
            ->when($filters['type'] ?? null, fn ($q, $v) => $q->where('type', $v))
            ->when($filters['region'] ?? null, fn ($q, $v) => $q->where('region', $v))
            ->latest()
            ->get();
    }

    public function findOrFail(string|int $id): Establishment
    {
        return $this->model->findOrFail($id);
    }

    public function findBySlug(string $slug): ?Establishment
    {
        return $this->model->where('slug', $slug)->firstOrFail();
    }

    public function create(array $data): Establishment
    {
        return DB::transaction(fn () => $this->model->create($data));
    }

    public function update(Establishment $establishment, array $data): Establishment
    {
        DB::transaction(fn () => $establishment->update($data));

        return $establishment->fresh();
    }

    public function delete(Establishment $establishment): bool
    {
        return DB::transaction(fn () => (bool) $establishment->delete());
    }

    public function verify(Establishment $establishment, User $verifier): Establishment
    {
        return DB::transaction(function () use ($establishment, $verifier) {
            $establishment->update([
                'is_verified' => true,
                'verification_status' => 'approved',
                'verified_by_user_id' => $verifier->id,
                'verified_at' => now(),
            ]);

            return $establishment->fresh();
        });
    }
}
