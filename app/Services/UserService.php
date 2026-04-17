<?php

namespace App\Services;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserService
{
    use ApiResponse;

    public function __construct(
        private readonly UserRepositoryInterface $repository
    ) {}

    /**
     * Retourne la liste des users (avec filtres et pagination).
     */
    public function index(Request $request): JsonResponse
    {
        return $this->try(function () use ($request) {
            $paginator = $this->repository->paginate($request->all(), $request->integer('per_page', 15));

            return $this->paginated($paginator, UserResource::class);
        }, 'Impossible de récupérer les users.');
    }

    /**
     * Affiche un(e) User.
     */
    public function show(User $user): JsonResponse
    {
        return $this->try(function () use ($user) {
            $model = $this->repository->findOrFail($user['id']);

            return $this->success(new UserResource($model), 'User récupéré.');
        }, 'Impossible de récupérer ce user.');
    }

    /**
     * Crée un(e) User.
     */
    public function store(StoreUserRequest $request): JsonResponse
    {
        return $this->try(function () use ($request) {
            $model = $this->repository->create($request->validated());

            return $this->created(new UserResource($model));
        }, 'Impossible de créer le user.');
    }

    /**
     * Met à jour un(e) User.
     */
    public function update(User $user, UpdateUserRequest $request): JsonResponse
    {
        return $this->try(function () use ($request, $user) {
            $model = $this->repository->update($user, $request->validated());

            return $this->updated(new UserResource($model));
        }, 'Impossible de mettre à jour le user.');
    }

    /**
     * Supprime un(e) User.
     */
    public function destroy(User $user): JsonResponse
    {
        return $this->try(function () use ($user) {
            $this->repository->delete($user);

            return $this->deleted();
        }, 'Impossible de supprimer le user.');
    }
}
