<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Models\Workspace;

class TaskPolicy
{
    public function viewAny(User $user, Project $project): bool
    {
        return $this->canReadWorkspace($user, $project->workspace);
    }

    public function view(User $user, Task $task): bool
    {
        return $this->canReadWorkspace($user, $task->project->workspace);
    }

    public function create(User $user, Project $project): bool
    {
        return $this->canWriteWorkspace($user, $project->workspace);
    }

    public function update(User $user, Task $task): bool
    {
        return $this->canWriteWorkspace($user, $task->project->workspace);
    }

    public function delete(User $user, Task $task): bool
    {
        return $this->canWriteWorkspace($user, $task->project->workspace);
    }

    public function assign(User $user, Task $task): bool
    {
        return $this->canWriteWorkspace($user, $task->project->workspace);
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
