<?php

namespace App\Livewire\Workspaces;

use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Membres workspace')]
class Members extends Component
{
    use AuthorizesRequests;

    public Workspace $workspace;

    public string $search = '';

    public string $filterRole = 'all';

    public string $filterStatus = 'all';

    public string $inviteEmail = '';

    public string $inviteRole = Workspace::ROLE_MEMBER;

    public ?string $inviteJobTitle = null;

    public int $inviteExpiresInDays = 7;

    public array $jobTitles = [];

    public array $memberRoles = [];

    public ?int $editingMemberId = null;

    public function mount(Workspace $workspace): void
    {
        $this->workspace = $workspace;
        $this->authorize('view', $this->workspace);
    }

    public function addMember(): void
    {
        $this->authorize('manageMembers', $this->workspace);
        session()->flash('error', 'Ajout direct desactive. Envoyez une invitation puis attendez son acceptation.');
    }

    public function createInvitation(): void
    {
        $this->authorize('manageMembers', $this->workspace);

        $validated = $this->validate([
            'inviteEmail' => ['required', 'email'],
            'inviteRole' => ['required', Rule::in($this->assignableRoles())],
            'inviteJobTitle' => ['nullable', 'string', 'max:120'],
            'inviteExpiresInDays' => ['required', 'integer', 'min:1', 'max:30'],
        ]);

        $email = strtolower(trim($validated['inviteEmail']));

        if ($this->workspace->members()->where('users.email', $email)->exists()) {
            session()->flash('error', 'Cette adresse email appartient deja a un membre.');

            return;
        }

        $pendingInvitation = $this->workspace->invitations()
            ->pending()
            ->where('email', $email)
            ->first();

        if ($pendingInvitation) {
            $pendingInvitation->update([
                'role' => $validated['inviteRole'],
                'job_title' => $this->normalizeJobTitle($validated['inviteJobTitle'] ?? null),
                'expires_at' => now()->addDays($validated['inviteExpiresInDays']),
                'token' => Str::random(48),
                'last_sent_at' => now(),
                'reminders_count' => $pendingInvitation->reminders_count + 1,
            ]);
        } else {
            $this->workspace->invitations()->create([
                'email' => $email,
                'role' => $validated['inviteRole'],
                'job_title' => $this->normalizeJobTitle($validated['inviteJobTitle'] ?? null),
                'token' => Str::random(48),
                'expires_at' => now()->addDays($validated['inviteExpiresInDays']),
                'invited_by' => Auth::id(),
                'last_sent_at' => now(),
            ]);
        }

        $this->inviteEmail = '';
        $this->inviteRole = Workspace::ROLE_MEMBER;
        $this->inviteJobTitle = null;
        $this->inviteExpiresInDays = 7;

        session()->flash('status', 'Invitation enregistree et envoyee.');
    }

    public function resendInvitation(int $invitationId): void
    {
        $this->authorize('manageMembers', $this->workspace);

        $invitation = $this->workspace->invitations()
            ->pending()
            ->find($invitationId);

        if (! $invitation) {
            session()->flash('error', 'Invitation introuvable.');

            return;
        }

        $invitation->update([
            'last_sent_at' => now(),
            'reminders_count' => $invitation->reminders_count + 1,
            'expires_at' => now()->addDays(7),
            'token' => Str::random(48),
        ]);

        session()->flash('status', 'Relance envoyee.');
    }

    public function cancelInvitation(int $invitationId): void
    {
        $this->authorize('manageMembers', $this->workspace);

        $invitation = $this->workspace->invitations()
            ->pending()
            ->find($invitationId);

        if (! $invitation) {
            session()->flash('error', 'Invitation introuvable.');

            return;
        }

        $invitation->update([
            'cancelled_at' => now(),
        ]);

        session()->flash('status', 'Invitation annulee.');
    }

