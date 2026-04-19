<?php

namespace App\Services;

use App\Http\Requests\StoreEstablishmentRequest;
use App\Http\Requests\UpdateEstablishmentRequest;
use App\Http\Resources\EstablishmentResource;
use App\Models\Establishment;
use App\Models\User;
use App\Repositories\Contracts\EstablishmentRepositoryInterface;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class EstablishmentService
{
    use ApiResponse;

    public function __construct(
        private readonly EstablishmentRepositoryInterface $repository
    ) {}

    /**
     * Retourne la liste des establishments (avec filtres et pagination).
     */
    public function index(Request $request): JsonResponse
    {
        return $this->try(function () use ($request) {
            $paginator = $this->repository->paginate($request->all(), $request->integer('per_page', 15));

            return $this->paginated($paginator, EstablishmentResource::class);
        }, 'Impossible de récupérer les establishments.');
    }

    /**
     * Affiche un(e) Establishment.
     */
    public function show(string $idOrSlug): JsonResponse
    {
        return $this->try(function () use ($idOrSlug) {
            if (Str::isUuid($idOrSlug)) {
                $model = $this->repository->findOrFail($idOrSlug);
            } else {
                $model = $this->repository->findBySlug($idOrSlug);
            }
            $model->load('courses', 'series', 'fields');

            return $this->success(new EstablishmentResource($model), 'Establishment récupéré.');
        }, 'Impossible de récupérer ce establishment.');
    }

    /**
     * Crée un(e) Establishment.
     */
    public function store(StoreEstablishmentRequest $request): JsonResponse
    {
        return $this->try(function () use ($request) {
            $model = $this->repository->create($request->validated());

            return $this->created(new EstablishmentResource($model));
        }, 'Impossible de créer le establishment.');
    }

    /**
     * Met à jour un(e) Establishment.
     */
    public function update(Establishment $establishment, UpdateEstablishmentRequest $request): JsonResponse
    {
        return $this->try(function () use ($request, $establishment) {
            $model = $this->repository->update($establishment, $request->validated());

            return $this->updated(new EstablishmentResource($model));
        }, 'Impossible de mettre à jour le establishment.');
    }

    /**
     * Supprime un(e) Establishment.
     */
    public function destroy(Establishment $establishment): JsonResponse
    {
        return $this->try(function () use ($establishment) {
            $this->repository->delete($establishment);

            return $this->deleted();
        }, 'Impossible de supprimer le establishment.');
    }

    /**
     * Valide un établissement.
     */
    public function verify(Establishment $establishment, User $verifier): JsonResponse
    {
        return $this->try(function () use ($establishment, $verifier) {
            $model = $this->repository->verify($establishment, $verifier);

            return $this->success(new EstablishmentResource($model), 'Establishment vérifié.');
        }, 'Impossible de vérifier le establishment.');
    }
}
