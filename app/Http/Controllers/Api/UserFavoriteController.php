<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserFavoriteRequest;
use App\Services\UserFavoriteService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Tag(name: "Favoris", description: "Gestion des éléments favoris")]
class UserFavoriteController extends Controller
{
    public function __construct(
        private readonly UserFavoriteService $service
    ) {}

    #[OA\Get(
        path: "/v1/user/favorites",
        summary: "Lister mes favoris",
        security: [["sanctum" => []]],
        tags: ["Favoris"]
    )]
    #[OA\Parameter(name: "type", in: "query", description: "Filtrer par type (ex: App\Models\Quiz)", required: false)]
    #[OA\Response(response: 200, description: "Liste des favoris")]
    public function index(Request $request): JsonResponse
    {
        return $this->service->index($request->user(), $request->query('type'));
    }

    #[OA\Post(
        path: "/v1/user/favorites/toggle",
        summary: "Ajouter/Retirer un favori",
        security: [["sanctum" => []]],
        tags: ["Favoris"]
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ["favorable_type", "favorable_id"],
            properties: [
                new OA\Property(property: "favorable_type", type: "string", example: "App\Models\Quiz"),
                new OA\Property(property: "favorable_id", type: "string", format: "uuid"),
            ]
        )
    )]
    #[OA\Response(response: 200, description: "Action réussie")]
    public function toggle(StoreUserFavoriteRequest $request): JsonResponse
    {
        return $this->service->toggle($request->user(), $request->validated());
    }
}
