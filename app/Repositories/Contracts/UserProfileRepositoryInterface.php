<?php

namespace App\Repositories\Contracts;

use App\Models\UserProfile;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface UserProfileRepositoryInterface
{
    /**
     * Retourne une liste paginée (avec filtres optionnels).
     *
     * @param  array<string, mixed>  $filters
     */
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    /**
     * Retourne tous les userProfiles sans pagination.
     *
     * @return Collection<int, UserProfile>
     */
    public function all(array $filters = []): Collection;

    /**
     * Trouve un(e) UserProfile par son identifiant (lève ModelNotFoundException si absent).
     */
    public function findOrFail(string|int $id): UserProfile;

    /**
     * Crée un(e) UserProfile.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): UserProfile;

    /**
     * Met à jour un(e) UserProfile.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(UserProfile $userProfile, array $data): UserProfile;

    /**
     * Supprime un(e) UserProfile.
     */
    public function delete(UserProfile $userProfile): bool;
}