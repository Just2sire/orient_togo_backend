<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginWithEmailRequest;
use App\Http\Requests\Auth\RegisterWithEmailRequest;
use App\Http\Resources\Auth\AuthTokenResource;
use App\Services\Auth\AuthService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class EmailAuthController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly AuthService $authService
    ) {}

    /**
     * Connexion par email + mot de passe.
     */
    public function login(LoginWithEmailRequest $request): JsonResponse
    {
        return $this->try(function () use ($request) {
            $result = $this->authService->loginWithEmail(
                email:      $request->validated('email'),
                password:   $request->validated('password'),
                deviceName: $request->validated('device_name')
            );

            return $this->success(new AuthTokenResource($result), 'Connexion réussie.');
        });
    }

    /**
     * Inscription par email + mot de passe.
     */
    public function register(RegisterWithEmailRequest $request): JsonResponse
    {
        return $this->try(function () use ($request) {
            $result = $this->authService->registerWithEmail(
                email:      $request->validated('email'),
                password:   $request->validated('password'),
                deviceName: $request->validated('device_name')
            );

            return $this->created(new AuthTokenResource($result), 'Compte créé avec succès.');
        });
    }
}
