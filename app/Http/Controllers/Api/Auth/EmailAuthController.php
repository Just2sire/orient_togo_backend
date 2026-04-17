<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginWithEmailRequest;
use App\Http\Requests\Auth\RegisterWithEmailRequest;
use App\Http\Resources\Auth\AuthTokenResource;
use App\Services\Auth\AuthService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Authentification Email', description: "Endpoints pour la connexion et l'inscription classique")]
class EmailAuthController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly AuthService $authService
    ) {}

    #[OA\Post(
        path: '/v1/auth/login',
        summary: 'Connexion par email',
        tags: ['Authentification Email']
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['email', 'password'],
            properties: [
                new OA\Property(property: 'email', type: 'string', format: 'email', example: 'test@example.com'),
                new OA\Property(property: 'password', type: 'string', format: 'password', example: 'password123'),
                new OA\Property(property: 'device_name', type: 'string', example: 'Chrome Windows'),
            ]
        )
    )]
    #[OA\Response(
        response: 200,
        description: 'Connexion réussie',
        content: new OA\JsonContent(properties: [
            new OA\Property(property: 'success', type: 'boolean', example: true),
            new OA\Property(property: 'data', ref: '#/components/schemas/AuthTokenResource'),
        ])
    )]
    #[OA\Response(response: 401, description: 'Identifiants invalides')]
    public function login(LoginWithEmailRequest $request): JsonResponse
    {
        return $this->try(function () use ($request) {
            $result = $this->authService->loginWithEmail(
                email: $request->validated('email'),
                password: $request->validated('password'),
                deviceName: $request->validated('device_name')
            );

            return $this->success(new AuthTokenResource($result), 'Connexion réussie.');
        });
    }

    #[OA\Post(
        path: '/v1/auth/register',
        summary: 'Inscription par email',
        tags: ['Authentification Email']
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['email', 'password', 'password_confirmation'],
            properties: [
                new OA\Property(property: 'email', type: 'string', format: 'email', example: 'newuser@example.com'),
                new OA\Property(property: 'password', type: 'string', format: 'password', example: 'password123'),
                new OA\Property(property: 'password_confirmation', type: 'string', format: 'password', example: 'password123'),
                new OA\Property(property: 'device_name', type: 'string', example: 'Android App'),
            ]
        )
    )]
    #[OA\Response(
        response: 201,
        description: 'Compte créé',
        content: new OA\JsonContent(properties: [
            new OA\Property(property: 'success', type: 'boolean', example: true),
            new OA\Property(property: 'message', type: 'string', example: 'Compte créé avec succès.'),
            new OA\Property(property: 'data', ref: '#/components/schemas/AuthTokenResource'),
        ])
    )]
    #[OA\Response(response: 422, description: 'Erreur de validation')]
    public function register(RegisterWithEmailRequest $request): JsonResponse
    {
        return $this->try(function () use ($request) {
            $result = $this->authService->registerWithEmail(
                email: $request->validated('email'),
                password: $request->validated('password'),
                deviceName: $request->validated('device_name')
            );

            return $this->created(new AuthTokenResource($result), 'Compte créé avec succès.');
        });
    }
}
