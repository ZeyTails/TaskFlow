<?php

namespace App\Livewire\Projects;

use App\Models\Project;
use App\Models\Workspace;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Projets')]
class Index extends Component
{
    use AuthorizesRequests;

    public Workspace $workspace;

    public string $name = '';

    public ?string $description = null;

    public string $priority = Project::PRIORITY_MEDIUM;

    public string $status = Project::STATUS_ACTIVE;

    public ?int $editingProjectId = null;

    public string $editName = '';

    public ?string $editDescription = null;

    public string $editPriority = Project::PRIORITY_MEDIUM;

    public string $editStatus = Project::STATUS_ACTIVE;

    public bool $showCreateForm = false;

    public function mount(Workspace $workspace): void
    {
        $this->workspace = $workspace;
        $this->authorize('view', $this->workspace);
    }

    /**
     * Create a new project in the current workspace.
     */
    public function createProject(): void
    {
        $this->authorize('create', [Project::class, $this->workspace]);

        $validated = $this->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('projects', 'name')->where('workspace_id', $this->workspace->id),
            ],
            'description' => ['nullable', 'string', 'max:2000'],
            'priority' => ['required', Rule::in(Project::PRIORITIES)],
            'status' => ['required', Rule::in(Project::STATUSES)],
        ]);

        $this->workspace->projects()->create([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'priority' => $validated['priority'],
            'status' => $validated['status'],
        ]);

        $this->reset(['name', 'description', 'priority', 'status', 'showCreateForm']);
        session()->flash('status', 'Projet cree avec succes.');
    }

    public function toggleCreateForm(): void
    {
        $this->authorize('create', [Project::class, $this->workspace]);
        $this->showCreateForm = ! $this->showCreateForm;
    }

    public function cancelCreateForm(): void
    {
        $this->reset(['name', 'description', 'priority', 'status', 'showCreateForm']);
    }

    public function startEditing(int $projectId): void
    {
        $project = $this->workspace->projects()->findOrFail($projectId);
        $this->authorize('update', $project);

        $this->editingProjectId = $project->id;
        $this->editName = $project->name;
        $this->editDescription = $project->description;
        $this->editPriority = $project->priority;
        $this->editStatus = $project->status;
    }

    public function cancelEditing(): void
    {
        $this->reset(['editingProjectId', 'editName', 'editDescription', 'editPriority', 'editStatus']);
    }

    public function updateProject(): void
    {
        if (! $this->editingProjectId) {
            return;
        }

        $project = $this->workspace->projects()->findOrFail($this->editingProjectId);
        $this->authorize('update', $project);

        $validated = $this->validate([
            'editName' => [
                'required',
                'string',
                'max:255',
                Rule::unique('projects', 'name')
                    ->where('workspace_id', $this->workspace->id)
                    ->ignore($project->id),
            ],
            'editDescription' => ['nullable', 'string', 'max:2000'],
            'editPriority' => ['required', Rule::in(Project::PRIORITIES)],
            'editStatus' => ['required', Rule::in(Project::STATUSES)],
        ]);

        $project->update([
            'name' => $validated['editName'],
            'description' => $validated['editDescription'],
            'priority' => $validated['editPriority'],
            'status' => $validated['editStatus'],
        ]);

        $this->cancelEditing();
        session()->flash('status', 'Projet mis a jour avec succes.');
    }

    public function deleteProject(int $projectId): void
    {
        $project = $this->workspace->projects()->findOrFail($projectId);
        $this->authorize('delete', $project);

        $project->delete();

        if ($this->editingProjectId === $projectId) {
            $this->cancelEditing();
        }

        session()->flash('status', 'Projet supprime avec succes.');
    }

    public function deleteWorkspace(): \Illuminate\Http\RedirectResponse
    {
        $this->authorize('delete', $this->workspace);

        $this->workspace->delete();

        session()->flash('status', 'Workspace supprime.');

        return redirect()->route('workspaces.index');
    }

    public function updateWorkspaceIcon(string $iconKey): void
    {
        $this->authorize('manageMembers', $this->workspace);

        validator(
            ['iconKey' => $iconKey],
            ['iconKey' => ['required', 'string', Rule::in(Workspace::ICON_KEYS)]]
        )->validate();

        $this->workspace->update([
            'icon_key' => $iconKey,
        ]);

        $this->workspace->refresh();
        session()->flash('status', 'Icone du workspace mise a jour.');
    }

    public function render()
    {
        $this->workspace->load('owner:id,name')->loadCount(['projects', 'members']);

        $projects = $this->workspace
            ->projects()
            ->withCount('tasks')
            ->orderByDesc('created_at')
            ->get();

        $canWrite = Auth::user()->can('create', [Project::class, $this->workspace]);
        $canManageMembers = Auth::user()->can('manageMembers', $this->workspace);

        return view('livewire.projects.index', [
            'projects' => $projects,
            'canWrite' => $canWrite,
            'canManageMembers' => $canManageMembers,
        ]);
    }
}
