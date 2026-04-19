<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateUserProfileRequest;
use App\Services\UserProfileService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Tag(name: "Profil Utilisateur", description: "Gestion des profils et préférences")]
class UserProfileController extends Controller
{
    public function __construct(
        private readonly UserProfileService $service
    ) {}

    #[OA\Get(
        path: "/v1/user/profile",
        summary: "Récupérer mon profil",
        operationId: "getUserProfile",
        security: [["sanctum" => []]],
        tags: ["Profil Utilisateur"]
    )]
    #[OA\Response(
        response: 200,
        description: "Profil récupéré",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "success", type: "boolean", example: true),
                new OA\Property(property: "message", type: "string"),
                new OA\Property(property: "data", ref: "#/components/schemas/UserProfileResource"),
            ]
        )
    )]
    public function show(Request $request): JsonResponse
    {
        return $this->service->show($request->user());
    }

    #[OA\Put(
        path: "/v1/user/profile",
        summary: "Mettre à jour mon profil",
        operationId: "updateUserProfile",
        security: [["sanctum" => []]],
        tags: ["Profil Utilisateur"]
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(ref: "#/components/schemas/UpdateUserProfileRequest")
    )]
    #[OA\Response(
        response: 200,
        description: "Profil mis à jour",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "success", type: "boolean", example: true),
                new OA\Property(property: "message", type: "string"),
                new OA\Property(property: "data", ref: "#/components/schemas/UserProfileResource"),
            ]
        )
    )]
    public function update(UpdateUserProfileRequest $request): JsonResponse
    {
        return $this->service->update($request->user(), $request);
    }

    #[OA\Patch(
        path: "/v1/user/profile/onboarding",
        summary: "Terminer l'onboarding",
        operationId: "completeUserOnboarding",
        security: [["sanctum" => []]],
        tags: ["Profil Utilisateur"]
    )]
    #[OA\Response(
        response: 200,
        description: "Onboarding marqué comme terminé",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "success", type: "boolean", example: true),
                new OA\Property(property: "message", type: "string"),
            ]
        )
    )]
    public function completeOnboarding(Request $request): JsonResponse
    {
        return $this->service->completeOnboarding($request->user());
    }
}
