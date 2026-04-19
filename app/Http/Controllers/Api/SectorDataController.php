<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSectorDataRequest;
use App\Http\Requests\UpdateSectorDataRequest;
use App\Models\SectorData;
use App\Services\SectorDataService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="SectorData",
 *     description="Gestion des sectorDatas"
 * )
 */
class SectorDataController extends Controller
{
    public function __construct(
        private readonly SectorDataService $service
    ) {}

    /**
     * @OA\Get(
     *     path="/api/sector-datas",
     *     summary="Liste des sectorDatas",
     *     description="Retourne une liste paginée avec filtres optionnels",
     *     operationId="getSectorDatas",
     *     tags={"SectorData"},
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
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/SectorDataResource")),
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
     *     path="/api/sector-datas/{id}",
     *     summary="Détails d'un(e) SectorData",
     *     operationId="getSectorData",
     *     tags={"SectorData"},
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
     *         description="SectorData trouvé(e)",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", ref="#/components/schemas/SectorDataResource")
     *         )
     *     ),
     *
     *     @OA\Response(response=404, description="Non trouvé(e)", @OA\JsonContent(ref="#/components/schemas/ErrorResponse"))
     * )
     */
    public function show(SectorData $sectorData): JsonResponse
    {
        return $this->service->show($sectorData);
    }

    /**
     * @OA\Post(
     *     path="/api/sector-datas",
     *     summary="Créer un(e) SectorData",
     *     operationId="createSectorData",
     *     tags={"SectorData"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(ref="#/components/schemas/StoreSectorDataRequest")
     *     ),
     *
     *     @OA\Response(
     *         response=201,
     *         description="SectorData créé(e)",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", ref="#/components/schemas/SectorDataResource")
     *         )
     *     ),
     *
     *     @OA\Response(response=422, description="Erreur de validation", @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse"))
     * )
     */
    public function store(StoreSectorDataRequest $request): JsonResponse
    {
        // $this->authorize('create', SectorData::class);

        return $this->service->store($request);
    }

    /**
     * @OA\Put(
     *     path="/api/sector-datas/{id}",
     *     summary="Mettre à jour un(e) SectorData",
     *     operationId="updateSectorData",
     *     tags={"SectorData"},
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
     *         @OA\JsonContent(ref="#/components/schemas/UpdateSectorDataRequest")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="SectorData mis(e) à jour",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", ref="#/components/schemas/SectorDataResource")
     *         )
     *     ),
     *
     *     @OA\Response(response=404, description="Non trouvé(e)", @OA\JsonContent(ref="#/components/schemas/ErrorResponse"))
     * )
     */
    public function update(UpdateSectorDataRequest $request, SectorData $sectorData): JsonResponse
    {
        // $this->authorize('update', $sectorData);

        return $this->service->update($sectorData, $request);
    }

    /**
     * @OA\Delete(
     *     path="/api/sector-datas/{id}",
     *     summary="Supprimer un(e) SectorData",
     *     operationId="deleteSectorData",
     *     tags={"SectorData"},
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
     *         description="SectorData supprimé(e)",
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
    public function destroy(SectorData $sectorData): JsonResponse
    {
        // $this->authorize('delete', $sectorData);

        return $this->service->destroy($sectorData);
    }
}
