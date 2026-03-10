<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Workspace;

class WorkspacePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Workspace $workspace): bool
    {
        return $workspace->members()
            ->whereKey($user->id)
            ->wherePivot('status', Workspace::MEMBER_STATUS_ACTIVE)
            ->exists();
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Workspace $workspace): bool
    {
        return $this->isOwner($user, $workspace);
    }

    public function delete(User $user, Workspace $workspace): bool
    {
        return $this->isOwner($user, $workspace);
    }

    public function manageMembers(User $user, Workspace $workspace): bool
    {
        return $this->isOwner($user, $workspace);
    }

    private function isOwner(User $user, Workspace $workspace): bool
    {
        return $workspace->members()
            ->whereKey($user->id)
            ->wherePivot('role', Workspace::ROLE_OWNER)
            ->wherePivot('status', Workspace::MEMBER_STATUS_ACTIVE)
            ->exists();
    }
}
