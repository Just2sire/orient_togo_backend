<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreGrowthSectorRequest;
use App\Http\Requests\UpdateGrowthSectorRequest;
use App\Models\GrowthSector;
use App\Services\GrowthSectorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="GrowthSector",
 *     description="Gestion des growthSectors"
 * )
 */
class GrowthSectorController extends Controller
{
    public function __construct(
        private readonly GrowthSectorService $service
    ) {}

    /**
     * @OA\Get(
     *     path="/api/growth-sectors",
     *     summary="Liste des growthSectors",
     *     description="Retourne une liste paginée avec filtres optionnels",
     *     operationId="getGrowthSectors",
     *     tags={"GrowthSector"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Numéro de page",
     *         required=false,
     *
     *         @OA\Schema(type="integer", default=1)
     *     ),
     *
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Nombre d'éléments par page",
     *         required=false,
     *
     *         @OA\Schema(type="integer", default=15)
     *     ),
     *
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Recherche par nom",
     *         required=false,
     *
     *         @OA\Schema(type="string")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Liste récupérée avec succès",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/GrowthSectorResource")),
     *             @OA\Property(property="meta", type="object")
     *         )
     *     ),
     *
     *     @OA\Response(response=401, description="Non authentifié", @OA\JsonContent(ref="#/components/schemas/ErrorResponse"))
     * )
     */
    public function index(Request $request): JsonResponse
    {
        return $this->service->index($request);
    }

    /**
     * @OA\Get(
     *     path="/api/growth-sectors/{id}",
     *     summary="Détails d'un(e) GrowthSector",
     *     operationId="getGrowthSector",
     *     tags={"GrowthSector"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="GrowthSector trouvé(e)",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", ref="#/components/schemas/GrowthSectorResource")
     *         )
     *     ),
     *
     *     @OA\Response(response=404, description="Non trouvé(e)", @OA\JsonContent(ref="#/components/schemas/ErrorResponse"))
     * )
     */
    public function show(GrowthSector $growthSector): JsonResponse
    {
        return $this->service->show($growthSector);
    }

    /**
     * @OA\Post(
     *     path="/api/growth-sectors",
     *     summary="Créer un(e) GrowthSector",
     *     operationId="createGrowthSector",
     *     tags={"GrowthSector"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(ref="#/components/schemas/StoreGrowthSectorRequest")
     *     ),
     *
     *     @OA\Response(
     *         response=201,
     *         description="GrowthSector créé(e)",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", ref="#/components/schemas/GrowthSectorResource")
     *         )
     *     ),
     *
     *     @OA\Response(response=422, description="Erreur de validation", @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse"))
     * )
     */
    public function store(StoreGrowthSectorRequest $request): JsonResponse
    {
        // $this->authorize('create', GrowthSector::class);

        return $this->service->store($request);
    }

    /**
     * @OA\Put(
     *     path="/api/growth-sectors/{id}",
     *     summary="Mettre à jour un(e) GrowthSector",
     *     operationId="updateGrowthSector",
     *     tags={"GrowthSector"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(ref="#/components/schemas/UpdateGrowthSectorRequest")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="GrowthSector mis(e) à jour",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", ref="#/components/schemas/GrowthSectorResource")
     *         )
     *     ),
     *
     *     @OA\Response(response=404, description="Non trouvé(e)", @OA\JsonContent(ref="#/components/schemas/ErrorResponse"))
     * )
     */
    public function update(UpdateGrowthSectorRequest $request, GrowthSector $growthSector): JsonResponse
    {
        // $this->authorize('update', $growthSector);

        return $this->service->update($growthSector, $request);
    }

    /**
     * @OA\Delete(
     *     path="/api/growth-sectors/{id}",
     *     summary="Supprimer un(e) GrowthSector",
     *     operationId="deleteGrowthSector",
     *     tags={"GrowthSector"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="GrowthSector supprimé(e)",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *
     *     @OA\Response(response=404, description="Non trouvé(e)", @OA\JsonContent(ref="#/components/schemas/ErrorResponse"))
     * )
     */
    public function destroy(GrowthSector $growthSector): JsonResponse
    {
        // $this->authorize('delete', $growthSector);

        return $this->service->destroy($growthSector);
    }
}
