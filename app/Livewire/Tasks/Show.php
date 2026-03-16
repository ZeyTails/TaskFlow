<?php

namespace App\Livewire\Tasks;

use App\Models\Task;
use App\Models\TaskComment;
use App\Models\Workspace;
use App\Support\TaskCollaboration;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Detail tache')]
class Show extends Component
{
    use AuthorizesRequests;

    public Task $task;

    public Workspace $workspace;

    public string $status = Task::STATUS_TODO;

    public string $priority = Task::PRIORITY_MEDIUM;

    public ?string $dueDate = null;

    public array $assigneeIds = [];

    public string $commentContent = '';

    public function mount(Task $task): void
    {
        $this->task = $task;
        $this->task->loadMissing(['project.workspace.owner', 'assignees', 'creator', 'comments.user']);

        $this->authorize('view', $this->task);

        $this->workspace = $this->task->project->workspace;
        $this->syncFormFromTask();
    }

    public function updateTask(): void
    {
        $this->authorize('update', $this->task);

        $validated = $this->validate([
            'status' => ['required', Rule::in(Task::STATUSES)],
            'priority' => ['required', Rule::in(Task::PRIORITIES)],
            'dueDate' => ['nullable', 'date'],
            'assigneeIds' => ['nullable', 'array'],
            'assigneeIds.*' => [
                'integer',
                'distinct',
                Rule::exists('workspace_user', 'user_id')->where(function ($query): void {
                    $query->where('workspace_id', $this->workspace->id)
                        ->where('status', Workspace::MEMBER_STATUS_ACTIVE);
                }),
            ],
        ]);

        $beforeState = [
            'title' => $this->task->title,
            'description' => $this->task->description,
            'status' => $this->task->status,
            'priority' => $this->task->priority,
            'due_date' => $this->task->due_date?->format('Y-m-d'),
        ];
        $beforeAssigneeIds = $this->task->assignees()->pluck('users.id')->all();

        $this->task->update([
            'status' => $validated['status'],
            'priority' => $validated['priority'],
            'due_date' => $validated['dueDate'],
        ]);
        $this->task->syncAssignees($validated['assigneeIds'] ?? []);
        $this->task->refresh()->load('assignees:id,name');
        TaskCollaboration::recordTaskUpdated($this->task, Auth::user(), $beforeState, $beforeAssigneeIds);

        $this->refreshTask();
        $this->syncFormFromTask();

        session()->flash('task-status', 'Tache mise a jour.');
    }

    public function addComment(): void
    {
        $this->authorize('create', [TaskComment::class, $this->task]);

        $validated = $this->validate([
            'commentContent' => ['required', 'string', 'min:2', 'max:2000'],
        ]);

        $comment = $this->task->comments()->create([
            'user_id' => Auth::id(),
            'content' => $validated['commentContent'],
        ]);

        TaskCollaboration::recordCommentAdded($comment, Auth::user());

        $this->reset('commentContent');
        $this->refreshTask();

        session()->flash('comment-status', 'Commentaire ajoute.');
    }

    public function deleteComment(int $commentId): void
    {
        $comment = $this->task->comments()->findOrFail($commentId);
        $this->authorize('delete', $comment);

        $comment->delete();

        $this->refreshTask();
        session()->flash('comment-status', 'Commentaire supprime.');
    }

    public function render()
    {
        $this->task->load(['project.workspace.owner', 'assignees:id,name,email,avatar_path', 'creator:id,name,email', 'comments.user:id,name,email,avatar_path']);
        $activityLogs = $this->task->activityLogs()
            ->with('actor:id,name')
            ->latest()
            ->limit(10)
            ->get();

        $members = $this->workspace->members()
            ->wherePivot('status', Workspace::MEMBER_STATUS_ACTIVE)
            ->select('users.id', 'users.name', 'users.email', 'users.avatar_path')
            ->orderBy('users.name')
            ->get();

        return view('livewire.tasks.show', [
            'members' => $members,
            'activityLogs' => $activityLogs,
            'canUpdate' => Auth::user()->can('update', $this->task),
            'canComment' => Auth::user()->can('create', [TaskComment::class, $this->task]),
        ]);
    }

    private function refreshTask(): void
    {
        $this->task = $this->task->fresh();
        $this->task->load(['project.workspace.owner', 'assignees:id,name,email,avatar_path', 'creator:id,name,email', 'comments.user:id,name,email,avatar_path']);
    }

    private function syncFormFromTask(): void
    {
        $this->status = $this->task->status;
        $this->priority = $this->task->priority;
        $this->dueDate = $this->task->due_date?->format('Y-m-d');
        $this->assigneeIds = $this->task->assignees->pluck('id')->all();
    }
}
