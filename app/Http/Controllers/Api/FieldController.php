<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreFieldRequest;
use App\Http\Requests\UpdateFieldRequest;
use App\Models\Field;
use App\Services\FieldService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Field', description: 'Gestion des fields')]
class FieldController extends Controller
{
    public function __construct(
        private readonly FieldService $service
    ) {}

    #[OA\Get(
        path: '/v1/fields',
        summary: 'Liste des fields',
        description: 'Retourne une liste paginée avec filtres optionnels',
        operationId: 'getFields',
        tags: ['Field']
    )]
    #[OA\Parameter(name: 'page', in: 'query', description: 'Numéro de page', required: false, schema: new OA\Schema(type: 'integer', default: 1))]
    #[OA\Parameter(name: 'per_page', in: 'query', description: 'Nombre d’éléments par page', required: false, schema: new OA\Schema(type: 'integer', default: 15))]
    #[OA\Parameter(name: 'search', in: 'query', description: 'Recherche par nom', required: false, schema: new OA\Schema(type: 'string'))]
    #[OA\Response(
        response: 200,
        description: 'Liste récupérée avec succès',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'success', type: 'boolean', example: true),
                new OA\Property(property: 'message', type: 'string'),
                new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/FieldResource')),
                new OA\Property(property: 'meta', type: 'object'),
            ]
        )
    )]
    #[OA\Response(response: 401, description: 'Non authentifié', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse'))]
    public function index(Request $request): JsonResponse
    {
        return $this->service->index($request);
    }

    #[OA\Get(
        path: '/v1/fields/{field}',
        summary: 'Détails d’un(e) Field',
        operationId: 'getField',
        tags: ['Field']
    )]
    #[OA\Parameter(name: 'field', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid'))]
    #[OA\Response(
        response: 200,
        description: 'Field trouvé(e)',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'success', type: 'boolean', example: true),
                new OA\Property(property: 'message', type: 'string'),
                new OA\Property(property: 'data', ref: '#/components/schemas/FieldResource'),
            ]
        )
    )]
    #[OA\Response(response: 404, description: 'Non trouvé(e)', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse'))]
    public function show(Field $field): JsonResponse
    {
        return $this->service->show($field);
    }

    #[OA\Post(
        path: '/v1/fields',
        summary: 'Créer un(e) Field',
        operationId: 'createField',
        tags: ['Field'],
        security: [['sanctum' => []]]
    )]
    #[OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/StoreFieldRequest'))]
    #[OA\Response(
        response: 201,
        description: 'Field créé(e)',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'success', type: 'boolean', example: true),
                new OA\Property(property: 'message', type: 'string'),
                new OA\Property(property: 'data', ref: '#/components/schemas/FieldResource'),
            ]
        )
    )]
    #[OA\Response(response: 422, description: 'Erreur de validation', content: new OA\JsonContent(ref: '#/components/schemas/ValidationErrorResponse'))]
    public function store(StoreFieldRequest $request): JsonResponse
    {
        // $this->authorize('create', Field::class);

        return $this->service->store($request);
    }

    #[OA\Put(
        path: '/v1/fields/{field}',
        summary: 'Mettre à jour un(e) Field',
        operationId: 'updateField',
        tags: ['Field'],
        security: [['sanctum' => []]]
    )]
    #[OA\Parameter(name: 'field', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid'))]
    #[OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/UpdateFieldRequest'))]
    #[OA\Response(
        response: 200,
        description: 'Field mis(e) à jour',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'success', type: 'boolean', example: true),
                new OA\Property(property: 'message', type: 'string'),
                new OA\Property(property: 'data', ref: '#/components/schemas/FieldResource'),
            ]
        )
    )]
    #[OA\Response(response: 404, description: 'Non trouvé(e)', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse'))]
    public function update(UpdateFieldRequest $request, Field $field): JsonResponse
    {
        // $this->authorize('update', $field);

        return $this->service->update($field, $request);
    }

    #[OA\Delete(
        path: '/v1/fields/{field}',
        summary: 'Supprimer un(e) Field',
        operationId: 'deleteField',
        tags: ['Field'],
        security: [['sanctum' => []]]
    )]
    #[OA\Parameter(name: 'field', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid'))]
    #[OA\Response(
        response: 200,
        description: 'Field supprimé(e)',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'success', type: 'boolean', example: true),
                new OA\Property(property: 'message', type: 'string'),
            ]
        )
    )]
    #[OA\Response(response: 404, description: 'Non trouvé(e)', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse'))]
    public function destroy(Field $field): JsonResponse
    {
        // $this->authorize('delete', $field);

        return $this->service->destroy($field);
    }
}