    public function startEditing(int $userId): void
    {
        $this->authorize('manageMembers', $this->workspace);

        if ($userId === $this->workspace->owner_id) {
            session()->flash('error', 'Le proprietaire ne peut pas etre modifie ici.');

            return;
        }

        $member = $this->workspace->members()
            ->whereKey($userId)
            ->select('users.id')
            ->first();

        if (! $member) {
            session()->flash('error', 'Membre introuvable.');

            return;
        }

        $this->memberRoles[$userId] = (string) $member->pivot->role;
        $this->jobTitles[$userId] = (string) ($member->pivot->job_title ?? '');
        $this->editingMemberId = $userId;
    }

    public function cancelEditing(): void
    {
        $this->editingMemberId = null;
    }

    public function saveMemberChanges(int $userId): void
    {
        $this->authorize('manageMembers', $this->workspace);

        if ($this->editingMemberId !== $userId) {
            session()->flash('error', 'Activez d abord le mode modifier pour ce membre.');

            return;
        }

        if ($userId === $this->workspace->owner_id) {
            session()->flash('error', 'Le proprietaire ne peut pas etre modifie ici.');

            return;
        }

        if (! $this->workspace->members()->whereKey($userId)->exists()) {
            session()->flash('error', 'Membre introuvable.');

            return;
        }

        $this->validate([
            "memberRoles.$userId" => ['required', Rule::in($this->assignableRoles())],
            "jobTitles.$userId" => ['nullable', 'string', 'max:120'],
        ]);

        $this->workspace->members()->updateExistingPivot($userId, [
            'role' => $this->memberRoles[$userId],
            'job_title' => $this->normalizeJobTitle($this->jobTitles[$userId] ?? null),
        ]);

        $this->editingMemberId = null;
        session()->flash('status', 'Membre mis a jour avec succes.');
    }

    public function suspendMember(int $userId): void
    {
        $this->authorize('manageMembers', $this->workspace);

        if ($userId === $this->workspace->owner_id) {
            session()->flash('error', 'Le proprietaire ne peut pas etre suspendu.');

            return;
        }

        if (! $this->workspace->members()->whereKey($userId)->exists()) {
            session()->flash('error', 'Membre introuvable.');

            return;
        }

        $this->workspace->members()->updateExistingPivot($userId, [
            'status' => Workspace::MEMBER_STATUS_SUSPENDED,
            'suspended_at' => now(),
        ]);

        if ($this->editingMemberId === $userId) {
            $this->editingMemberId = null;
        }

        session()->flash('status', 'Membre suspendu.');
    }

    public function activateMember(int $userId): void
    {
        $this->authorize('manageMembers', $this->workspace);

        if (! $this->workspace->members()->whereKey($userId)->exists()) {
            session()->flash('error', 'Membre introuvable.');

            return;
        }

        $this->workspace->members()->updateExistingPivot($userId, [
            'status' => Workspace::MEMBER_STATUS_ACTIVE,
            'suspended_at' => null,
        ]);

        session()->flash('status', 'Membre reactive.');
    }

    public function removeMember(int $userId): void
    {
        $this->authorize('manageMembers', $this->workspace);

        if ($userId === $this->workspace->owner_id) {
            session()->flash('error', 'Le proprietaire du workspace ne peut pas etre retire.');

            return;
        }

        if (! $this->workspace->members()->whereKey($userId)->exists()) {
            session()->flash('error', 'Membre introuvable.');

            return;
        }

        $this->workspace->members()->detach($userId);

        if ($this->editingMemberId === $userId) {
            $this->editingMemberId = null;
        }

        session()->flash('status', 'Membre retire avec succes.');
    }

