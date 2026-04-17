<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\SendOtpRequest;
use App\Http\Requests\Auth\VerifyOtpRequest;
use App\Http\Resources\Auth\AuthTokenResource;
use App\Services\Auth\AuthService;
use App\Services\Auth\OtpCodeService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class OtpController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly AuthService $authService,
        private readonly OtpCodeService $otpService
    ) {}

    /**
     * Envoie un code OTP au numéro fourni.
     */
    public function send(SendOtpRequest $request): JsonResponse
    {
        return $this->try(function () use ($request) {
            $this->otpService->send(
                phone: $request->validated('phone'),
                type:  $request->validated('type', 'login')
            );

            return $this->success(null, 'Code OTP envoyé avec succès.');
        });
    }

    /**
     * Vérifie le code OTP et connecte/inscrit l'utilisateur.
     */
    public function verify(VerifyOtpRequest $request): JsonResponse
    {
        return $this->try(function () use ($request) {
            $result = $this->authService->loginWithOtp(
                phone:      $request->validated('phone'),
                code:       $request->validated('code'),
                deviceName: $request->validated('device_name')
            );

            return $this->success(new AuthTokenResource($result), 'Authentification réussie.');
        });
    }
}
