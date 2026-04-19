<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSerieRequest;
use App\Http\Requests\UpdateSerieRequest;
use App\Http\Resources\SerieResource;
use App\Models\Serie;
use App\Services\SerieService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="Serie",
 *     description="Gestion des series"
 * )
 */
class SerieController extends Controller
{
    public function __construct(
        private readonly SerieService $service
    ) {}

    /**
     * @OA\Get(
     *     path="/api/series",
     *     summary="Liste des series",
     *     description="Retourne une liste paginée avec filtres optionnels",
     *     operationId="getSeries",
     *     tags={"Serie"},
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
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/SerieResource")),
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
     *     path="/api/series/accessible",
     *     summary="Séries accessibles selon la moyenne",
     *     tags={"Serie"},
     *
     *     @OA\Parameter(name="average", in="query", required=true, @OA\Schema(type="number")),
     *
     *     @OA\Response(response=200, description="Succès")
     * )
     */
    public function accessible(Request $request): JsonResponse
    {
        $request->validate(['average' => 'required|numeric|between:0,20']);
        $series = $this->service->findAccessibleSeries($request->float('average'));

        return response()->json([
            'success' => true,
            'data' => SerieResource::collection($series),
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/series/{id}",
     *     summary="Détails d'un(e) Serie",
     *     operationId="getSerie",
     *     tags={"Serie"},
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
     *         description="Serie trouvé(e)",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", ref="#/components/schemas/SerieResource")
     *         )
     *     ),
     *
     *     @OA\Response(response=404, description="Non trouvé(e)", @OA\JsonContent(ref="#/components/schemas/ErrorResponse"))
     * )
     */
    public function show(Serie $serie): JsonResponse
    {
        return $this->service->show($serie);
    }

    /**
     * @OA\Post(
     *     path="/api/series",
     *     summary="Créer un(e) Serie",
     *     operationId="createSerie",
     *     tags={"Serie"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(ref="#/components/schemas/StoreSerieRequest")
     *     ),
     *
     *     @OA\Response(
     *         response=201,
     *         description="Serie créé(e)",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", ref="#/components/schemas/SerieResource")
     *         )
     *     ),
     *
     *     @OA\Response(response=422, description="Erreur de validation", @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse"))
     * )
     */
    public function store(StoreSerieRequest $request): JsonResponse
    {
        // $this->authorize('create', Serie::class);

        return $this->service->store($request);
    }

    /**
     * @OA\Put(
     *     path="/api/series/{id}",
     *     summary="Mettre à jour un(e) Serie",
     *     operationId="updateSerie",
     *     tags={"Serie"},
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
     *         @OA\JsonContent(ref="#/components/schemas/UpdateSerieRequest")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Serie mis(e) à jour",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", ref="#/components/schemas/SerieResource")
     *         )
     *     ),
     *
     *     @OA\Response(response=404, description="Non trouvé(e)", @OA\JsonContent(ref="#/components/schemas/ErrorResponse"))
     * )
     */
    public function update(UpdateSerieRequest $request, Serie $serie): JsonResponse
    {
        // $this->authorize('update', $serie);

        return $this->service->update($serie, $request);
    }

    /**
     * @OA\Delete(
     *     path="/api/series/{id}",
     *     summary="Supprimer un(e) Serie",
     *     operationId="deleteSerie",
     *     tags={"Serie"},
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
     *         description="Serie supprimé(e)",
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
    public function destroy(Serie $serie): JsonResponse
    {
        // $this->authorize('delete', $serie);

        return $this->service->destroy($serie);
    }
}
