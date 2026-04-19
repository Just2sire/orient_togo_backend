<?php

namespace App\Services;

use App\Http\Requests\StoreSectorDataRequest;
use App\Http\Requests\UpdateSectorDataRequest;
use App\Http\Resources\SectorDataResource;
use App\Models\SectorData;
use App\Repositories\Contracts\SectorDataRepositoryInterface;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SectorDataService
{
    use ApiResponse;

    public function __construct(
        private readonly SectorDataRepositoryInterface $repository
    ) {}

    /**
     * Retourne la liste des sectorDatas (avec filtres et pagination).
     */
    public function index(Request $request): JsonResponse
    {
        return $this->try(function () use ($request) {
            $paginator = $this->repository->paginate($request->all(), $request->integer('per_page', 15));

            return $this->paginated($paginator, SectorDataResource::class);
        }, 'Impossible de récupérer les sectorDatas.');
    }

    /**
     * Affiche un(e) SectorData.
     */
    public function show(SectorData $sectorData): JsonResponse
    {
        return $this->try(function () use ($sectorData) {
            $model = $this->repository->findOrFail($sectorData->id);

            return $this->success(new SectorDataResource($model), 'SectorData récupéré.');
        }, 'Impossible de récupérer ce sectorData.');
    }

    /**
     * Crée un(e) SectorData.
     */
    public function store(StoreSectorDataRequest $request): JsonResponse
    {
        return $this->try(function () use ($request) {
            $model = $this->repository->create($request->validated());

            return $this->created(new SectorDataResource($model));
        }, 'Impossible de créer le sectorData.');
    }

    /**
     * Met à jour un(e) SectorData.
     */
    public function update(SectorData $sectorData, UpdateSectorDataRequest $request): JsonResponse
    {
        return $this->try(function () use ($request, $sectorData) {
            $model = $this->repository->update($sectorData, $request->validated());

            return $this->updated(new SectorDataResource($model));
        }, 'Impossible de mettre à jour le sectorData.');
    }

    /**
     * Supprime un(e) SectorData.
     */
    public function destroy(SectorData $sectorData): JsonResponse
    {
        return $this->try(function () use ($sectorData) {
            $this->repository->delete($sectorData);

            return $this->deleted();
        }, 'Impossible de supprimer le sectorData.');
    }
}
