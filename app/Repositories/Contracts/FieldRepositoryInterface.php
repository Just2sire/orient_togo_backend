<?php

namespace App\Repositories\Contracts;

use App\Models\Field;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface FieldRepositoryInterface
{
    /**
     * Retourne une liste paginée (avec filtres optionnels).
     *
     * @param  array<string, mixed>  $filters
     */
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    /**
     * Retourne tous les fields sans pagination.
     *
     * @return Collection<int, Field>
     */
    public function all(array $filters = []): Collection;

    /**
     * Trouve un(e) Field par son identifiant (lève ModelNotFoundException si absent).
     */
    public function findOrFail(string|int $id): Field;

    /**
     * Crée un(e) Field.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Field;

    /**
     * Met à jour un(e) Field.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(Field $field, array $data): Field;

    /**
     * Supprime un(e) Field.
     */
    public function delete(Field $field): bool;
}
