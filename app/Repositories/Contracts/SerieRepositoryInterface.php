<?php

namespace App\Repositories\Contracts;

use App\Models\Serie;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface SerieRepositoryInterface
{
    /**
     * Retourne une liste paginée (avec filtres optionnels).
     *
     * @param  array<string, mixed>  $filters
     */
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    /**
     * Retourne tous les series sans pagination.
     *
     * @return Collection<int, Serie>
     */
    public function all(array $filters = []): Collection;

    /**
     * Trouve un(e) Serie par son identifiant (lève ModelNotFoundException si absent).
     */
    public function findOrFail(string|int $id): Serie;

    /**
     * Crée un(e) Serie.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Serie;

    /**
     * Met à jour un(e) Serie.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(Serie $serie, array $data): Serie;

    /**
     * Supprime un(e) Serie.
     */
    public function delete(Serie $serie): bool;
}
