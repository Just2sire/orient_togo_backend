<?php

namespace App\Services;

use App\Http\Requests\StoreFieldRequest;
use App\Http\Requests\UpdateFieldRequest;
use App\Http\Resources\FieldResource;
use App\Models\Field;
use App\Repositories\Contracts\FieldRepositoryInterface;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FieldService
{
    use ApiResponse;

    public function __construct(
        private readonly FieldRepositoryInterface $repository
    ) {}

    /**
     * Retourne la liste des fields (avec filtres et pagination).
     */
    public function index(Request $request): JsonResponse
    {
        return $this->try(function () use ($request) {
            $paginator = $this->repository->paginate($request->all(), $request->integer('per_page', 15));

            return $this->paginated($paginator, FieldResource::class);
        }, 'Impossible de récupérer les fields.');
    }

    /**
     * Affiche un(e) Field.
     */
    public function show(Field $field): JsonResponse
    {
        return $this->try(function () use ($field) {
            $model = $this->repository->findOrFail($field->id);
            $model->load('series', 'establishments', 'careers', 'growthSectors');

            return $this->success(new FieldResource($model), 'Field récupéré.');
        }, 'Impossible de récupérer ce field.');
    }

    /**
     * Crée un(e) Field.
     */
    public function store(StoreFieldRequest $request): JsonResponse
    {
        return $this->try(function () use ($request) {
            $model = $this->repository->create($request->validated());

            return $this->created(new FieldResource($model));
        }, 'Impossible de créer le field.');
    }

    /**
     * Met à jour un(e) Field.
     */
    public function update(Field $field, UpdateFieldRequest $request): JsonResponse
    {
        return $this->try(function () use ($request, $field) {
            $model = $this->repository->update($field, $request->validated());

            return $this->updated(new FieldResource($model));
        }, 'Impossible de mettre à jour le field.');
    }

    /**
     * Supprime un(e) Field.
     */
    public function destroy(Field $field): JsonResponse
    {
        return $this->try(function () use ($field) {
            $this->repository->delete($field);

            return $this->deleted();
        }, 'Impossible de supprimer le field.');
    }
}
