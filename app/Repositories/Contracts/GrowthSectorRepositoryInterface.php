<?php

namespace App\Repositories\Contracts;

use App\Models\GrowthSector;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface GrowthSectorRepositoryInterface
{
    /**
     * Retourne une liste paginée (avec filtres optionnels).
     *
     * @param  array<string, mixed>  $filters
     */
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    /**
     * Retourne tous les growthSectors sans pagination.
     *
     * @return Collection<int, GrowthSector>
     */
    public function all(array $filters = []): Collection;

    /**
     * Trouve un(e) GrowthSector par son identifiant (lève ModelNotFoundException si absent).
     */
    public function findOrFail(string|int $id): GrowthSector;

    /**
     * Crée un(e) GrowthSector.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): GrowthSector;

    /**
     * Met à jour un(e) GrowthSector.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(GrowthSector $growthSector, array $data): GrowthSector;

    /**
     * Supprime un(e) GrowthSector.
     */
    public function delete(GrowthSector $growthSector): bool;
}
