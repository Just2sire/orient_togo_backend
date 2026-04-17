<?php

namespace App\Services;

use App\Http\Requests\StoreUserDeviceRequest;
use App\Http\Resources\UserDeviceResource;
use App\Models\User;
use App\Models\UserDevice;
use App\Repositories\Contracts\UserDeviceRepositoryInterface;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class UserDeviceService
{
    use ApiResponse;

    public function __construct(
        private readonly UserDeviceRepositoryInterface $repository
    ) {}

    /**
     * Enregistre ou met à jour un appareil.
     */
    public function register(User $user, StoreUserDeviceRequest $request): JsonResponse
    {
        return $this->try(function () use ($user, $request) {
            $data = $request->validated();
            
            $device = UserDevice::updateOrCreate(
                ['user_id' => $user->id, 'push_token' => $data['push_token']],
                [
                    'platform' => $data['platform'],
                    'user_agent' => $request->userAgent(),
                    'last_active_at' => now(),
                ]
            );

            return $this->success(new UserDeviceResource($device), 'Appareil enregistré.');
        });
    }

    /**
     * Liste les appareils de l'utilisateur.
     */
    public function index(User $user): JsonResponse
    {
        return $this->try(function () use ($user) {
            $devices = $user->userDevices()->latest('last_active_at')->get();
            return $this->success(UserDeviceResource::collection($devices), 'Liste des appareils.');
        });
    }
}
