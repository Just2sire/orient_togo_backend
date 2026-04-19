<?php

namespace App\Services;

use App\Http\Requests\StoreSubjectCoefficientRequest;
use App\Http\Requests\UpdateSubjectCoefficientRequest;
use App\Http\Resources\SubjectCoefficientResource;
use App\Models\SubjectCoefficient;
use App\Repositories\Contracts\SubjectCoefficientRepositoryInterface;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SubjectCoefficientService
{
    use ApiResponse;

    public function __construct(
        private readonly SubjectCoefficientRepositoryInterface $repository
    ) {}

    /**
     * Retourne la liste des subjectCoefficients (avec filtres et pagination).
     */
    public function index(Request $request): JsonResponse
    {
        return $this->try(function () use ($request) {
            $paginator = $this->repository->paginate($request->all(), $request->integer('per_page', 15));

            return $this->paginated($paginator, SubjectCoefficientResource::class);
        }, 'Impossible de récupérer les subjectCoefficients.');
    }

    /**
     * Affiche un(e) SubjectCoefficient.
     */
    public function show(SubjectCoefficient $subjectCoefficient): JsonResponse
    {
        return $this->try(function () use ($subjectCoefficient) {
            $model = $this->repository->findOrFail($subjectCoefficient->id);

            return $this->success(new SubjectCoefficientResource($model), 'SubjectCoefficient récupéré.');
        }, 'Impossible de récupérer ce subjectCoefficient.');
    }

    /**
     * Crée un(e) SubjectCoefficient.
     */
    public function store(StoreSubjectCoefficientRequest $request): JsonResponse
    {
        return $this->try(function () use ($request) {
            $model = $this->repository->create($request->validated());

            return $this->created(new SubjectCoefficientResource($model));
        }, 'Impossible de créer le subjectCoefficient.');
    }

    /**
     * Met à jour un(e) SubjectCoefficient.
     */
    public function update(SubjectCoefficient $subjectCoefficient, UpdateSubjectCoefficientRequest $request): JsonResponse
    {
        return $this->try(function () use ($request, $subjectCoefficient) {
            $model = $this->repository->update($subjectCoefficient, $request->validated());

            return $this->updated(new SubjectCoefficientResource($model));
        }, 'Impossible de mettre à jour le subjectCoefficient.');
    }

    /**
     * Supprime un(e) SubjectCoefficient.
     */
    public function destroy(SubjectCoefficient $subjectCoefficient): JsonResponse
    {
        return $this->try(function () use ($subjectCoefficient) {
            $this->repository->delete($subjectCoefficient);

            return $this->deleted();
        }, 'Impossible de supprimer le subjectCoefficient.');
    }
}
