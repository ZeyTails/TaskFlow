<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\TaskComment;
use App\Models\User;
use App\Models\Workspace;

class TaskCommentPolicy
{
    public function viewAny(User $user, Task $task): bool
    {
        return $this->canReadWorkspace($user, $task->project->workspace);
    }

    public function view(User $user, TaskComment $taskComment): bool
    {
        return $this->canReadWorkspace($user, $taskComment->task->project->workspace);
    }

    public function create(User $user, Task $task): bool
    {
        return $this->canWriteWorkspace($user, $task->project->workspace);
    }

    public function delete(User $user, TaskComment $taskComment): bool
    {
        if ($taskComment->user_id === $user->id) {
            return true;
        }

        return $taskComment->task->project->workspace->members()
            ->whereKey($user->id)
            ->wherePivot('role', Workspace::ROLE_OWNER)
            ->exists();
    }

    private function canReadWorkspace(User $user, Workspace $workspace): bool
    {
        return $workspace->members()
            ->whereKey($user->id)
            ->exists();
    }

    private function canWriteWorkspace(User $user, Workspace $workspace): bool
    {
        return $workspace->members()
            ->whereKey($user->id)
            ->wherePivotIn('role', [Workspace::ROLE_OWNER, Workspace::ROLE_MEMBER])
            ->exists();
    }
}
