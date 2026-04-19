<?php

namespace App\Policies;

use App\Models\Establishment;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class EstablishmentPolicy
{
    use HandlesAuthorization;

    private function isOrgAdmin(User $user): bool
    {
        return $user->memberships()
            ->where('organization_id', $user->current_organization_id)
            ->where('role', 'admin')
            ->where('is_active', true)
            ->exists();
    }

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Establishment $establishment): bool
    {
        return $establishment->organization_id === $user->current_organization_id;
    }

    public function create(User $user): bool
    {
        return $this->isOrgAdmin($user);
    }

    public function update(User $user, Establishment $establishment): bool
    {
        return $establishment->organization_id === $user->current_organization_id
            && $this->isOrgAdmin($user);
    }

    public function delete(User $user, Establishment $establishment): bool
    {
        return $establishment->organization_id === $user->current_organization_id
            && $this->isOrgAdmin($user);
    }

    public function restore(User $user, Establishment $establishment): bool
    {
        return $this->isOrgAdmin($user);
    }

    public function forceDelete(User $user, Establishment $establishment): bool
    {
        return false;
    }
}
