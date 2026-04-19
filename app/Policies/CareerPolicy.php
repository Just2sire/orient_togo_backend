<?php

namespace App\Policies;

use App\Models\Career;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CareerPolicy
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

    public function view(User $user, Career $career): bool
    {
        return $career->organization_id === $user->current_organization_id;
    }

    public function create(User $user): bool
    {
        return $this->isOrgAdmin($user);
    }

    public function update(User $user, Career $career): bool
    {
        return $career->organization_id === $user->current_organization_id
            && $this->isOrgAdmin($user);
    }

    public function delete(User $user, Career $career): bool
    {
        return $career->organization_id === $user->current_organization_id
            && $this->isOrgAdmin($user);
    }

    public function restore(User $user, Career $career): bool
    {
        return $this->isOrgAdmin($user);
    }

    public function forceDelete(User $user, Career $career): bool
    {
        return false;
    }
}
