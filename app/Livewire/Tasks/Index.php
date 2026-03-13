<?php

namespace App\Livewire\Tasks;

use App\Models\Project;
use App\Models\Task;
use App\Models\Workspace;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Taches')]
class Index extends Component
{
    use AuthorizesRequests;

    public Workspace $workspace;

    public Project $project;

    public string $title = '';

    public ?string $description = null;

    public string $status = Task::STATUS_TODO;

    public string $priority = Task::PRIORITY_MEDIUM;

    public ?string $dueDate = null;

    public ?int $assigneeId = null;

    public ?int $editingTaskId = null;

    public string $editTitle = '';

    public ?string $editDescription = null;

    public string $editStatus = Task::STATUS_TODO;

    public string $editPriority = Task::PRIORITY_MEDIUM;

    public ?string $editDueDate = null;

    public ?int $editAssigneeId = null;

    public string $search = '';

    public string $filterStatus = 'all';

    public string $filterPriority = 'all';

    public ?int $filterAssignee = null;

    public function mount(Workspace $workspace, Project $project): void
    {
        if ($project->workspace_id !== $workspace->id) {
            abort(404);
        }

        $this->workspace = $workspace;
        $this->project = $project;
        $this->authorize('view', $this->project);
    }

    public function createTask(): void
    {
        $this->authorize('create', [Task::class, $this->project]);

        $validated = $this->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
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

        $this->project->tasks()->create([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'status' => $validated['status'],
            'priority' => $validated['priority'],
            'due_date' => $validated['dueDate'],
            'assignee_id' => $validated['assigneeId'],
            'created_by' => Auth::id(),
        ]);

        $this->reset(['title', 'description', 'status', 'priority', 'dueDate', 'assigneeId']);
        session()->flash('status', 'Tache creee avec succes.');
    }

    public function startEditing(int $taskId): void
    {
        $task = $this->project->tasks()->findOrFail($taskId);
        $this->authorize('update', $task);

        $this->editingTaskId = $task->id;
        $this->editTitle = $task->title;
        $this->editDescription = $task->description;
        $this->editStatus = $task->status;
        $this->editPriority = $task->priority;
        $this->editDueDate = $task->due_date?->format('Y-m-d');
        $this->editAssigneeId = $task->assignee_id;
    }

    public function cancelEditing(): void
    {
        $this->reset([
            'editingTaskId',
            'editTitle',
            'editDescription',
            'editStatus',
            'editPriority',
            'editDueDate',
            'editAssigneeId',
        ]);
    }

    public function updateTask(): void
    {
        if (! $this->editingTaskId) {
            return;
        }

        $task = $this->project->tasks()->findOrFail($this->editingTaskId);
        $this->authorize('update', $task);

        $validated = $this->validate([
            'editTitle' => ['required', 'string', 'max:255'],
            'editDescription' => ['nullable', 'string', 'max:2000'],
            'editStatus' => ['required', Rule::in(Task::STATUSES)],
            'editPriority' => ['required', Rule::in(Task::PRIORITIES)],
            'editDueDate' => ['nullable', 'date'],
            'editAssigneeId' => [
                'nullable',
                'integer',
                Rule::exists('workspace_user', 'user_id')->where(function ($query): void {
                    $query->where('workspace_id', $this->workspace->id)
                        ->where('status', Workspace::MEMBER_STATUS_ACTIVE);
                }),
            ],
        ]);

        $task->update([
            'title' => $validated['editTitle'],
            'description' => $validated['editDescription'],
            'status' => $validated['editStatus'],
            'priority' => $validated['editPriority'],
            'due_date' => $validated['editDueDate'],
            'assignee_id' => $validated['editAssigneeId'],
        ]);

        $this->cancelEditing();
        session()->flash('status', 'Tache mise a jour.');
    }

    public function deleteTask(int $taskId): void
    {
        $task = $this->project->tasks()->findOrFail($taskId);
        $this->authorize('delete', $task);

        $task->delete();

        if ($this->editingTaskId === $taskId) {
            $this->cancelEditing();
        }

        session()->flash('status', 'Tache supprimee.');
    }

    public function render()
    {
        $members = $this->workspace->members()
            ->wherePivot('status', Workspace::MEMBER_STATUS_ACTIVE)
            ->select('users.id', 'users.name', 'users.email')
            ->orderBy('users.name')
            ->get();

        $tasks = $this->project->tasks()
            ->with(['assignee:id,name,email'])
            ->when($this->search !== '', fn ($query) => $query->where('title', 'like', '%'.$this->search.'%'))
            ->when(in_array($this->filterStatus, Task::STATUSES, true), fn ($query) => $query->where('status', $this->filterStatus))
            ->when(in_array($this->filterPriority, Task::PRIORITIES, true), fn ($query) => $query->where('priority', $this->filterPriority))
            ->when($this->filterAssignee, fn ($query) => $query->where('assignee_id', $this->filterAssignee))
            ->orderByRaw('due_date IS NULL, due_date ASC')
            ->latest('updated_at')
            ->get();

        $canWrite = Auth::user()->can('create', [Task::class, $this->project]);

        return view('livewire.tasks.index', [
            'tasks' => $tasks,
            'members' => $members,
            'canWrite' => $canWrite,
        ]);
    }
}
