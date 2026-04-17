<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Services\Auth\AuthService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Session & Profil', description: 'Endpoints pour la gestion de la session utilisateur')]
class SessionController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly AuthService $authService
    ) {}

    #[OA\Get(
        path: '/v1/auth/me',
        summary: 'Récupérer le profil connecté',
        security: [['sanctum' => []]],
        tags: ['Session & Profil']
    )]
    #[OA\Response(
        response: 200,
        description: 'Profil récupéré',
        content: new OA\JsonContent(properties: [
            new OA\Property(property: 'success', type: 'boolean', example: true),
            new OA\Property(property: 'data', ref: '#/components/schemas/UserResource'),
        ])
    )]
    #[OA\Response(response: 401, description: 'Non authentifié')]
    public function me(Request $request): JsonResponse
    {
        return $this->try(function () use ($request) {
            return $this->success(new UserResource($request->user()), 'Profil récupéré.');
        });
    }

    #[OA\Post(
        path: '/v1/auth/logout',
        summary: 'Déconnexion',
        security: [['sanctum' => []]],
        tags: ['Session & Profil']
    )]
    #[OA\RequestBody(
        content: new OA\JsonContent(properties: [
            new OA\Property(property: 'all_devices', description: "Si vrai, révoque tous les tokens de l'utilisateur", type: 'boolean', example: false),
        ])
    )]
    #[OA\Response(
        response: 200,
        description: 'Déconnexion réussie',
        content: new OA\JsonContent(properties: [
            new OA\Property(property: 'success', type: 'boolean', example: true),
            new OA\Property(property: 'message', type: 'string', example: 'Déconnexion réussie.'),
        ])
    )]
    #[OA\Response(response: 401, description: 'Non authentifié')]
    public function logout(Request $request): JsonResponse
    {
        return $this->try(function () use ($request) {
            $this->authService->logout(
                request: $request,
                allDevices: $request->boolean('all_devices')
            );

            return $this->success(null, 'Déconnexion réussie.');
        });
    }
}
