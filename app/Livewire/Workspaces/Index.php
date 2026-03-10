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
        session()->flash('status', 'Espace cree avec succes.');
    }

    public function render()
    {
        $workspaces = Auth::user()
            ->workspaces()
            ->with('owner:id,name')
            ->withCount(['projects', 'members'])
            ->orderBy('name')
            ->get();

        return view('livewire.workspaces.index', [
            'workspaces' => $workspaces,
        ]);
    }
}
