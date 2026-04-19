<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTagRequest;
use App\Http\Requests\UpdateTagRequest;
use App\Models\Tag;
use App\Services\TagService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Tag', description: 'Gestion des tags')]
class TagController extends Controller
{
    public function __construct(
        private readonly TagService $service
    ) {}

    #[OA\Get(
        path: '/v1/tags',
        summary: 'Liste des tags',
        description: 'Retourne une liste paginée avec filtres optionnels',
        operationId: 'getTags',
        security: [['sanctum' => []]],
        tags: ['Tag']
    )]
    #[OA\Parameter(name: 'page', in: 'query', description: 'Numéro de page', required: false, schema: new OA\Schema(type: 'integer', default: 1))]
    #[OA\Parameter(name: 'per_page', in: 'query', description: 'Nombre d\'éléments par page', required: false, schema: new OA\Schema(type: 'integer', default: 15))]
    #[OA\Parameter(name: 'search', in: 'query', description: 'Recherche par nom', required: false, schema: new OA\Schema(type: 'string'))]
    #[OA\Response(
        response: 200,
        description: 'Liste récupérée avec succès',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'success', type: 'boolean', example: true),
                new OA\Property(property: 'message', type: 'string'),
                new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/TagResource')),
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
        path: '/v1/tags/{tag}',
        summary: "Détails d'un(e) Tag",
        operationId: 'getTag',
        security: [['sanctum' => []]],
        tags: ['Tag']
    )]
    #[OA\Parameter(name: 'tag', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid'))]
    #[OA\Response(
        response: 200,
        description: 'Tag trouvé(e)',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'success', type: 'boolean', example: true),
                new OA\Property(property: 'message', type: 'string'),
                new OA\Property(property: 'data', ref: '#/components/schemas/TagResource'),
            ]
        )
    )]
    #[OA\Response(response: 404, description: 'Non trouvé(e)', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse'))]
    public function show(Tag $tag): JsonResponse
    {
        return $this->service->show($tag);
    }

    #[OA\Post(
        path: '/v1/tags',
        summary: 'Créer un(e) Tag',
        operationId: 'createTag',
        security: [['sanctum' => []]],
        tags: ['Tag']
    )]
    #[OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/StoreTagRequest'))]
    #[OA\Response(
        response: 201,
        description: 'Tag créé(e)',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'success', type: 'boolean', example: true),
                new OA\Property(property: 'message', type: 'string'),
                new OA\Property(property: 'data', ref: '#/components/schemas/TagResource'),
            ]
        )
    )]
    #[OA\Response(response: 422, description: 'Erreur de validation', content: new OA\JsonContent(ref: '#/components/schemas/ValidationErrorResponse'))]
    public function store(StoreTagRequest $request): JsonResponse
    {
        // $this->authorize('create', Tag::class);

        return $this->service->store($request);
    }

    #[OA\Put(
        path: '/v1/tags/{tag}',
        summary: 'Mettre à jour un(e) Tag',
        operationId: 'updateTag',
        security: [['sanctum' => []]],
        tags: ['Tag']
    )]
    #[OA\Parameter(name: 'tag', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid'))]
    #[OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/UpdateTagRequest'))]
    #[OA\Response(
        response: 200,
        description: 'Tag mis(e) à jour',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'success', type: 'boolean', example: true),
                new OA\Property(property: 'message', type: 'string'),
                new OA\Property(property: 'data', ref: '#/components/schemas/TagResource'),
            ]
        )
    )]
    #[OA\Response(response: 404, description: 'Non trouvé(e)', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse'))]
    public function update(UpdateTagRequest $request, Tag $tag): JsonResponse
    {
        // $this->authorize('update', $tag);

        return $this->service->update($tag, $request);
    }

    #[OA\Delete(
        path: '/v1/tags/{tag}',
        summary: 'Supprimer un(e) Tag',
        operationId: 'deleteTag',
        security: [['sanctum' => []]],
        tags: ['Tag']
    )]
    #[OA\Parameter(name: 'tag', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid'))]
    #[OA\Response(
        response: 200,
        description: 'Tag supprimé(e)',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'success', type: 'boolean', example: true),
                new OA\Property(property: 'message', type: 'string'),
            ]
        )
    )]
    #[OA\Response(response: 404, description: 'Non trouvé(e)', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse'))]
    public function destroy(Tag $tag): JsonResponse
    {
        // $this->authorize('delete', $tag);

        return $this->service->destroy($tag);
    }
}
