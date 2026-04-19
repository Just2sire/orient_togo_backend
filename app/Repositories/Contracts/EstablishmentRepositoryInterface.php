<?php

namespace App\Repositories\Contracts;

use App\Models\Establishment;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface EstablishmentRepositoryInterface
{
    /**
     * Retourne une liste paginée (avec filtres optionnels).
     *
     * @param  array<string, mixed>  $filters
     */
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    /**
     * Retourne tous les establishments sans pagination.
     *
     * @return Collection<int, Establishment>
     */
    public function all(array $filters = []): Collection;

    /**
     * Trouve un(e) Establishment par son identifiant (lève ModelNotFoundException si absent).
     */
    public function findOrFail(string|int $id): Establishment;

    /**
     * Crée un(e) Establishment.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Establishment;

    /**
     * Met à jour un(e) Establishment.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(Establishment $establishment, array $data): Establishment;

    /**
     * Supprime un(e) Establishment.
     */
    public function delete(Establishment $establishment): bool;
}
