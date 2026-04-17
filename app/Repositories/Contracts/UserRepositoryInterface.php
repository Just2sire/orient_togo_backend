<?php

namespace App\Repositories\Contracts;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface UserRepositoryInterface
{
    /**
     * Retourne une liste paginée (avec filtres optionnels).
     *
     * @param  array<string, mixed>  $filters
     */
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    /**
     * Retourne tous les users sans pagination.
     *
     * @return Collection<int, User>
     */
    public function all(array $filters = []): Collection;

    /**
     * Trouve un(e) User par son identifiant (lève ModelNotFoundException si absent).
     */
    public function findOrFail(string|int $id): User;

    /**
     * Trouve un(e) User par son email.
     */
    public function findByEmail(string $email): ?User;

    /**
     * Trouve un(e) User par son numéro de téléphone.
     */
    public function findByPhone(string $phone): ?User;

    /**
     * Crée un(e) User à partir d'un OTP (inscription auto).
     */
    public function createFromOtp(string $phone): User;

    /**
     * Crée un(e) User avec email et mot de passe.
     */
    public function createWithPassword(string $email, string $password): User;

    /**
     * Crée un(e) User.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): User;

    /**
     * Met à jour un(e) User.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(User $user, array $data): User;

    /**
     * Supprime un(e) User.
     */
    public function delete(User $user): bool;
}
