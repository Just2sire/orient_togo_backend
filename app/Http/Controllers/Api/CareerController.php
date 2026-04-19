<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCareerRequest;
use App\Http\Requests\UpdateCareerRequest;
use App\Models\Career;
use App\Services\CareerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="Career",
 *     description="Gestion des careers"
 * )
 */
class CareerController extends Controller
{
    public function __construct(
        private readonly CareerService $service
    ) {}

    /**
     * @OA\Get(
     *     path="/api/careers",
     *     summary="Liste des careers",
     *     description="Retourne une liste paginée avec filtres optionnels",
     *     operationId="getCareers",
     *     tags={"Career"},
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
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/CareerResource")),
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
     *     path="/api/careers/{id}",
     *     summary="Détails d'un(e) Career",
     *     operationId="getCareer",
     *     tags={"Career"},
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
     *         description="Career trouvé(e)",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", ref="#/components/schemas/CareerResource")
     *         )
     *     ),
     *
     *     @OA\Response(response=404, description="Non trouvé(e)", @OA\JsonContent(ref="#/components/schemas/ErrorResponse"))
     * )
     */
    public function show(Career $career): JsonResponse
    {
        return $this->service->show($career);
    }

    /**
     * @OA\Post(
     *     path="/api/careers",
     *     summary="Créer un(e) Career",
     *     operationId="createCareer",
     *     tags={"Career"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(ref="#/components/schemas/StoreCareerRequest")
     *     ),
     *
     *     @OA\Response(
     *         response=201,
     *         description="Career créé(e)",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", ref="#/components/schemas/CareerResource")
     *         )
     *     ),
     *
     *     @OA\Response(response=422, description="Erreur de validation", @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse"))
     * )
     */
    public function store(StoreCareerRequest $request): JsonResponse
    {
        // $this->authorize('create', Career::class);

        return $this->service->store($request);
    }

    /**
     * @OA\Put(
     *     path="/api/careers/{id}",
     *     summary="Mettre à jour un(e) Career",
     *     operationId="updateCareer",
     *     tags={"Career"},
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
     *         @OA\JsonContent(ref="#/components/schemas/UpdateCareerRequest")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Career mis(e) à jour",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", ref="#/components/schemas/CareerResource")
     *         )
     *     ),
     *
     *     @OA\Response(response=404, description="Non trouvé(e)", @OA\JsonContent(ref="#/components/schemas/ErrorResponse"))
     * )
     */
    public function update(UpdateCareerRequest $request, Career $career): JsonResponse
    {
        // $this->authorize('update', $career);

        return $this->service->update($career, $request);
    }

    /**
     * @OA\Delete(
     *     path="/api/careers/{id}",
     *     summary="Supprimer un(e) Career",
     *     operationId="deleteCareer",
     *     tags={"Career"},
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
     *         description="Career supprimé(e)",
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
    public function destroy(Career $career): JsonResponse
    {
        // $this->authorize('delete', $career);

        return $this->service->destroy($career);
    }
}
