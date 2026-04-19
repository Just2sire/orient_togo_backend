<?php

namespace App\Repositories\Contracts;

use App\Models\Course;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface CourseRepositoryInterface
{
    /**
     * Retourne une liste paginée (avec filtres optionnels).
     *
     * @param  array<string, mixed>  $filters
     */
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    /**
     * Retourne tous les courses sans pagination.
     *
     * @return Collection<int, Course>
     */
    public function all(array $filters = []): Collection;

    /**
     * Trouve un(e) Course par son identifiant (lève ModelNotFoundException si absent).
     */
    public function findOrFail(string|int $id): Course;

    /**
     * Crée un(e) Course.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Course;

    /**
     * Met à jour un(e) Course.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(Course $course, array $data): Course;

    /**
     * Supprime un(e) Course.
     */
    public function delete(Course $course): bool;
}
