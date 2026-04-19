<?php

namespace App\Services;

use App\Http\Requests\StoreCareerRequest;
use App\Http\Requests\UpdateCareerRequest;
use App\Http\Resources\CareerResource;
use App\Models\Career;
use App\Repositories\Contracts\CareerRepositoryInterface;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CareerService
{
    use ApiResponse;

    public function __construct(
        private readonly CareerRepositoryInterface $repository
    ) {}

    /**
     * Retourne la liste des careers (avec filtres et pagination).
     */
    public function index(Request $request): JsonResponse
    {
        return $this->try(function () use ($request) {
            $paginator = $this->repository->paginate($request->all(), $request->integer('per_page', 15));

            return $this->paginated($paginator, CareerResource::class);
        }, 'Impossible de récupérer les careers.');
    }

    /**
     * Affiche un(e) Career.
     */
    public function show(Career $career): JsonResponse
    {
        return $this->try(function () use ($career) {
            $model = $this->repository->findOrFail($career->id);
            $model->load('growthSector', 'sectorData', 'fields', 'courses', 'tags');

            return $this->success(new CareerResource($model), 'Career récupéré.');
        }, 'Impossible de récupérer ce career.');
    }

    /**
     * Crée un(e) Career.
     */
    public function store(StoreCareerRequest $request): JsonResponse
    {
        return $this->try(function () use ($request) {
            $model = $this->repository->create($request->validated());

            return $this->created(new CareerResource($model));
        }, 'Impossible de créer le career.');
    }

    /**
     * Met à jour un(e) Career.
     */
    public function update(Career $career, UpdateCareerRequest $request): JsonResponse
    {
        return $this->try(function () use ($request, $career) {
            $model = $this->repository->update($career, $request->validated());

            return $this->updated(new CareerResource($model));
        }, 'Impossible de mettre à jour le career.');
    }

    /**
     * Supprime un(e) Career.
     */
    public function destroy(Career $career): JsonResponse
    {
        return $this->try(function () use ($career) {
            $this->repository->delete($career);

            return $this->deleted();
        }, 'Impossible de supprimer le career.');
    }
}
