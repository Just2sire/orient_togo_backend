<?php

namespace App\Services;

use App\Http\Requests\StoreSerieRequest;
use App\Http\Requests\UpdateSerieRequest;
use App\Http\Resources\SerieResource;
use App\Models\Serie;
use App\Repositories\Contracts\SerieRepositoryInterface;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class SerieService
{
    use ApiResponse;

    public function __construct(
        private readonly SerieRepositoryInterface $repository
    ) {}

    /**
     * Retourne la liste des series (avec filtres et pagination).
     */
    public function index(Request $request): JsonResponse
    {
        return $this->try(function () use ($request) {
            $paginator = $this->repository->paginate($request->all(), $request->integer('per_page', 15));

            return $this->paginated($paginator, SerieResource::class);
        }, 'Impossible de récupérer les series.');
    }

    /**
     * Affiche un(e) Serie.
     */
    public function show(Serie $serie): JsonResponse
    {
        return $this->try(function () use ($serie) {
            $model = $this->repository->findOrFail($serie->id);
            $model->load('subjectCoefficients');

            return $this->success(new SerieResource($model), 'Serie récupérée.');
        }, 'Impossible de récupérer cette serie.');
    }

    /**
     * Crée un(e) Serie.
     */
    public function store(StoreSerieRequest $request): JsonResponse
    {
        return $this->try(function () use ($request) {
            $model = $this->repository->create($request->validated());

            return $this->created(new SerieResource($model));
        }, 'Impossible de créer la serie.');
    }

    /**
     * Met à jour un(e) Serie.
     */
    public function update(Serie $serie, UpdateSerieRequest $request): JsonResponse
    {
        return $this->try(function () use ($request, $serie) {
            $model = $this->repository->update($serie, $request->validated());

            return $this->updated(new SerieResource($model));
        }, 'Impossible de mettre à jour la serie.');
    }

    /**
     * Supprime un(e) Serie.
     */
    public function destroy(Serie $serie): JsonResponse
    {
        return $this->try(function () use ($serie) {
            $this->repository->delete($serie);

            return $this->deleted();
        }, 'Impossible de supprimer la serie.');
    }

    /**
     * Trouve les séries accessibles selon une moyenne donnée.
     */
    public function findAccessibleSeries(float $average): Collection
    {
        return Serie::active()
            ->where('minimum_average', '<=', $average)
            ->orderBy('order')
            ->get();
    }
}
