<?php

namespace App\Repositories;

use App\Enums\UserRoleEnum;
use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserRepository implements UserRepositoryInterface
{
    public function __construct(private readonly User $model) {}

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->query()
            ->when($filters['search'] ?? null, fn ($q, $v) => $q->where('email', 'like', "%{$v}%")->orWhere('phone', 'like', "%{$v}%"))
            ->latest()
            ->paginate($perPage);
    }

    public function all(array $filters = []): Collection
    {
        return $this->model->query()
            ->when($filters['search'] ?? null, fn ($q, $v) => $q->where('email', 'like', "%{$v}%")->orWhere('phone', 'like', "%{$v}%"))
            ->latest()
            ->get();
    }

    public function findOrFail(string|int $id): User
    {
        return $this->model->findOrFail($id);
    }

    public function findByEmail(string $email): ?User
    {
        return $this->model->withoutGlobalScopes()->where('email', $email)->first();
    }

    public function findByPhone(string $phone): ?User
    {
        return $this->model->withoutGlobalScopes()->where('phone', $phone)->first();
    }

    public function createFromOtp(string $phone): User
    {
        return DB::transaction(function () use ($phone) {
            $user = $this->model->create([
                'phone' => $phone,
                // On met un email bidon ou on laisse null si la migration le permet
                // Ici la migration users originale a email unique, donc on doit gérer
                'email' => $phone.'@orienttogo.tg',
                'password' => Hash::make(str()->random(32)),
                'role' => UserRoleEnum::Student,
                'is_active' => true,
            ]);

            $user->assignRole(UserRoleEnum::Student);

            return $user;
        });
    }

    public function createWithPassword(string $email, string $password): User
    {
        return DB::transaction(function () use ($email, $password) {
            $user = $this->model->create([
                'email' => $email,
                'password' => $password, // Le model a un cast 'hashed'
                'role' => UserRoleEnum::Student,
                'is_active' => true,
            ]);

            $user->assignRole(UserRoleEnum::Student);

            return $user;
        });
    }

    public function create(array $data): User
    {
        return DB::transaction(fn () => $this->model->create($data));
    }

    public function update(User $user, array $data): User
    {
        DB::transaction(fn () => $user->update($data));

        return $user->fresh();
    }

    public function delete(User $user): bool
    {
        return DB::transaction(fn () => (bool) $user->delete());
    }
}
