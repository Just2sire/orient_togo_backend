<?php

namespace App\Repositories;

use App\Models\OtpCode;
use App\Repositories\Contracts\OtpCodeRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class OtpCodeRepository implements OtpCodeRepositoryInterface
{
    public function __construct(private readonly OtpCode $model) {}

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->query()
            ->when($filters['phone'] ?? null, fn ($q, $v) => $q->where('phone', 'like', "%{$v}%"))
            ->latest()
            ->paginate($perPage);
    }

    public function all(array $filters = []): Collection
    {
        return $this->model->query()
            ->when($filters['phone'] ?? null, fn ($q, $v) => $q->where('phone', 'like', "%{$v}%"))
            ->latest()
            ->get();
    }

    public function findOrFail(string|int $id): OtpCode
    {
        return $this->model->findOrFail($id);
    }

    public function createForPhone(string $phone, string $hashedCode, string $type): OtpCode
    {
        return DB::transaction(fn () => $this->model->create([
            'phone' => $phone,
            'code' => $hashedCode,
            'type' => $type,
            'expires_at' => now()->addMinutes(10),
            'ip_address' => request()->ip(),
        ]));
    }

    public function findValidForPhone(string $phone, string $type): ?OtpCode
    {
        return $this->model->query()
            ->where('phone', $phone)
            ->where('type', $type)
            ->where('is_used', false)
            ->where('attempts', '<', 3)
            ->where('expires_at', '>', now())
            ->latest('created_at')
            ->first();
    }

    public function countRecentForPhone(string $phone, int $minutes = 60): int
    {
        return $this->model->query()
            ->where('phone', $phone)
            ->where('created_at', '>', now()->subMinutes($minutes))
            ->count();
    }

    public function invalidatePreviousForPhone(string $phone, string $type): void
    {
        DB::transaction(fn () => $this->model->query()
            ->where('phone', $phone)
            ->where('type', $type)
            ->where('is_used', false)
            ->update(['is_used' => true])
        );
    }

    public function create(array $data): OtpCode
    {
        return DB::transaction(fn () => $this->model->create($data));
    }

    public function update(OtpCode $otpCode, array $data): OtpCode
    {
        DB::transaction(fn () => $otpCode->update($data));

        return $otpCode->fresh();
    }

    public function delete(OtpCode $otpCode): bool
    {
        return DB::transaction(fn () => (bool) $otpCode->delete());
    }
}
