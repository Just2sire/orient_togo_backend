<?php

namespace App\Repositories\Contracts;

use App\Models\SectorData;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface SectorDataRepositoryInterface
{
    /**
     * Retourne une liste paginée (avec filtres optionnels).
     *
     * @param  array<string, mixed>  $filters
     */
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    /**
     * Retourne tous les sectorDatas sans pagination.
     *
     * @return Collection<int, SectorData>
     */
    public function all(array $filters = []): Collection;

    /**
     * Trouve un(e) SectorData par son identifiant (lève ModelNotFoundException si absent).
     */
    public function findOrFail(string|int $id): SectorData;

    /**
     * Crée un(e) SectorData.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): SectorData;

    /**
     * Met à jour un(e) SectorData.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(SectorData $sectorData, array $data): SectorData;

    /**
     * Supprime un(e) SectorData.
     */
    public function delete(SectorData $sectorData): bool;
}
