<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSubjectCoefficientRequest;
use App\Http\Requests\UpdateSubjectCoefficientRequest;
use App\Models\SubjectCoefficient;
use App\Services\SubjectCoefficientService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'SubjectCoefficient', description: 'Gestion des subjectCoefficients')]
class SubjectCoefficientController extends Controller
{
    public function __construct(
        private readonly SubjectCoefficientService $service
    ) {}

    #[OA\Get(
        path: '/v1/subject-coefficients',
        summary: 'Liste des subjectCoefficients',
        description: 'Retourne une liste paginée avec filtres optionnels',
        operationId: 'getSubjectCoefficients',
        tags: ['SubjectCoefficient'],
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
                new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/SubjectCoefficientResource')),
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
        path: '/v1/subject-coefficients/{subject_coefficient}',
        summary: 'Détails d’un(e) SubjectCoefficient',
        operationId: 'getSubjectCoefficient',
        tags: ['SubjectCoefficient'],
        security: [['sanctum' => []]]
    )]
    #[OA\Parameter(name: 'subject_coefficient', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid'))]
    #[OA\Response(
        response: 200,
        description: 'SubjectCoefficient trouvé(e)',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'success', type: 'boolean', example: true),
                new OA\Property(property: 'message', type: 'string'),
                new OA\Property(property: 'data', ref: '#/components/schemas/SubjectCoefficientResource'),
            ]
        )
    )]
    #[OA\Response(response: 404, description: 'Non trouvé(e)', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse'))]
    public function show(SubjectCoefficient $subjectCoefficient): JsonResponse
    {
        return $this->service->show($subjectCoefficient);
    }

    #[OA\Post(
        path: '/v1/subject-coefficients',
        summary: 'Créer un(e) SubjectCoefficient',
        operationId: 'createSubjectCoefficient',
        tags: ['SubjectCoefficient'],
        security: [['sanctum' => []]]
    )]
    #[OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/StoreSubjectCoefficientRequest'))]
    #[OA\Response(
        response: 201,
        description: 'SubjectCoefficient créé(e)',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'success', type: 'boolean', example: true),
                new OA\Property(property: 'message', type: 'string'),
                new OA\Property(property: 'data', ref: '#/components/schemas/SubjectCoefficientResource'),
            ]
        )
    )]
    #[OA\Response(response: 422, description: 'Erreur de validation', content: new OA\JsonContent(ref: '#/components/schemas/ValidationErrorResponse'))]
    public function store(StoreSubjectCoefficientRequest $request): JsonResponse
    {
        // $this->authorize('create', SubjectCoefficient::class);

        return $this->service->store($request);
    }

    #[OA\Put(
        path: '/v1/subject-coefficients/{subject_coefficient}',
        summary: 'Mettre à jour un(e) SubjectCoefficient',
        operationId: 'updateSubjectCoefficient',
        tags: ['SubjectCoefficient'],
        security: [['sanctum' => []]]
    )]
    #[OA\Parameter(name: 'subject_coefficient', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid'))]
    #[OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/UpdateSubjectCoefficientRequest'))]
    #[OA\Response(
        response: 200,
        description: 'SubjectCoefficient mis(e) à jour',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'success', type: 'boolean', example: true),
                new OA\Property(property: 'message', type: 'string'),
                new OA\Property(property: 'data', ref: '#/components/schemas/SubjectCoefficientResource'),
            ]
        )
    )]
    #[OA\Response(response: 404, description: 'Non trouvé(e)', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse'))]
    public function update(UpdateSubjectCoefficientRequest $request, SubjectCoefficient $subjectCoefficient): JsonResponse
    {
        // $this->authorize('update', $subjectCoefficient);

        return $this->service->update($subjectCoefficient, $request);
    }

    #[OA\Delete(
        path: '/v1/subject-coefficients/{subject_coefficient}',
        summary: 'Supprimer un(e) SubjectCoefficient',
        operationId: 'deleteSubjectCoefficient',
        tags: ['SubjectCoefficient'],
        security: [['sanctum' => []]]
    )]
    #[OA\Parameter(name: 'subject_coefficient', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid'))]
    #[OA\Response(
        response: 200,
        description: 'SubjectCoefficient supprimé(e)',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'success', type: 'boolean', example: true),
                new OA\Property(property: 'message', type: 'string'),
            ]
        )
    )]
    #[OA\Response(response: 404, description: 'Non trouvé(e)', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse'))]
    public function destroy(SubjectCoefficient $subjectCoefficient): JsonResponse
    {
        // $this->authorize('delete', $subjectCoefficient);

        return $this->service->destroy($subjectCoefficient);
    }
}