    public function transferOwnership(int $userId): void
    {
        $this->authorize('manageMembers', $this->workspace);

        if ($userId === $this->workspace->owner_id) {
            session()->flash('error', 'Cet utilisateur est deja proprietaire.');

            return;
        }

        if (! $this->workspace->members()->whereKey($userId)->exists()) {
            session()->flash('error', 'Membre introuvable.');

            return;
        }

        $oldOwnerId = $this->workspace->owner_id;

        DB::transaction(function () use ($userId, $oldOwnerId): void {
            $this->workspace->update([
                'owner_id' => $userId,
            ]);

            $this->workspace->members()->updateExistingPivot($userId, [
                'role' => Workspace::ROLE_OWNER,
                'status' => Workspace::MEMBER_STATUS_ACTIVE,
                'suspended_at' => null,
            ]);

            $this->workspace->members()->updateExistingPivot($oldOwnerId, [
                'role' => Workspace::ROLE_MEMBER,
                'status' => Workspace::MEMBER_STATUS_ACTIVE,
                'suspended_at' => null,
            ]);
        });

        $this->editingMemberId = null;

        session()->flash('status', 'Ownership transfere avec succes.');
    }

    public function render()
    {
        $membersQuery = $this->workspace->members()
            ->select('users.id', 'users.name', 'users.email', 'users.avatar_path')
            ->when($this->search !== '', function ($query): void {
                $query->where(function ($subQuery): void {
                    $subQuery
                        ->where('users.name', 'like', '%'.$this->search.'%')
                        ->orWhere('users.email', 'like', '%'.$this->search.'%');
                });
            })
            ->when(in_array($this->filterRole, Workspace::ROLES, true), fn ($query) => $query->wherePivot('role', $this->filterRole))
            ->when(
                in_array($this->filterStatus, Workspace::MEMBER_STATUSES, true),
                fn ($query) => $query->wherePivot('status', $this->filterStatus)
            );

        $members = $this->filterStatus === 'invited'
            ? collect()
            : $membersQuery->get()
                ->sortBy(function (User $member): string {
                    $roleWeights = [
                        Workspace::ROLE_OWNER => 0,
                        Workspace::ROLE_MEMBER => 1,
                        Workspace::ROLE_VIEWER => 2,
                    ];
                    $statusWeights = [
                        Workspace::MEMBER_STATUS_ACTIVE => 0,
                        Workspace::MEMBER_STATUS_SUSPENDED => 1,
                    ];

                    $weight = $roleWeights[$member->pivot->role] ?? 9;
                    $statusWeight = $statusWeights[$member->pivot->status] ?? 9;

                    return sprintf('%d-%d-%s', $statusWeight, $weight, strtolower($member->name));
                })
                ->values();

        foreach ($members as $member) {
            if (! array_key_exists($member->id, $this->jobTitles)) {
                $this->jobTitles[$member->id] = (string) ($member->pivot->job_title ?? '');
            }

            if (! array_key_exists($member->id, $this->memberRoles)) {
                $this->memberRoles[$member->id] = (string) $member->pivot->role;
            }
        }

        $canManageMembers = Auth::user()->can('manageMembers', $this->workspace);

        $invitations = $canManageMembers && in_array($this->filterStatus, ['all', 'invited'], true)
            ? $this->workspace->invitations()
                ->pending()
                ->with('inviter:id,name,email')
                ->when($this->search !== '', fn ($query) => $query->where('email', 'like', '%'.$this->search.'%'))
                ->when(in_array($this->filterRole, Workspace::ROLES, true), fn ($query) => $query->where('role', $this->filterRole))
                ->orderBy('expires_at')
                ->get()
            : collect();

        return view('livewire.workspaces.members', [
            'members' => $members,
            'invitations' => $invitations,
            'canManageMembers' => $canManageMembers,
        ]);
    }

    private function assignableRoles(): array
    {
        return [
            Workspace::ROLE_MEMBER,
            Workspace::ROLE_VIEWER,
        ];
    }

    private function normalizeJobTitle(?string $jobTitle): ?string
    {
        $value = trim((string) $jobTitle);

        return $value === '' ? null : $value;
    }
}
