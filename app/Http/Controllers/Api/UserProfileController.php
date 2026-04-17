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
        security: [["sanctum" => []]],
        tags: ["Profil Utilisateur"]
    )]
    #[OA\Response(
        response: 200,
        description: "Profil récupéré",
        content: new OA\JsonContent(ref: "#/components/schemas/UserProfileResource")
    )]
    public function show(Request $request): JsonResponse
    {
        return $this->service->show($request->user());
    }

    #[OA\Put(
        path: "/v1/user/profile",
        summary: "Mettre à jour mon profil",
        security: [["sanctum" => []]],
        tags: ["Profil Utilisateur"]
    )]
    #[OA\RequestBody(
        content: new OA\JsonContent(ref: "#/components/schemas/UpdateUserProfileRequest")
    )]
    #[OA\Response(
        response: 200,
        description: "Profil mis à jour",
        content: new OA\JsonContent(ref: "#/components/schemas/UserProfileResource")
    )]
    public function update(UpdateUserProfileRequest $request): JsonResponse
    {
        return $this->service->update($request->user(), $request);
    }

    #[OA\Patch(
        path: "/v1/user/profile/onboarding",
        summary: "Terminer l'onboarding",
        security: [["sanctum" => []]],
        tags: ["Profil Utilisateur"]
    )]
    #[OA\Response(response: 200, description: "Onboarding marqué comme terminé")]
    public function completeOnboarding(Request $request): JsonResponse
    {
        return $this->service->completeOnboarding($request->user());
    }
}
