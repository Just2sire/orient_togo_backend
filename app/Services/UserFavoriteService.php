<?php

namespace App\Services;

use App\Http\Requests\StoreUserFavoriteRequest;
use App\Http\Resources\UserFavoriteResource;
use App\Models\User;
use App\Models\UserFavorite;
use App\Repositories\Contracts\UserFavoriteRepositoryInterface;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class UserFavoriteService
{
    use ApiResponse;

    public function __construct(
        private readonly UserFavoriteRepositoryInterface $repository
    ) {}

    /**
     * Ajoute ou retire un favori.
     */
    public function toggle(User $user, array $data): JsonResponse
    {
        return $this->try(function () use ($user, $data) {
            $favorite = UserFavorite::where([
                'user_id' => $user->id,
                'favorable_type' => $data['favorable_type'],
                'favorable_id' => $data['favorable_id'],
            ])->first();

            if ($favorite) {
                $favorite->delete();
                return $this->success(null, 'Retiré des favoris.');
            }

            $favorite = UserFavorite::create([
                'user_id' => $user->id,
                'favorable_type' => $data['favorable_type'],
                'favorable_id' => $data['favorable_id'],
            ]);

            return $this->created(new UserFavoriteResource($favorite), 'Ajouté aux favoris.');
        });
    }

    /**
     * Liste les favoris par type.
     */
    public function index(User $user, ?string $type = null): JsonResponse
    {
        return $this->try(function () use ($user, $type) {
            $query = $user->userFavorites();
            
            if ($type) {
                $query->where('favorable_type', $type);
            }

            return $this->success(UserFavoriteResource::collection($query->get()), 'Liste des favoris.');
        });
    }
}
