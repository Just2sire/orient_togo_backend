<?php

namespace App\Services;

use App\Http\Requests\StoreTagRequest;
use App\Http\Requests\UpdateTagRequest;
use App\Http\Resources\TagResource;
use App\Models\Tag;
use App\Repositories\Contracts\TagRepositoryInterface;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TagService
{
    use ApiResponse;

    public function __construct(
        private readonly TagRepositoryInterface $repository
    ) {}

    /**
     * Retourne la liste des tags (avec filtres et pagination).
     */
    public function index(Request $request): JsonResponse
    {
        return $this->try(function () use ($request) {
            $paginator = $this->repository->paginate($request->all(), $request->integer('per_page', 15));

            return $this->paginated($paginator, TagResource::class);
        }, 'Impossible de récupérer les tags.');
    }

    /**
     * Affiche un(e) Tag.
     */
    public function show(Tag $tag): JsonResponse
    {
        return $this->try(function () use ($tag) {
            $model = $this->repository->findOrFail($tag->id);

            return $this->success(new TagResource($model), 'Tag récupéré.');
        }, 'Impossible de récupérer ce tag.');
    }

    /**
     * Crée un(e) Tag.
     */
    public function store(StoreTagRequest $request): JsonResponse
    {
        return $this->try(function () use ($request) {
            $model = $this->repository->create($request->validated());

            return $this->created(new TagResource($model));
        }, 'Impossible de créer le tag.');
    }

    /**
     * Met à jour un(e) Tag.
     */
    public function update(Tag $tag, UpdateTagRequest $request): JsonResponse
    {
        return $this->try(function () use ($request, $tag) {
            $model = $this->repository->update($tag, $request->validated());

            return $this->updated(new TagResource($model));
        }, 'Impossible de mettre à jour le tag.');
    }

    /**
     * Supprime un(e) Tag.
     */
    public function destroy(Tag $tag): JsonResponse
    {
        return $this->try(function () use ($tag) {
            $this->repository->delete($tag);

            return $this->deleted();
        }, 'Impossible de supprimer le tag.');
    }
}
