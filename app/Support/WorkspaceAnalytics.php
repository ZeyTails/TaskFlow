<?php

namespace App\Support;

use App\Models\ActivityLog;
use App\Models\Project;
use App\Models\Task;
use App\Models\TaskComment;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Support\Collection;

class WorkspaceAnalytics
{
    public static function forUser(User $user): array
    {
        $workspaceIds = $user->workspaces()->pluck('workspaces.id');

        if ($workspaceIds->isEmpty()) {
            return [
                'workspaceIds' => collect(),
                'projectIds' => collect(),
                'totalTasksCount' => 0,
                'completedTasksCount' => 0,
                'completionRate' => 0,
                'recentActivities' => collect(),
                'projectStats' => collect(),
                'memberStats' => collect(),
                'recentCompletedTasks' => collect(),
            ];
        }

        $projectIds = Project::query()
            ->whereIn('workspace_id', $workspaceIds)
            ->pluck('id');

        $taskQuery = Task::query()
            ->whereHas('project', fn ($query) => $query->whereIn('workspace_id', $workspaceIds));

        $totalTasksCount = (clone $taskQuery)->count();
        $completedTasksCount = (clone $taskQuery)
            ->where('status', Task::STATUS_DONE)
            ->count();

        $projectStats = Project::query()
            ->whereIn('workspace_id', $workspaceIds)
            ->with('workspace:id,name,icon_key')
            ->withCount([
                'tasks',
                'tasks as completed_tasks_count' => fn ($query) => $query->where('status', Task::STATUS_DONE),
                'tasks as overdue_tasks_count' => fn ($query) => $query
                    ->whereNotNull('due_date')
                    ->whereDate('due_date', '<', today())
                    ->where('status', '!=', Task::STATUS_DONE),
            ])
            ->latest()
            ->get()
            ->each(function (Project $project): void {
                $project->progress_rate = $project->tasks_count > 0
                    ? (int) round(($project->completed_tasks_count / $project->tasks_count) * 100)
                    : 0;
            });

        $recentActivities = ActivityLog::query()
            ->whereIn('workspace_id', $workspaceIds)
            ->with([
                'actor:id,name',
                'workspace:id,name',
                'project:id,name',
                'task:id,title',
            ])
            ->latest()
            ->limit(12)
            ->get();

        // Eager load comments for comment_added activities
        $commentIds = $recentActivities
            ->where('type', 'comment_added')
            ->pluck('meta.comment_id')
            ->filter()
            ->unique()
            ->values();

        if ($commentIds->isNotEmpty()) {
            $comments = TaskComment::query()
                ->whereIn('id', $commentIds)
                ->pluck('content', 'id');

            $recentActivities->each(function (ActivityLog $activity) use ($comments): void {
                if ($activity->type === 'comment_added' && isset($activity->meta['comment_id'])) {
                    $activity->setRelation('comment', (object) [
                        'content' => $comments->get($activity->meta['comment_id']),
                    ]);
                }
            });
        }

        $memberStats = self::memberStats($workspaceIds, $projectIds);

        $recentCompletedTasks = Task::query()
            ->whereHas('project', fn ($query) => $query->whereIn('workspace_id', $workspaceIds))
            ->where('status', Task::STATUS_DONE)
            ->with(['project:id,name,workspace_id', 'project.workspace:id,name'])
            ->latest('updated_at')
            ->limit(6)
            ->get();

        return [
            'workspaceIds' => $workspaceIds,
            'projectIds' => $projectIds,
            'totalTasksCount' => $totalTasksCount,
            'completedTasksCount' => $completedTasksCount,
            'completionRate' => $totalTasksCount > 0 ? (int) round(($completedTasksCount / $totalTasksCount) * 100) : 0,
            'recentActivities' => $recentActivities,
            'projectStats' => $projectStats,
            'memberStats' => $memberStats,
            'recentCompletedTasks' => $recentCompletedTasks,
        ];
    }

    private static function memberStats(Collection $workspaceIds, Collection $projectIds): Collection
    {
        if ($projectIds->isEmpty()) {
            return collect();
        }

        return User::query()
            ->select('users.id', 'users.name', 'users.email', 'users.avatar_path')
            ->join('workspace_user as memberships', 'memberships.user_id', '=', 'users.id')
            ->whereIn('memberships.workspace_id', $workspaceIds)
            ->where('memberships.status', Workspace::MEMBER_STATUS_ACTIVE)
            ->distinct()
            ->withCount([
                'assignedTasks as assigned_tasks_count' => fn ($query) => $query->whereIn('tasks.project_id', $projectIds),
                'assignedTasks as completed_tasks_count' => fn ($query) => $query
                    ->whereIn('tasks.project_id', $projectIds)
                    ->where('tasks.status', Task::STATUS_DONE),
            ])
            ->get()
            ->each(function (User $member): void {
                $member->completion_rate = $member->assigned_tasks_count > 0
                    ? (int) round(($member->completed_tasks_count / $member->assigned_tasks_count) * 100)
                    : 0;
            })
            ->sortByDesc('completed_tasks_count')
            ->values();
    }
}
