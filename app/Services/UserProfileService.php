<?php

namespace App\Services;

use App\Http\Requests\UpdateUserProfileRequest;
use App\Http\Resources\UserProfileResource;
use App\Models\User;
use App\Models\UserProfile;
use App\Repositories\Contracts\UserProfileRepositoryInterface;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class UserProfileService
{
    use ApiResponse;

    public function __construct(
        private readonly UserProfileRepositoryInterface $repository
    ) {}

    /**
     * Récupère le profil de l'utilisateur connecté.
     */
    public function show(User $user): JsonResponse
    {
        return $this->try(function () use ($user) {
            $profile = $user->userProfile()->firstOrCreate([
                'user_id' => $user->id,
            ], [
                'username' => $user->email ? explode('@', $user->email)[0] : 'user_' . substr($user->id, 0, 8),
                'level' => \App\Enums\SchoolLevelEnum::HighSchool,
                'region' => \App\Enums\RegionEnum::Maritime,
                'onboarding_done' => false,
                'dark_mode' => false,
                'notifications_on' => false,
            ]);

            return $this->success(new UserProfileResource($profile), 'Profil récupéré.');
        });
    }

    /**
     * Met à jour le profil.
     */
    public function update(User $user, UpdateUserProfileRequest $request): JsonResponse
    {
        return $this->try(function () use ($user, $request) {
            $profile = $user->userProfile()->firstOrCreate([
                'user_id' => $user->id,
            ], [
                'username' => $user->email ? explode('@', $user->email)[0] : 'user_' . substr($user->id, 0, 8),
                'level' => \App\Enums\SchoolLevelEnum::HighSchool,
                'region' => \App\Enums\RegionEnum::Maritime,
                'onboarding_done' => false,
                'dark_mode' => false,
                'notifications_on' => false,
            ]);
            
            $profile = $this->repository->update($profile, $request->validated());

            return $this->updated(new UserProfileResource($profile), 'Profil mis à jour.');
        });
    }

    /**
     * Marque l'onboarding comme terminé.
     */
    public function completeOnboarding(User $user): JsonResponse
    {
        return $this->try(function () use ($user) {
            $profile = $user->userProfile()->firstOrCreate([
                'user_id' => $user->id,
            ], [
                'username' => $user->email ? explode('@', $user->email)[0] : 'user_' . substr($user->id, 0, 8),
                'level' => \App\Enums\SchoolLevelEnum::HighSchool,
                'region' => \App\Enums\RegionEnum::Maritime,
                'onboarding_done' => false,
                'dark_mode' => false,
                'notifications_on' => false,
            ]);

            $profile->update(['onboarding_done' => true]);

            return $this->success(new UserProfileResource($profile), 'Onboarding terminé.');
        });
    }

    /**
     * Met à jour les préférences uniquement.
     */
    public function updatePreferences(User $user, array $preferences): JsonResponse
    {
        return $this->try(function () use ($user, $preferences) {
            $profile = $user->userProfile()->firstOrCreate([
                'user_id' => $user->id,
            ], [
                'username' => $user->email ? explode('@', $user->email)[0] : 'user_' . substr($user->id, 0, 8),
                'level' => \App\Enums\SchoolLevelEnum::HighSchool,
                'region' => \App\Enums\RegionEnum::Maritime,
                'onboarding_done' => false,
                'dark_mode' => false,
                'notifications_on' => false,
            ]);

            $profile->update($preferences);

            return $this->success(new UserProfileResource($profile), 'Préférences mises à jour.');
        });
    }
}
