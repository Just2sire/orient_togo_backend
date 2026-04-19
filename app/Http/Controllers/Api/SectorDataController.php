<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSectorDataRequest;
use App\Http\Requests\UpdateSectorDataRequest;
use App\Models\SectorData;
use App\Services\SectorDataService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'SectorData', description: 'Gestion des sectorDatas')]
class SectorDataController extends Controller
{
    public function __construct(
        private readonly SectorDataService $service
    ) {}

    #[OA\Get(
        path: '/v1/sector-datas',
        summary: 'Liste des sectorDatas',
        description: 'Retourne une liste paginée avec filtres optionnels',
        operationId: 'getSectorDatas',
        tags: ['SectorData'],
        security: [['sanctum' => []]]
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
                new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/SectorDataResource')),
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
        path: '/v1/sector-datas/{sector_data}',
        summary: 'Détails d’un(e) SectorData',
        operationId: 'getSectorData',
        tags: ['SectorData'],
        security: [['sanctum' => []]]
    )]
    #[OA\Parameter(name: 'sector_data', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid'))]
    #[OA\Response(
        response: 200,
        description: 'SectorData trouvé(e)',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'success', type: 'boolean', example: true),
                new OA\Property(property: 'message', type: 'string'),
                new OA\Property(property: 'data', ref: '#/components/schemas/SectorDataResource'),
            ]
        )
    )]
    #[OA\Response(response: 404, description: 'Non trouvé(e)', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse'))]
    public function show(SectorData $sectorData): JsonResponse
    {
        return $this->service->show($sectorData);
    }

    #[OA\Post(
        path: '/v1/sector-datas',
        summary: 'Créer un(e) SectorData',
        operationId: 'createSectorData',
        tags: ['SectorData'],
        security: [['sanctum' => []]]
    )]
    #[OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/StoreSectorDataRequest'))]
    #[OA\Response(
        response: 201,
        description: 'SectorData créé(e)',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'success', type: 'boolean', example: true),
                new OA\Property(property: 'message', type: 'string'),
                new OA\Property(property: 'data', ref: '#/components/schemas/SectorDataResource'),
            ]
        )
    )]
    #[OA\Response(response: 422, description: 'Erreur de validation', content: new OA\JsonContent(ref: '#/components/schemas/ValidationErrorResponse'))]
    public function store(StoreSectorDataRequest $request): JsonResponse
    {
        // $this->authorize('create', SectorData::class);

        return $this->service->store($request);
    }

    #[OA\Put(
        path: '/v1/sector-datas/{sector_data}',
        summary: 'Mettre à jour un(e) SectorData',
        operationId: 'updateSectorData',
        tags: ['SectorData'],
        security: [['sanctum' => []]]
    )]
    #[OA\Parameter(name: 'sector_data', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid'))]
    #[OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/UpdateSectorDataRequest'))]
    #[OA\Response(
        response: 200,
        description: 'SectorData mis(e) à jour',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'success', type: 'boolean', example: true),
                new OA\Property(property: 'message', type: 'string'),
                new OA\Property(property: 'data', ref: '#/components/schemas/SectorDataResource'),
            ]
        )
    )]
    #[OA\Response(response: 404, description: 'Non trouvé(e)', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse'))]
    public function update(UpdateSectorDataRequest $request, SectorData $sectorData): JsonResponse
    {
        // $this->authorize('update', $sectorData);

        return $this->service->update($sectorData, $request);
    }

    #[OA\Delete(
        path: '/v1/sector-datas/{sector_data}',
        summary: 'Supprimer un(e) SectorData',
        operationId: 'deleteSectorData',
        tags: ['SectorData'],
        security: [['sanctum' => []]]
    )]
    #[OA\Parameter(name: 'sector_data', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid'))]
    #[OA\Response(
        response: 200,
        description: 'SectorData supprimé(e)',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'success', type: 'boolean', example: true),
                new OA\Property(property: 'message', type: 'string'),
            ]
        )
    )]
    #[OA\Response(response: 404, description: 'Non trouvé(e)', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse'))]
    public function destroy(SectorData $sectorData): JsonResponse
    {
        // $this->authorize('delete', $sectorData);

        return $this->service->destroy($sectorData);
    }
}
