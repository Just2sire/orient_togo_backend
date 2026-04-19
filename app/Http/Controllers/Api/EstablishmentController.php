<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEstablishmentRequest;
use App\Http\Requests\UpdateEstablishmentRequest;
use App\Models\Establishment;
use App\Services\EstablishmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="Establishment",
 *     description="Gestion des establishments"
 * )
 */
class EstablishmentController extends Controller
{
    public function __construct(
        private readonly EstablishmentService $service
    ) {}

    /**
     * @OA\Get(
     *     path="/api/establishments",
     *     summary="Liste des establishments",
     *     description="Retourne une liste paginée avec filtres optionnels",
     *     operationId="getEstablishments",
     *     tags={"Establishment"},
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
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/EstablishmentResource")),
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
     *     path="/api/establishments/{idOrSlug}",
     *     summary="Détails d'un(e) Establishment",
     *     operationId="getEstablishment",
     *     tags={"Establishment"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(
     *         name="idOrSlug",
     *         in="path",
     *         required=true,
     *
     *         @OA\Schema(type="string")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Establishment trouvé(e)",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", ref="#/components/schemas/EstablishmentResource")
     *         )
     *     ),
     *
     *     @OA\Response(response=404, description="Non trouvé(e)", @OA\JsonContent(ref="#/components/schemas/ErrorResponse"))
     * )
     */
    public function show(string $idOrSlug): JsonResponse
    {
        return $this->service->show($idOrSlug);
    }

    /**
     * @OA\Post(
     *     path="/api/establishments",
     *     summary="Créer un(e) Establishment",
     *     operationId="createEstablishment",
     *     tags={"Establishment"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(ref="#/components/schemas/StoreEstablishmentRequest")
     *     ),
     *
     *     @OA\Response(
     *         response=201,
     *         description="Establishment créé(e)",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", ref="#/components/schemas/EstablishmentResource")
     *         )
     *     ),
     *
     *     @OA\Response(response=422, description="Erreur de validation", @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse"))
     * )
     */
    public function store(StoreEstablishmentRequest $request): JsonResponse
    {
        // $this->authorize('create', Establishment::class);

        return $this->service->store($request);
    }

    /**
     * @OA\Put(
     *     path="/api/establishments/{id}",
     *     summary="Mettre à jour un(e) Establishment",
     *     operationId="updateEstablishment",
     *     tags={"Establishment"},
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
     *         @OA\JsonContent(ref="#/components/schemas/UpdateEstablishmentRequest")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Establishment mis(e) à jour",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", ref="#/components/schemas/EstablishmentResource")
     *         )
     *     ),
     *
     *     @OA\Response(response=404, description="Non trouvé(e)", @OA\JsonContent(ref="#/components/schemas/ErrorResponse"))
     * )
     */
    public function update(UpdateEstablishmentRequest $request, Establishment $establishment): JsonResponse
    {
        // $this->authorize('update', $establishment);

        return $this->service->update($establishment, $request);
    }

    /**
     * @OA\Delete(
     *     path="/api/establishments/{id}",
     *     summary="Supprimer un(e) Establishment",
     *     operationId="deleteEstablishment",
     *     tags={"Establishment"},
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
     *         description="Establishment supprimé(e)",
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
    public function destroy(Establishment $establishment): JsonResponse
    {
        // $this->authorize('delete', $establishment);

        return $this->service->destroy($establishment);
    }

    /**
     * @OA\Post(
     *     path="/api/establishments/{id}/verify",
     *     summary="Valider un établissement (Admin)",
     *     tags={"Establishment"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *
     *     @OA\Response(response=200, description="Succès")
     * )
     */
    public function verify(Establishment $establishment): JsonResponse
    {
        // $this->authorize('verify', $establishment);
        return $this->service->verify($establishment, auth()->user());
    }
}
