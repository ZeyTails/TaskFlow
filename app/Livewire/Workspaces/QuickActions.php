<?php

namespace App\Livewire\Workspaces;

use App\Models\Workspace;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Component;

class QuickActions extends Component
{
    use AuthorizesRequests;

    public string $modalTab = 'create';

    public string $name = '';

    public string $joinCode = '';

    public function createWorkspace()
    {
        $this->authorize('create', Workspace::class);

        $user = Auth::user();
        $this->modalTab = 'create';

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

        return redirect()->route('workspaces.projects.index', $workspace);
    }

    public function joinWorkspace()
    {
        $user = Auth::user();
        $this->modalTab = 'join';
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
            session()->flash('quick-actions-error', 'Aucun workspace ne correspond a ce code.');
            session()->flash('quick-actions-modal', true);

            return;
        }

        $existingMembership = $workspace->members()
            ->whereKey($user->id)
            ->first();

        if ($existingMembership) {
            session()->flash(
                'quick-actions-error',
                $existingMembership->pivot->status === Workspace::MEMBER_STATUS_SUSPENDED
                    ? 'Votre acces a ce workspace est suspendu.'
                    : 'Vous etes deja membre de ce workspace.'
            );
            session()->flash('quick-actions-modal', true);

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

        return redirect()->route('workspaces.projects.index', $workspace);
    }

    public function render()
    {
        return view('livewire.workspaces.quick-actions');
    }
}
