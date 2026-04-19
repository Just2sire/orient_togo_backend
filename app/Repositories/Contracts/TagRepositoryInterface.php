<?php

namespace App\Repositories\Contracts;

use App\Models\Tag;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface TagRepositoryInterface
{
    /**
     * Retourne une liste paginée (avec filtres optionnels).
     *
     * @param  array<string, mixed>  $filters
     */
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    /**
     * Retourne tous les tags sans pagination.
     *
     * @return Collection<int, Tag>
     */
    public function all(array $filters = []): Collection;

    /**
     * Trouve un(e) Tag par son identifiant (lève ModelNotFoundException si absent).
     */
    public function findOrFail(string|int $id): Tag;

    /**
     * Crée un(e) Tag.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Tag;

    /**
     * Met à jour un(e) Tag.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(Tag $tag, array $data): Tag;

    /**
     * Supprime un(e) Tag.
     */
    public function delete(Tag $tag): bool;
}
