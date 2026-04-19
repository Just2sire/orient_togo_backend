<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreFieldRequest;
use App\Http\Requests\UpdateFieldRequest;
use App\Models\Field;
use App\Services\FieldService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="Field",
 *     description="Gestion des fields"
 * )
 */
class FieldController extends Controller
{
    public function __construct(
        private readonly FieldService $service
    ) {}

    /**
     * @OA\Get(
     *     path="/api/fields",
     *     summary="Liste des fields",
     *     description="Retourne une liste paginée avec filtres optionnels",
     *     operationId="getFields",
     *     tags={"Field"},
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
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/FieldResource")),
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
     *     path="/api/fields/{id}",
     *     summary="Détails d'un(e) Field",
     *     operationId="getField",
     *     tags={"Field"},
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
     *         description="Field trouvé(e)",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", ref="#/components/schemas/FieldResource")
     *         )
     *     ),
     *
     *     @OA\Response(response=404, description="Non trouvé(e)", @OA\JsonContent(ref="#/components/schemas/ErrorResponse"))
     * )
     */
    public function show(Field $field): JsonResponse
    {
        return $this->service->show($field);
    }

    /**
     * @OA\Post(
     *     path="/api/fields",
     *     summary="Créer un(e) Field",
     *     operationId="createField",
     *     tags={"Field"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(ref="#/components/schemas/StoreFieldRequest")
     *     ),
     *
     *     @OA\Response(
     *         response=201,
     *         description="Field créé(e)",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", ref="#/components/schemas/FieldResource")
     *         )
     *     ),
     *
     *     @OA\Response(response=422, description="Erreur de validation", @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse"))
     * )
     */
    public function store(StoreFieldRequest $request): JsonResponse
    {
        // $this->authorize('create', Field::class);

        return $this->service->store($request);
    }

    /**
     * @OA\Put(
     *     path="/api/fields/{id}",
     *     summary="Mettre à jour un(e) Field",
     *     operationId="updateField",
     *     tags={"Field"},
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
     *         @OA\JsonContent(ref="#/components/schemas/UpdateFieldRequest")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Field mis(e) à jour",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", ref="#/components/schemas/FieldResource")
     *         )
     *     ),
     *
     *     @OA\Response(response=404, description="Non trouvé(e)", @OA\JsonContent(ref="#/components/schemas/ErrorResponse"))
     * )
     */
    public function update(UpdateFieldRequest $request, Field $field): JsonResponse
    {
        // $this->authorize('update', $field);

        return $this->service->update($field, $request);
    }

    /**
     * @OA\Delete(
     *     path="/api/fields/{id}",
     *     summary="Supprimer un(e) Field",
     *     operationId="deleteField",
     *     tags={"Field"},
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
     *         description="Field supprimé(e)",
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
    public function destroy(Field $field): JsonResponse
    {
        // $this->authorize('delete', $field);

        return $this->service->destroy($field);
    }
}
