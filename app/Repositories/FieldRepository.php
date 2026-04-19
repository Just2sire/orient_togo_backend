<?php

namespace App\Repositories;

use App\Models\Field;
use App\Repositories\Contracts\FieldRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class FieldRepository implements FieldRepositoryInterface
{
    public function __construct(private readonly Field $model) {}

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->query()
            ->when($filters['search'] ?? null, fn ($q, $v) => $q->where('name', 'ilike', "%{$v}%"))
            ->when($filters['main_domain'] ?? null, fn ($q, $v) => $q->where('main_domain', $v))
            ->when(isset($filters['is_selected']), fn ($q) => $q->where('is_selected', $filters['is_selected']))
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

    public function findOrFail(string|int $id): Field
    {
        return $this->model->findOrFail($id);
    }

    public function create(array $data): Field
    {
        return DB::transaction(fn () => $this->model->create($data));
    }

    public function update(Field $field, array $data): Field
    {
        DB::transaction(fn () => $field->update($data));

        return $field->fresh();
    }

    public function delete(Field $field): bool
    {
        return DB::transaction(fn () => (bool) $field->delete());
    }
}
