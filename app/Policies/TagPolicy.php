<?php

namespace App\Policies;

use App\Models\Tag;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class TagPolicy
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

    public function view(User $user, Tag $tag): bool
    {
        return $tag->organization_id === $user->current_organization_id;
    }

    public function create(User $user): bool
    {
        return $this->isOrgAdmin($user);
    }

    public function update(User $user, Tag $tag): bool
    {
        return $tag->organization_id === $user->current_organization_id
            && $this->isOrgAdmin($user);
    }

    public function delete(User $user, Tag $tag): bool
    {
        return $tag->organization_id === $user->current_organization_id
            && $this->isOrgAdmin($user);
    }

    public function restore(User $user, Tag $tag): bool
    {
        return $this->isOrgAdmin($user);
    }

    public function forceDelete(User $user, Tag $tag): bool
    {
        return false;
    }
}
