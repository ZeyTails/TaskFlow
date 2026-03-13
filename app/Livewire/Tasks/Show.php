<?php

namespace App\Livewire\Tasks;

use App\Models\Task;
use App\Models\TaskComment;
use App\Models\Workspace;
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

    public ?int $assigneeId = null;

    public string $commentContent = '';

    public function mount(Task $task): void
    {
        $this->task = $task;
        $this->task->loadMissing(['project.workspace.owner', 'assignee', 'creator', 'comments.user']);

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
            'assigneeId' => [
                'nullable',
                'integer',
                Rule::exists('workspace_user', 'user_id')->where(function ($query): void {
                    $query->where('workspace_id', $this->workspace->id)
                        ->where('status', Workspace::MEMBER_STATUS_ACTIVE);
                }),
            ],
        ]);

        $this->task->update([
            'status' => $validated['status'],
            'priority' => $validated['priority'],
            'due_date' => $validated['dueDate'],
            'assignee_id' => $validated['assigneeId'],
        ]);

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

        $this->task->comments()->create([
            'user_id' => Auth::id(),
            'content' => $validated['commentContent'],
        ]);

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
        $this->task->load(['project.workspace.owner', 'assignee:id,name,email,avatar_path', 'creator:id,name,email', 'comments.user:id,name,email,avatar_path']);

        $members = $this->workspace->members()
            ->wherePivot('status', Workspace::MEMBER_STATUS_ACTIVE)
            ->select('users.id', 'users.name', 'users.email', 'users.avatar_path')
            ->orderBy('users.name')
            ->get();

        return view('livewire.tasks.show', [
            'members' => $members,
            'canUpdate' => Auth::user()->can('update', $this->task),
            'canComment' => Auth::user()->can('create', [TaskComment::class, $this->task]),
        ]);
    }

    private function refreshTask(): void
    {
        $this->task = $this->task->fresh();
        $this->task->load(['project.workspace.owner', 'assignee:id,name,email,avatar_path', 'creator:id,name,email', 'comments.user:id,name,email,avatar_path']);
    }

    private function syncFormFromTask(): void
    {
        $this->status = $this->task->status;
        $this->priority = $this->task->priority;
        $this->dueDate = $this->task->due_date?->format('Y-m-d');
        $this->assigneeId = $this->task->assignee_id;
    }
}
