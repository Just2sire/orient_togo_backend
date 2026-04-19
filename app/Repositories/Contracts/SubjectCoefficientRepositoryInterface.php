<?php

namespace App\Repositories\Contracts;

use App\Models\SubjectCoefficient;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface SubjectCoefficientRepositoryInterface
{
    /**
     * Retourne une liste paginée (avec filtres optionnels).
     *
     * @param  array<string, mixed>  $filters
     */
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    /**
     * Retourne tous les subjectCoefficients sans pagination.
     *
     * @return Collection<int, SubjectCoefficient>
     */
    public function all(array $filters = []): Collection;

    /**
     * Trouve un(e) SubjectCoefficient par son identifiant (lève ModelNotFoundException si absent).
     */
    public function findOrFail(string|int $id): SubjectCoefficient;

    /**
     * Crée un(e) SubjectCoefficient.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): SubjectCoefficient;

    /**
     * Met à jour un(e) SubjectCoefficient.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(SubjectCoefficient $subjectCoefficient, array $data): SubjectCoefficient;

    /**
     * Supprime un(e) SubjectCoefficient.
     */
    public function delete(SubjectCoefficient $subjectCoefficient): bool;
}
