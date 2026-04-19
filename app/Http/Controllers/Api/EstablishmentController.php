<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEstablishmentRequest;
use App\Http\Requests\UpdateEstablishmentRequest;
use App\Models\Establishment;
use App\Services\EstablishmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Establishment', description: 'Gestion des establishments')]
class EstablishmentController extends Controller
{
    public function __construct(
        private readonly EstablishmentService $service
    ) {}

    #[OA\Get(
        path: '/v1/establishments',
        summary: 'Liste des establishments',
        description: 'Retourne une liste paginée avec filtres optionnels',
        operationId: 'getEstablishments',
        tags: ['Establishment']
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
                new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/EstablishmentResource')),
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
        path: '/v1/establishments/{idOrSlug}',
        summary: 'Détails d’un(e) Establishment',
        operationId: 'getEstablishment',
        tags: ['Establishment']
    )]
    #[OA\Parameter(name: 'idOrSlug', in: 'path', required: true, schema: new OA\Schema(type: 'string'))]
    #[OA\Response(
        response: 200,
        description: 'Establishment trouvé(e)',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'success', type: 'boolean', example: true),
                new OA\Property(property: 'message', type: 'string'),
                new OA\Property(property: 'data', ref: '#/components/schemas/EstablishmentResource'),
            ]
        )
    )]
    #[OA\Response(response: 404, description: 'Non trouvé(e)', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse'))]
    public function show(string $idOrSlug): JsonResponse
    {
        return $this->service->show($idOrSlug);
    }

    #[OA\Post(
        path: '/v1/establishments',
        summary: 'Créer un(e) Establishment',
        operationId: 'createEstablishment',
        tags: ['Establishment'],
        security: [['sanctum' => []]]
    )]
    #[OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/StoreEstablishmentRequest'))]
    #[OA\Response(
        response: 201,
        description: 'Establishment créé(e)',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'success', type: 'boolean', example: true),
                new OA\Property(property: 'message', type: 'string'),
                new OA\Property(property: 'data', ref: '#/components/schemas/EstablishmentResource'),
            ]
        )
    )]
    #[OA\Response(response: 422, description: 'Erreur de validation', content: new OA\JsonContent(ref: '#/components/schemas/ValidationErrorResponse'))]
    public function store(StoreEstablishmentRequest $request): JsonResponse
    {
        // $this->authorize('create', Establishment::class);

        return $this->service->store($request);
    }

    #[OA\Put(
        path: '/v1/establishments/{establishment}',
        summary: 'Mettre à jour un(e) Establishment',
        operationId: 'updateEstablishment',
        tags: ['Establishment'],
        security: [['sanctum' => []]]
    )]
    #[OA\Parameter(name: 'establishment', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid'))]
    #[OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/UpdateEstablishmentRequest'))]
    #[OA\Response(
        response: 200,
        description: 'Establishment mis(e) à jour',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'success', type: 'boolean', example: true),
                new OA\Property(property: 'message', type: 'string'),
                new OA\Property(property: 'data', ref: '#/components/schemas/EstablishmentResource'),
            ]
        )
    )]
    #[OA\Response(response: 404, description: 'Non trouvé(e)', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse'))]
    public function update(UpdateEstablishmentRequest $request, Establishment $establishment): JsonResponse
    {
        // $this->authorize('update', $establishment);

        return $this->service->update($establishment, $request);
    }

    #[OA\Delete(
        path: '/v1/establishments/{establishment}',
        summary: 'Supprimer un(e) Establishment',
        operationId: 'deleteEstablishment',
        tags: ['Establishment'],
        security: [['sanctum' => []]]
    )]
    #[OA\Parameter(name: 'establishment', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid'))]
    #[OA\Response(
        response: 200,
        description: 'Establishment supprimé(e)',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'success', type: 'boolean', example: true),
                new OA\Property(property: 'message', type: 'string'),
            ]
        )
    )]
    #[OA\Response(response: 404, description: 'Non trouvé(e)', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse'))]
    public function destroy(Establishment $establishment): JsonResponse
    {
        // $this->authorize('delete', $establishment);

        return $this->service->destroy($establishment);
    }

    #[OA\Post(
        path: '/v1/establishments/{establishment}/verify',
        summary: 'Valider un établissement (Admin)',
        operationId: 'verifyEstablishment',
        tags: ['Establishment'],
        security: [['sanctum' => []]]
    )]
    #[OA\Parameter(name: 'establishment', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid'))]
    #[OA\Response(
        response: 200,
        description: 'Succès',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'success', type: 'boolean', example: true),
                new OA\Property(property: 'message', type: 'string'),
            ]
        )
    )]
    public function verify(Establishment $establishment): JsonResponse
    {
        // $this->authorize('verify', $establishment);
        return $this->service->verify($establishment, auth()->user());
    }
}
