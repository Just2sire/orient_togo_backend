<?php

namespace App\Repositories\Contracts;

use App\Models\UserDevice;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface UserDeviceRepositoryInterface
{
    /**
     * Retourne une liste paginée (avec filtres optionnels).
     *
     * @param  array<string, mixed>  $filters
     */
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    /**
     * Retourne tous les userDevices sans pagination.
     *
     * @return Collection<int, UserDevice>
     */
    public function all(array $filters = []): Collection;

    /**
     * Trouve un(e) UserDevice par son identifiant (lève ModelNotFoundException si absent).
     */
    public function findOrFail(string|int $id): UserDevice;

    /**
     * Crée un(e) UserDevice.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): UserDevice;

    /**
     * Met à jour un(e) UserDevice.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(UserDevice $userDevice, array $data): UserDevice;

    /**
     * Supprime un(e) UserDevice.
     */
    public function delete(UserDevice $userDevice): bool;
}