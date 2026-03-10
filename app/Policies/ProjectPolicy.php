<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;
use App\Models\Workspace;

class ProjectPolicy
{
    public function viewAny(User $user, Workspace $workspace): bool
    {
        return $this->canReadWorkspace($user, $workspace);
    }

    public function view(User $user, Project $project): bool
    {
        return $this->canReadWorkspace($user, $project->workspace);
    }

    public function create(User $user, Workspace $workspace): bool
    {
        return $this->canWriteWorkspace($user, $workspace);
    }

    public function update(User $user, Project $project): bool
    {
        return $this->canWriteWorkspace($user, $project->workspace);
    }

    public function delete(User $user, Project $project): bool
    {
        return $this->canWriteWorkspace($user, $project->workspace);
    }

    private function canReadWorkspace(User $user, Workspace $workspace): bool
    {
        return $workspace->members()
            ->whereKey($user->id)
            ->wherePivot('status', Workspace::MEMBER_STATUS_ACTIVE)
            ->exists();
    }

    private function canWriteWorkspace(User $user, Workspace $workspace): bool
    {
        return $workspace->members()
            ->whereKey($user->id)
            ->wherePivot('status', Workspace::MEMBER_STATUS_ACTIVE)
            ->wherePivotIn('role', [Workspace::ROLE_OWNER, Workspace::ROLE_MEMBER])
            ->exists();
    }
}
