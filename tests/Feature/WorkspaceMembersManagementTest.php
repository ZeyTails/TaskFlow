<?php

namespace Tests\Feature;

use App\Livewire\Workspaces\Members;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

class WorkspaceMembersManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_workspace_member_can_open_members_page(): void
    {
        [, $workspace] = $this->workspaceWithOwner();
        $member = User::factory()->create();

        $workspace->members()->attach($member->id, [
            'role' => Workspace::ROLE_MEMBER,
            'status' => Workspace::MEMBER_STATUS_ACTIVE,
        ]);

        $this->actingAs($member)
            ->get(route('workspaces.members.index', $workspace))
            ->assertOk();
    }

    public function test_non_member_cannot_open_members_page(): void
    {
        [, $workspace] = $this->workspaceWithOwner();
        $outsider = User::factory()->create();

        $this->actingAs($outsider)
            ->get(route('workspaces.members.index', $workspace))
            ->assertForbidden();
    }

    public function test_owner_must_invite_before_member_can_join(): void
    {
        [$owner, $workspace] = $this->workspaceWithOwner();
        $newUser = User::factory()->create();

        Livewire::actingAs($owner)
            ->test(Members::class, ['workspace' => $workspace])
            ->set('inviteEmail', $newUser->email)
            ->set('inviteRole', Workspace::ROLE_VIEWER)
            ->set('inviteJobTitle', 'Developpeur Front-end')
            ->set('inviteExpiresInDays', 7)
            ->call('createInvitation');

        $this->assertDatabaseMissing('workspace_user', [
            'workspace_id' => $workspace->id,
            'user_id' => $newUser->id,
        ]);

        $this->assertDatabaseHas('workspace_invitations', [
            'workspace_id' => $workspace->id,
            'email' => strtolower($newUser->email),
            'role' => Workspace::ROLE_VIEWER,
            'job_title' => 'Developpeur Front-end',
        ]);
    }

    public function test_owner_is_told_when_invited_email_has_no_account(): void
    {
        [$owner, $workspace] = $this->workspaceWithOwner();

        Livewire::actingAs($owner)
            ->test(Members::class, ['workspace' => $workspace])
            ->set('inviteEmail', 'nouveau@example.com')
            ->set('inviteRole', Workspace::ROLE_MEMBER)
            ->set('inviteExpiresInDays', 7)
            ->call('createInvitation')
            ->assertSee('Aucun compte n existe encore pour cette adresse email.');

        $this->assertDatabaseHas('workspace_invitations', [
            'workspace_id' => $workspace->id,
            'email' => 'nouveau@example.com',
            'role' => Workspace::ROLE_MEMBER,
        ]);
    }

    public function test_owner_can_update_member_role_and_remove_member(): void
    {
        [$owner, $workspace] = $this->workspaceWithOwner();
        $member = User::factory()->create();

        $workspace->members()->attach($member->id, [
            'role' => Workspace::ROLE_VIEWER,
            'status' => Workspace::MEMBER_STATUS_ACTIVE,
        ]);

        Livewire::actingAs($owner)
            ->test(Members::class, ['workspace' => $workspace])
            ->call('startEditing', $member->id)
            ->set("memberRoles.{$member->id}", Workspace::ROLE_MEMBER)
            ->call('saveMemberChanges', $member->id);

        $this->assertDatabaseHas('workspace_user', [
            'workspace_id' => $workspace->id,
            'user_id' => $member->id,
            'role' => Workspace::ROLE_MEMBER,
        ]);

        Livewire::actingAs($owner)
            ->test(Members::class, ['workspace' => $workspace])
            ->call('removeMember', $member->id);

        $this->assertDatabaseMissing('workspace_user', [
            'workspace_id' => $workspace->id,
            'user_id' => $member->id,
        ]);
    }

    public function test_owner_can_update_own_job_title_but_cannot_remove_workspace_owner(): void
    {
        [$owner, $workspace] = $this->workspaceWithOwner();

        Livewire::actingAs($owner)
            ->test(Members::class, ['workspace' => $workspace])
            ->call('startEditing', $owner->id)
            ->set("jobTitles.{$owner->id}", 'Responsable produit')
            ->call('saveMemberChanges', $owner->id)
            ->call('removeMember', $owner->id);

        $this->assertDatabaseHas('workspace_user', [
            'workspace_id' => $workspace->id,
            'user_id' => $owner->id,
            'role' => Workspace::ROLE_OWNER,
            'job_title' => 'Responsable produit',
        ]);
    }

    public function test_owner_can_update_member_job_title(): void
    {
        [$owner, $workspace] = $this->workspaceWithOwner();
        $member = User::factory()->create();

        $workspace->members()->attach($member->id, [
            'role' => Workspace::ROLE_MEMBER,
            'job_title' => null,
            'status' => Workspace::MEMBER_STATUS_ACTIVE,
        ]);

        Livewire::actingAs($owner)
            ->test(Members::class, ['workspace' => $workspace])
            ->call('startEditing', $member->id)
            ->set("jobTitles.{$member->id}", 'Product Designer')
            ->call('saveMemberChanges', $member->id);

        $this->assertDatabaseHas('workspace_user', [
            'workspace_id' => $workspace->id,
            'user_id' => $member->id,
            'job_title' => 'Product Designer',
        ]);
    }

    public function test_member_cannot_update_job_title(): void
    {
        [$owner, $workspace] = $this->workspaceWithOwner();
        $member = User::factory()->create();

        $workspace->members()->attach($member->id, [
            'role' => Workspace::ROLE_MEMBER,
            'job_title' => null,
            'status' => Workspace::MEMBER_STATUS_ACTIVE,
        ]);

        Livewire::actingAs($member)
            ->test(Members::class, ['workspace' => $workspace])
            ->call('startEditing', $owner->id)
            ->assertForbidden();
    }

    public function test_owner_cannot_save_without_modifier_mode(): void
    {
        [$owner, $workspace] = $this->workspaceWithOwner();
        $member = User::factory()->create();

        $workspace->members()->attach($member->id, [
            'role' => Workspace::ROLE_MEMBER,
            'job_title' => null,
            'status' => Workspace::MEMBER_STATUS_ACTIVE,
        ]);

        Livewire::actingAs($owner)
            ->test(Members::class, ['workspace' => $workspace])
            ->set("jobTitles.{$member->id}", 'Tech Lead')
            ->set("memberRoles.{$member->id}", Workspace::ROLE_VIEWER)
            ->call('saveMemberChanges', $member->id);

        $this->assertDatabaseHas('workspace_user', [
            'workspace_id' => $workspace->id,
            'user_id' => $member->id,
            'role' => Workspace::ROLE_MEMBER,
            'job_title' => null,
        ]);

        Livewire::actingAs($owner)
            ->test(Members::class, ['workspace' => $workspace])
            ->call('startEditing', $member->id)
            ->set("jobTitles.{$member->id}", 'Tech Lead')
            ->set("memberRoles.{$member->id}", Workspace::ROLE_VIEWER)
            ->call('saveMemberChanges', $member->id);

        $this->assertDatabaseHas('workspace_user', [
            'workspace_id' => $workspace->id,
            'user_id' => $member->id,
            'role' => Workspace::ROLE_VIEWER,
            'job_title' => 'Tech Lead',
        ]);
    }

    public function test_member_cannot_remove_other_members(): void
    {
        [$owner, $workspace] = $this->workspaceWithOwner();
        $member = User::factory()->create();
        $viewer = User::factory()->create();

        $workspace->members()->attach($member->id, [
            'role' => Workspace::ROLE_MEMBER,
            'status' => Workspace::MEMBER_STATUS_ACTIVE,
        ]);
        $workspace->members()->attach($viewer->id, [
            'role' => Workspace::ROLE_VIEWER,
            'status' => Workspace::MEMBER_STATUS_ACTIVE,
        ]);

        Livewire::actingAs($member)
            ->test(Members::class, ['workspace' => $workspace])
            ->call('removeMember', $viewer->id)
            ->assertForbidden();
    }

    public function test_owner_can_suspend_and_reactivate_member(): void
    {
        [$owner, $workspace] = $this->workspaceWithOwner();
        $member = User::factory()->create();

        $workspace->members()->attach($member->id, [
            'role' => Workspace::ROLE_MEMBER,
            'status' => Workspace::MEMBER_STATUS_ACTIVE,
        ]);

        Livewire::actingAs($owner)
            ->test(Members::class, ['workspace' => $workspace])
            ->call('suspendMember', $member->id);

        $this->assertDatabaseHas('workspace_user', [
            'workspace_id' => $workspace->id,
            'user_id' => $member->id,
            'status' => Workspace::MEMBER_STATUS_SUSPENDED,
        ]);

        Livewire::actingAs($owner)
            ->test(Members::class, ['workspace' => $workspace])
            ->call('activateMember', $member->id);

        $this->assertDatabaseHas('workspace_user', [
            'workspace_id' => $workspace->id,
            'user_id' => $member->id,
            'status' => Workspace::MEMBER_STATUS_ACTIVE,
        ]);
    }

    public function test_owner_can_transfer_ownership(): void
    {
        [$owner, $workspace] = $this->workspaceWithOwner();
        $newOwner = User::factory()->create();

        $workspace->members()->attach($newOwner->id, [
            'role' => Workspace::ROLE_MEMBER,
            'status' => Workspace::MEMBER_STATUS_ACTIVE,
        ]);

        Livewire::actingAs($owner)
            ->test(Members::class, ['workspace' => $workspace])
            ->call('transferOwnership', $newOwner->id);

        $this->assertDatabaseHas('workspaces', [
            'id' => $workspace->id,
            'owner_id' => $newOwner->id,
        ]);

        $this->assertDatabaseHas('workspace_user', [
            'workspace_id' => $workspace->id,
            'user_id' => $newOwner->id,
            'role' => Workspace::ROLE_OWNER,
        ]);
    }

    public function test_owner_can_create_and_resend_invitation(): void
    {
        [$owner, $workspace] = $this->workspaceWithOwner();

        Livewire::actingAs($owner)
            ->test(Members::class, ['workspace' => $workspace])
            ->set('inviteEmail', 'invite@example.com')
            ->set('inviteRole', Workspace::ROLE_VIEWER)
            ->set('inviteJobTitle', 'UX Designer')
            ->set('inviteExpiresInDays', 3)
            ->call('createInvitation');

        $this->assertDatabaseHas('workspace_invitations', [
            'workspace_id' => $workspace->id,
            'email' => 'invite@example.com',
            'role' => Workspace::ROLE_VIEWER,
            'job_title' => 'UX Designer',
        ]);

        $invitationId = (int) DB::table('workspace_invitations')
            ->where('workspace_id', $workspace->id)
            ->where('email', 'invite@example.com')
            ->value('id');

        Livewire::actingAs($owner)
            ->test(Members::class, ['workspace' => $workspace])
            ->call('resendInvitation', $invitationId);

        $this->assertDatabaseHas('workspace_invitations', [
            'id' => $invitationId,
            'reminders_count' => 1,
        ]);
    }

    private function workspaceWithOwner(): array
    {
        $owner = User::factory()->create();

        $workspace = Workspace::factory()->create([
            'owner_id' => $owner->id,
        ]);

        $workspace->members()->attach($owner->id, [
            'role' => Workspace::ROLE_OWNER,
            'status' => Workspace::MEMBER_STATUS_ACTIVE,
        ]);

        return [$owner, $workspace];
    }
}
