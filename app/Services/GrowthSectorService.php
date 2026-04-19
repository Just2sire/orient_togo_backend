<?php

namespace App\Services;

use App\Http\Requests\StoreGrowthSectorRequest;
use App\Http\Requests\UpdateGrowthSectorRequest;
use App\Http\Resources\GrowthSectorResource;
use App\Models\GrowthSector;
use App\Repositories\Contracts\GrowthSectorRepositoryInterface;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GrowthSectorService
{
    use ApiResponse;

    public function __construct(
        private readonly GrowthSectorRepositoryInterface $repository
    ) {}

    /**
     * Retourne la liste des growth-sectors (avec filtres et pagination).
     */
    public function index(Request $request): JsonResponse
    {
        return $this->try(function () use ($request) {
            $paginator = $this->repository->paginate($request->all(), $request->integer('per_page', 15));

            return $this->paginated($paginator, GrowthSectorResource::class);
        }, 'Impossible de récupérer les growth-sectors.');
    }

    /**
     * Affiche un(e) GrowthSector.
     */
    public function show(GrowthSector $growthSector): JsonResponse
    {
        return $this->try(function () use ($growthSector) {
            $model = $this->repository->findOrFail($growthSector->id);
            $model->load('careers', 'sectorData', 'fields');

            return $this->success(new GrowthSectorResource($model), 'GrowthSector récupéré.');
        }, 'Impossible de récupérer ce growthSector.');
    }

    /**
     * Crée un(e) GrowthSector.
     */
    public function store(StoreGrowthSectorRequest $request): JsonResponse
    {
        return $this->try(function () use ($request) {
            $model = $this->repository->create($request->validated());

            return $this->created(new GrowthSectorResource($model));
        }, 'Impossible de créer le growthSector.');
    }

    /**
     * Met à jour un(e) GrowthSector.
     */
    public function update(GrowthSector $growthSector, UpdateGrowthSectorRequest $request): JsonResponse
    {
        return $this->try(function () use ($request, $growthSector) {
            $model = $this->repository->update($growthSector, $request->validated());

            return $this->updated(new GrowthSectorResource($model));
        }, 'Impossible de mettre à jour le growthSector.');
    }

    /**
     * Supprime un(e) GrowthSector.
     */
    public function destroy(GrowthSector $growthSector): JsonResponse
    {
        return $this->try(function () use ($growthSector) {
            $this->repository->delete($growthSector);

            return $this->deleted();
        }, 'Impossible de supprimer le growthSector.');
    }
}
