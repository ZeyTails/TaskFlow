<?php

namespace App\Livewire\Workspaces;

use App\Models\Workspace;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Espaces de travail')]
class Index extends Component
{
    use AuthorizesRequests;

    public string $name = '';

    public string $joinCode = '';

    public ?int $editingWorkspaceId = null;

    public string $editWorkspaceName = '';

    public string $editWorkspaceIconKey = Workspace::ICON_KEYS[0];

    /**
     * Create a new workspace owned by the current user.
     */
    public function createWorkspace(): void
    {
        $this->authorize('create', Workspace::class);

        $user = Auth::user();

        $validated = $this->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('workspaces', 'name')->where('owner_id', $user->id),
            ],
        ]);

        $workspace = Workspace::create([
            'name' => $validated['name'],
            'owner_id' => $user->id,
        ]);

        $workspace->members()->syncWithoutDetaching([
            $user->id => [
                'role' => Workspace::ROLE_OWNER,
                'status' => Workspace::MEMBER_STATUS_ACTIVE,
                'suspended_at' => null,
            ],
        ]);

        $this->reset('name');
        session()->flash('status', 'Espace cree avec succes. Code d acces: '.$workspace->join_code);
    }

    public function joinWorkspace(): void
    {
        $user = Auth::user();

        $this->joinCode = Workspace::normalizeJoinCode($this->joinCode);

        $this->validate([
            'joinCode' => ['required', 'regex:'.Workspace::JOIN_CODE_PATTERN],
        ], [], [
            'joinCode' => 'code d acces',
        ]);

        $workspace = Workspace::query()
            ->where('join_code', $this->joinCode)
            ->first();

        if (! $workspace) {
            session()->flash('error', 'Aucun espace ne correspond a ce code.');

            return;
        }

        $existingMembership = $workspace->members()
            ->whereKey($user->id)
            ->first();

        if ($existingMembership) {
            if ($existingMembership->pivot->status === Workspace::MEMBER_STATUS_SUSPENDED) {
                session()->flash('error', 'Votre acces a cet espace est suspendu.');

                return;
            }

            session()->flash('error', 'Vous etes deja membre de cet espace.');

            return;
        }

        $workspace->members()->attach($user->id, [
            'role' => Workspace::ROLE_MEMBER,
            'job_title' => null,
            'status' => Workspace::MEMBER_STATUS_ACTIVE,
            'suspended_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->reset('joinCode');
        session()->flash('status', 'Espace rejoint avec succes.');
    }

    public function startEditingWorkspace(int $workspaceId): void
    {
        $workspace = Workspace::query()->findOrFail($workspaceId);
        $this->authorize('update', $workspace);

        $this->editingWorkspaceId = $workspace->id;
        $this->editWorkspaceName = $workspace->name;
        $this->editWorkspaceIconKey = $workspace->icon_key ?? Workspace::ICON_KEYS[0];
    }

    public function cancelEditingWorkspace(): void
    {
        $this->reset(['editingWorkspaceId', 'editWorkspaceName', 'editWorkspaceIconKey']);
        $this->editWorkspaceIconKey = Workspace::ICON_KEYS[0];
    }

    public function updateWorkspaceName(): void
    {
        if (! $this->editingWorkspaceId) {
            return;
        }

        $workspace = Workspace::query()->findOrFail($this->editingWorkspaceId);
        $this->authorize('update', $workspace);

        $validated = $this->validate([
            'editWorkspaceName' => [
                'required',
                'string',
                'max:255',
                Rule::unique('workspaces', 'name')
                    ->where('owner_id', $workspace->owner_id)
                    ->ignore($workspace->id),
            ],
        ], [], [
            'editWorkspaceName' => 'nom de l espace',
        ]);

        $workspace->update([
            'name' => $validated['editWorkspaceName'],
        ]);

        $this->cancelEditingWorkspace();
        session()->flash('status', 'Nom de l espace mis a jour.');
    }

    public function updateWorkspaceIcon(string $iconKey): void
    {
        validator(
            ['iconKey' => $iconKey],
            ['iconKey' => ['required', 'string', Rule::in(Workspace::ICON_KEYS)]]
        )->validate();

        $this->editWorkspaceIconKey = $iconKey;
    }

    public function saveWorkspaceSettings(): void
    {
        if (! $this->editingWorkspaceId) {
            return;
        }

        $workspace = Workspace::query()->findOrFail($this->editingWorkspaceId);
        $this->authorize('update', $workspace);

        $validated = $this->validate([
            'editWorkspaceName' => [
                'required',
                'string',
                'max:255',
                Rule::unique('workspaces', 'name')
                    ->where('owner_id', $workspace->owner_id)
                    ->ignore($workspace->id),
            ],
            'editWorkspaceIconKey' => ['required', 'string', Rule::in(Workspace::ICON_KEYS)],
        ], [], [
            'editWorkspaceName' => 'nom de l espace',
            'editWorkspaceIconKey' => 'icone de l espace',
        ]);

        $workspace->update([
            'name' => $validated['editWorkspaceName'],
            'icon_key' => $validated['editWorkspaceIconKey'],
        ]);

        $this->cancelEditingWorkspace();
        session()->flash('status', 'Espace mis a jour.');
    }

    public function togglePinned(int $workspaceId): void
    {
        $user = Auth::user();

        $workspace = $user->workspaces()
            ->whereKey($workspaceId)
            ->firstOrFail();

        $isPinned = ! (bool) $workspace->pivot->is_pinned;

        $user->workspaces()->updateExistingPivot($workspaceId, [
            'is_pinned' => $isPinned,
        ]);

        session()->flash('status', $isPinned ? 'Espace epingle.' : 'Espace desepingle.');
    }

    public function render()
    {
        $user = Auth::user();

        $workspaces = $user
            ->workspaces()
            ->with('owner:id,name')
            ->withCount(['projects', 'members'])
            ->orderByDesc('workspace_user.is_pinned')
            ->orderBy('name')
            ->get();

        return view('livewire.workspaces.index', [
            'workspaces' => $workspaces,
        ]);
    }
}
