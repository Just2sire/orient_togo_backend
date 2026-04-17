<?php

namespace App\Repositories\Contracts;

use App\Models\OtpCode;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface OtpCodeRepositoryInterface
{
    /**
     * Retourne une liste paginée (avec filtres optionnels).
     *
     * @param  array<string, mixed>  $filters
     */
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    /**
     * Retourne tous les otpCodes sans pagination.
     *
     * @return Collection<int, OtpCode>
     */
    public function all(array $filters = []): Collection;

    /**
     * Trouve un(e) OtpCode par son identifiant (lève ModelNotFoundException si absent).
     */
    public function findOrFail(string|int $id): OtpCode;

    /**
     * Crée un OTP pour un numéro de téléphone.
     */
    public function createForPhone(string $phone, string $hashedCode, string $type): OtpCode;

    /**
     * Trouve un OTP valide pour un numéro et un type.
     */
    public function findValidForPhone(string $phone, string $type): ?OtpCode;

    /**
     * Compte les OTP récents pour un numéro (rate limiting).
     */
    public function countRecentForPhone(string $phone, int $minutes = 60): int;

    /**
     * Invalide les anciens OTP pour un numéro et un type.
     */
    public function invalidatePreviousForPhone(string $phone, string $type): void;

    /**
     * Crée un(e) OtpCode.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): OtpCode;

    /**
     * Met à jour un(e) OtpCode.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(OtpCode $otpCode, array $data): OtpCode;

    /**
     * Supprime un(e) OtpCode.
     */
    public function delete(OtpCode $otpCode): bool;
}
