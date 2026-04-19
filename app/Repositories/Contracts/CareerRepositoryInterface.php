<?php

namespace App\Repositories\Contracts;

use App\Models\Career;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface CareerRepositoryInterface
{
    /**
     * Retourne une liste paginée (avec filtres optionnels).
     *
     * @param  array<string, mixed>  $filters
     */
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    /**
     * Retourne tous les careers sans pagination.
     *
     * @return Collection<int, Career>
     */
    public function all(array $filters = []): Collection;

    /**
     * Trouve un(e) Career par son identifiant (lève ModelNotFoundException si absent).
     */
    public function findOrFail(string|int $id): Career;

    /**
     * Crée un(e) Career.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Career;

    /**
     * Met à jour un(e) Career.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(Career $career, array $data): Career;

    /**
     * Supprime un(e) Career.
     */
    public function delete(Career $career): bool;
}
