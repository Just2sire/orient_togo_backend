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
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Authentification OTP', description: "Endpoints pour la connexion et l'inscription via téléphone")]
class OtpController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly AuthService $authService,
        private readonly OtpCodeService $otpService
    ) {}

    #[OA\Post(
        path: '/v1/auth/otp/send',
        summary: 'Demander un code OTP',
        operationId: 'sendOtp',
        tags: ['Authentification OTP']
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['phone'],
            properties: [
                new OA\Property(property: 'phone', description: 'Numéro de téléphone (8 chiffres)', type: 'string', example: '90010203'),
                new OA\Property(property: 'type', enum: ['login', 'register'], type: 'string', example: 'login'),
            ]
        )
    )]
    #[OA\Response(
        response: 200,
        description: 'Code OTP envoyé',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'success', type: 'boolean', example: true),
                new OA\Property(property: 'message', type: 'string', example: 'Code OTP envoyé avec succès.'),
            ]
        )
    )]
    #[OA\Response(response: 422, description: 'Erreur de validation', content: new OA\JsonContent(ref: '#/components/schemas/ValidationErrorResponse'))]
    public function send(SendOtpRequest $request): JsonResponse
    {
        return $this->try(function () use ($request) {
            $this->otpService->send(
                phone: $request->validated('phone'),
                type: $request->validated('type', 'login')
            );

            return $this->success(null, 'Code OTP envoyé avec succès.');
        });
    }

    #[OA\Post(
        path: '/v1/auth/otp/verify',
        summary: "Vérifier l'OTP et se connecter",
        operationId: 'verifyOtp',
        tags: ['Authentification OTP']
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['phone', 'code'],
            properties: [
                new OA\Property(property: 'phone', type: 'string', example: '90010203'),
                new OA\Property(property: 'code', description: 'Code à 6 chiffres reçu par SMS', type: 'string', example: '123456'),
                new OA\Property(property: 'device_name', type: 'string', example: 'iPhone 15 Pro'),
            ]
        )
    )]
    #[OA\Response(
        response: 200,
        description: 'Authentification réussie',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'success', type: 'boolean', example: true),
                new OA\Property(property: 'message', type: 'string', example: 'Authentification réussie.'),
                new OA\Property(property: 'data', ref: '#/components/schemas/AuthTokenResource'),
            ]
        )
    )]
    #[OA\Response(response: 401, description: 'Code invalide ou expiré', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse'))]
    public function verify(VerifyOtpRequest $request): JsonResponse
    {
        return $this->try(function () use ($request) {
            $result = $this->authService->loginWithOtp(
                phone: $request->validated('phone'),
                code: $request->validated('code'),
                deviceName: $request->validated('device_name')
            );

            return $this->success(new AuthTokenResource($result), 'Authentification réussie.');
        });
    }
}
