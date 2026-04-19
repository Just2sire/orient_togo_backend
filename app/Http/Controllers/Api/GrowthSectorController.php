<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreGrowthSectorRequest;
use App\Http\Requests\UpdateGrowthSectorRequest;
use App\Models\GrowthSector;
use App\Services\GrowthSectorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'GrowthSector', description: 'Gestion des growthSectors')]
class GrowthSectorController extends Controller
{
    public function __construct(
        private readonly GrowthSectorService $service
    ) {}

    #[OA\Get(
        path: '/v1/growth-sectors',
        summary: 'Liste des growthSectors',
        description: 'Retourne une liste paginée avec filtres optionnels',
        operationId: 'getGrowthSectors',
        tags: ['GrowthSector']
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
                new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/GrowthSectorResource')),
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
        path: '/v1/growth-sectors/{growth_sector}',
        summary: 'Détails d’un(e) GrowthSector',
        operationId: 'getGrowthSector',
        tags: ['GrowthSector']
    )]
    #[OA\Parameter(name: 'growth_sector', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid'))]
    #[OA\Response(
        response: 200,
        description: 'GrowthSector trouvé(e)',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'success', type: 'boolean', example: true),
                new OA\Property(property: 'message', type: 'string'),
                new OA\Property(property: 'data', ref: '#/components/schemas/GrowthSectorResource'),
            ]
        )
    )]
    #[OA\Response(response: 404, description: 'Non trouvé(e)', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse'))]
    public function show(GrowthSector $growthSector): JsonResponse
    {
        return $this->service->show($growthSector);
    }

    #[OA\Post(
        path: '/v1/growth-sectors',
        summary: 'Créer un(e) GrowthSector',
        operationId: 'createGrowthSector',
        tags: ['GrowthSector'],
        security: [['sanctum' => []]]
    )]
    #[OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/StoreGrowthSectorRequest'))]
    #[OA\Response(
        response: 201,
        description: 'GrowthSector créé(e)',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'success', type: 'boolean', example: true),
                new OA\Property(property: 'message', type: 'string'),
                new OA\Property(property: 'data', ref: '#/components/schemas/GrowthSectorResource'),
            ]
        )
    )]
    #[OA\Response(response: 422, description: 'Erreur de validation', content: new OA\JsonContent(ref: '#/components/schemas/ValidationErrorResponse'))]
    public function store(StoreGrowthSectorRequest $request): JsonResponse
    {
        // $this->authorize('create', GrowthSector::class);

        return $this->service->store($request);
    }

    #[OA\Put(
        path: '/v1/growth-sectors/{growth_sector}',
        summary: 'Mettre à jour un(e) GrowthSector',
        operationId: 'updateGrowthSector',
        tags: ['GrowthSector'],
        security: [['sanctum' => []]]
    )]
    #[OA\Parameter(name: 'growth_sector', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid'))]
    #[OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/UpdateGrowthSectorRequest'))]
    #[OA\Response(
        response: 200,
        description: 'GrowthSector mis(e) à jour',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'success', type: 'boolean', example: true),
                new OA\Property(property: 'message', type: 'string'),
                new OA\Property(property: 'data', ref: '#/components/schemas/GrowthSectorResource'),
            ]
        )
    )]
    #[OA\Response(response: 404, description: 'Non trouvé(e)', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse'))]
    public function update(UpdateGrowthSectorRequest $request, GrowthSector $growthSector): JsonResponse
    {
        // $this->authorize('update', $growthSector);

        return $this->service->update($growthSector, $request);
    }

    #[OA\Delete(
        path: '/v1/growth-sectors/{growth_sector}',
        summary: 'Supprimer un(e) GrowthSector',
        operationId: 'deleteGrowthSector',
        tags: ['GrowthSector'],
        security: [['sanctum' => []]]
    )]
    #[OA\Parameter(name: 'growth_sector', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid'))]
    #[OA\Response(
        response: 200,
        description: 'GrowthSector supprimé(e)',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'success', type: 'boolean', example: true),
                new OA\Property(property: 'message', type: 'string'),
            ]
        )
    )]
    #[OA\Response(response: 404, description: 'Non trouvé(e)', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse'))]
    public function destroy(GrowthSector $growthSector): JsonResponse
    {
        // $this->authorize('delete', $growthSector);

        return $this->service->destroy($growthSector);
    }
}
