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
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Serie', description: 'Gestion des series')]
class SerieController extends Controller
{
    public function __construct(
        private readonly SerieService $service
    ) {}

    #[OA\Get(
        path: '/v1/series',
        summary: 'Liste des series',
        description: 'Retourne une liste paginée avec filtres optionnels',
        operationId: 'getSeries',
        tags: ['Serie']
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
                new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/SerieResource')),
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
        path: '/v1/series/accessible',
        summary: 'Séries accessibles selon la moyenne',
        operationId: 'getAccessibleSeries',
        tags: ['Serie']
    )]
    #[OA\Parameter(name: 'average', in: 'query', required: true, schema: new OA\Schema(type: 'number'))]
    #[OA\Response(
        response: 200,
        description: 'Succès',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'success', type: 'boolean', example: true),
                new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/SerieResource')),
            ]
        )
    )]
    public function accessible(Request $request): JsonResponse
    {
        $request->validate(['average' => 'required|numeric|between:0,20']);
        $series = $this->service->findAccessibleSeries($request->float('average'));

        return response()->json([
            'success' => true,
            'data' => SerieResource::collection($series),
        ]);
    }

    #[OA\Get(
        path: '/v1/series/{serie}',
        summary: 'Détails d’un(e) Serie',
        operationId: 'getSerie',
        tags: ['Serie']
    )]
    #[OA\Parameter(name: 'serie', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid'))]
    #[OA\Response(
        response: 200,
        description: 'Serie trouvé(e)',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'success', type: 'boolean', example: true),
                new OA\Property(property: 'message', type: 'string'),
                new OA\Property(property: 'data', ref: '#/components/schemas/SerieResource'),
            ]
        )
    )]
    #[OA\Response(response: 404, description: 'Non trouvé(e)', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse'))]
    public function show(Serie $serie): JsonResponse
    {
        return $this->service->show($serie);
    }

    #[OA\Post(
        path: '/v1/series',
        summary: 'Créer un(e) Serie',
        operationId: 'createSerie',
        tags: ['Serie'],
        security: [['sanctum' => []]]
    )]
    #[OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/StoreSerieRequest'))]
    #[OA\Response(
        response: 201,
        description: 'Serie créé(e)',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'success', type: 'boolean', example: true),
                new OA\Property(property: 'message', type: 'string'),
                new OA\Property(property: 'data', ref: '#/components/schemas/SerieResource'),
            ]
        )
    )]
    #[OA\Response(response: 422, description: 'Erreur de validation', content: new OA\JsonContent(ref: '#/components/schemas/ValidationErrorResponse'))]
    public function store(StoreSerieRequest $request): JsonResponse
    {
        // $this->authorize('create', Serie::class);

        return $this->service->store($request);
    }

    #[OA\Put(
        path: '/v1/series/{serie}',
        summary: 'Mettre à jour un(e) Serie',
        operationId: 'updateSerie',
        tags: ['Serie'],
        security: [['sanctum' => []]]
    )]
    #[OA\Parameter(name: 'serie', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid'))]
    #[OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/UpdateSerieRequest'))]
    #[OA\Response(
        response: 200,
        description: 'Serie mis(e) à jour',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'success', type: 'boolean', example: true),
                new OA\Property(property: 'message', type: 'string'),
                new OA\Property(property: 'data', ref: '#/components/schemas/SerieResource'),
            ]
        )
    )]
    #[OA\Response(response: 404, description: 'Non trouvé(e)', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse'))]
    public function update(UpdateSerieRequest $request, Serie $serie): JsonResponse
    {
        // $this->authorize('update', $serie);

        return $this->service->update($serie, $request);
    }

    #[OA\Delete(
        path: '/v1/series/{serie}',
        summary: 'Supprimer un(e) Serie',
        operationId: 'deleteSerie',
        tags: ['Serie'],
        security: [['sanctum' => []]]
    )]
    #[OA\Parameter(name: 'serie', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid'))]
    #[OA\Response(
        response: 200,
        description: 'Serie supprimé(e)',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'success', type: 'boolean', example: true),
                new OA\Property(property: 'message', type: 'string'),
            ]
        )
    )]
    #[OA\Response(response: 404, description: 'Non trouvé(e)', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse'))]
    public function destroy(Serie $serie): JsonResponse
    {
        // $this->authorize('delete', $serie);

        return $this->service->destroy($serie);
    }
}
