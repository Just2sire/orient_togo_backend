<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Services\Auth\AuthService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SessionController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly AuthService $authService
    ) {}

    /**
     * Retourne les informations de l'utilisateur connecté.
     */
    public function me(Request $request): JsonResponse
    {
        return $this->try(function () use ($request) {
            return $this->success(new UserResource($request->user()), 'Profil récupéré.');
        });
    }

    /**
     * Déconnecte l'utilisateur (révoque le token).
     */
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
