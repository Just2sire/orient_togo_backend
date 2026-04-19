<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserDeviceRequest;
use App\Services\UserDeviceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Tag(name: "Appareils & Push", description: "Gestion des tokens de notification")]
class UserDeviceController extends Controller
{
    public function __construct(
        private readonly UserDeviceService $service
    ) {}

    #[OA\Get(
        path: "/v1/user/devices",
        summary: "Lister mes appareils",
        operationId: "getUserDevices",
        security: [["sanctum" => []]],
        tags: ["Appareils & Push"]
    )]
    #[OA\Response(
        response: 200,
        description: "Liste des appareils récupérée",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "success", type: "boolean", example: true),
                new OA\Property(property: "message", type: "string"),
                new OA\Property(property: "data", type: "array", items: new OA\Items(ref: "#/components/schemas/UserDeviceResource")),
            ]
        )
    )]
    public function index(Request $request): JsonResponse
    {
        return $this->service->index($request->user());
    }

    #[OA\Post(
        path: "/v1/user/devices",
        summary: "Enregistrer un appareil (Push Token)",
        operationId: "registerUserDevice",
        security: [["sanctum" => []]],
        tags: ["Appareils & Push"]
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ["push_token", "platform"],
            properties: [
                new OA\Property(property: "push_token", type: "string"),
                new OA\Property(property: "platform", enum: ["android", "ios", "web"], type: "string"),
            ]
        )
    )]
    #[OA\Response(
        response: 200,
        description: "Appareil enregistré",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "success", type: "boolean", example: true),
                new OA\Property(property: "message", type: "string"),
                new OA\Property(property: "data", ref: "#/components/schemas/UserDeviceResource"),
            ]
        )
    )]
    public function store(StoreUserDeviceRequest $request): JsonResponse
    {
        return $this->service->register($request->user(), $request);
    }
}
