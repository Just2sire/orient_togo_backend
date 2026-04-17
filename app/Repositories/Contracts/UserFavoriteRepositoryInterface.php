<?php

namespace App\Repositories\Contracts;

use App\Models\UserFavorite;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface UserFavoriteRepositoryInterface
{
    /**
     * Retourne une liste paginée (avec filtres optionnels).
     *
     * @param  array<string, mixed>  $filters
     */
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    /**
     * Retourne tous les userFavorites sans pagination.
     *
     * @return Collection<int, UserFavorite>
     */
    public function all(array $filters = []): Collection;

    /**
     * Trouve un(e) UserFavorite par son identifiant (lève ModelNotFoundException si absent).
     */
    public function findOrFail(string|int $id): UserFavorite;

    /**
     * Crée un(e) UserFavorite.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): UserFavorite;

    /**
     * Met à jour un(e) UserFavorite.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(UserFavorite $userFavorite, array $data): UserFavorite;

    /**
     * Supprime un(e) UserFavorite.
     */
    public function delete(UserFavorite $userFavorite): bool;
}