<?php

namespace App\Support;

use App\Models\ActivityLog;
use App\Models\Task;
use App\Models\TaskComment;
use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Support\Carbon;

class TaskCollaboration
{
    public static function recordTaskCreated(Task $task, User $actor): void
    {
        $task->loadMissing(['project.workspace', 'assignees:id,name']);

        ActivityLog::create([
            'workspace_id' => $task->project->workspace_id,
            'project_id' => $task->project_id,
            'task_id' => $task->id,
            'user_id' => $actor->id,
            'type' => 'task_created',
            'description' => sprintf('%s a cree la tache "%s".', $actor->name, $task->title),
            'meta' => [
                'status' => $task->status,
                'priority' => $task->priority,
            ],
        ]);

        self::notifyUsers(
            recipientIds: $task->assignees->modelKeys(),
            actor: $actor,
            task: $task,
            type: 'task_assigned',
            title: 'Nouvelle tache assignee',
            body: sprintf('%s vous a assigne la tache "%s".', $actor->name, $task->title),
        );
    }

    public static function recordTaskUpdated(Task $task, User $actor, array $beforeState, array $beforeAssigneeIds): void
    {
        $task->loadMissing(['project.workspace', 'assignees:id,name']);

        $changeSet = self::buildTaskChangeSet($task, $beforeState, $beforeAssigneeIds);
        $changes = collect($changeSet)
            ->pluck('label')
            ->all();
        $afterAssigneeIds = $task->assignees->modelKeys();
        $newAssigneeIds = array_values(array_diff($afterAssigneeIds, $beforeAssigneeIds));

        if ($changes !== []) {
            ActivityLog::create([
                'workspace_id' => $task->project->workspace_id,
                'project_id' => $task->project_id,
                'task_id' => $task->id,
                'user_id' => $actor->id,
                'type' => 'task_updated',
                'description' => sprintf('%s a mis a jour "%s" (%s).', $actor->name, $task->title, implode(', ', $changes)),
                'meta' => [
                    'changes' => $changes,
                    'change_set' => $changeSet,
                ],
            ]);
        }

        if ($newAssigneeIds !== []) {
            self::notifyUsers(
                recipientIds: $newAssigneeIds,
                actor: $actor,
                task: $task,
                type: 'task_assigned',
                title: 'Tache assignee',
                body: sprintf('%s vous a assigne la tache "%s".', $actor->name, $task->title),
            );
        }

        if ($changes !== []) {
            self::notifyUsers(
                recipientIds: array_values(array_diff($afterAssigneeIds, [$actor->id])),
                actor: $actor,
                task: $task,
                type: 'task_updated',
                title: 'Tache modifiee',
                body: sprintf('%s a modifie la tache "%s".', $actor->name, $task->title),
            );
        }
    }

    public static function recordCommentAdded(TaskComment $comment, User $actor): void
    {
        $comment->loadMissing('task.project.workspace');

        ActivityLog::create([
            'workspace_id' => $comment->task->project->workspace_id,
            'project_id' => $comment->task->project_id,
            'task_id' => $comment->task_id,
            'user_id' => $actor->id,
            'type' => 'comment_added',
            'description' => sprintf('%s a ajoute un commentaire sur "%s".', $actor->name, $comment->task->title),
            'meta' => [
                'comment_id' => $comment->id,
            ],
        ]);
    }

    private static function notifyUsers(array $recipientIds, User $actor, Task $task, string $type, string $title, string $body): void
    {
        $recipientIds = collect($recipientIds)
            ->map(fn ($id) => (int) $id)
            ->reject(fn ($id) => $id === $actor->id)
            ->unique()
            ->values();

        if ($recipientIds->isEmpty()) {
            return;
        }

        $timestamp = now();

        $rows = $recipientIds->map(fn (int $userId) => [
            'user_id' => $userId,
            'workspace_id' => $task->project->workspace_id,
            'task_id' => $task->id,
            'type' => $type,
            'title' => $title,
            'body' => $body,
            'meta' => json_encode([
                'project_name' => $task->project->name,
                'task_title' => $task->title,
            ], JSON_THROW_ON_ERROR),
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ])->all();

        UserNotification::insert($rows);
    }

    private static function buildTaskChangeSet(Task $task, array $beforeState, array $beforeAssigneeIds): array
    {
        $changes = [];

        if (($beforeState['status'] ?? null) !== $task->status) {
            $changes[] = [
                'field' => 'status',
                'label' => 'statut',
                'before' => Task::statusLabel($beforeState['status'] ?? ''),
                'after' => Task::statusLabel($task->status),
            ];
        }

        if (($beforeState['title'] ?? null) !== $task->title) {
            $changes[] = [
                'field' => 'title',
                'label' => 'titre',
                'before' => $beforeState['title'] ?? 'Sans titre',
                'after' => $task->title,
            ];
        }

        if (($beforeState['description'] ?? null) !== $task->description) {
            $changes[] = [
                'field' => 'description',
                'label' => 'description',
                'before' => filled($beforeState['description'] ?? null) ? $beforeState['description'] : 'Vide',
                'after' => filled($task->description) ? $task->description : 'Vide',
            ];
        }

        if (($beforeState['priority'] ?? null) !== $task->priority) {
            $changes[] = [
                'field' => 'priority',
                'label' => 'priorite',
                'before' => Task::priorityLabel($beforeState['priority'] ?? ''),
                'after' => Task::priorityLabel($task->priority),
            ];
        }

        $beforeDueDate = $beforeState['due_date'] ?? null;
        $afterDueDate = $task->due_date?->format('Y-m-d');

        if ($beforeDueDate !== $afterDueDate) {
            $changes[] = [
                'field' => 'due_date',
                'label' => 'echeance',
                'before' => $beforeDueDate ? Carbon::createFromFormat('Y-m-d', $beforeDueDate)->format('d/m/Y') : 'Non definie',
                'after' => $afterDueDate ? Carbon::createFromFormat('Y-m-d', $afterDueDate)->format('d/m/Y') : 'Non definie',
            ];
        }

        $afterAssigneeIds = $task->assignees->modelKeys();

        sort($beforeAssigneeIds);
        sort($afterAssigneeIds);

        if ($beforeAssigneeIds !== $afterAssigneeIds) {
            $changes[] = [
                'field' => 'assignees',
                'label' => 'assignes',
                'before' => self::formatAssigneeNames($beforeAssigneeIds),
                'after' => self::formatAssigneeNames($afterAssigneeIds),
            ];
        }

        return $changes;
    }

    private static function formatAssigneeNames(array $userIds): string
    {
        if ($userIds === []) {
            return 'Aucun assigne';
        }

        $names = User::query()
            ->whereIn('id', $userIds)
            ->orderBy('name')
            ->pluck('name')
            ->all();

        return $names !== [] ? implode(', ', $names) : 'Aucun assigne';
    }
}
