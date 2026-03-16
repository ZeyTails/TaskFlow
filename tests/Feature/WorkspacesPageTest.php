<?php

namespace Tests\Feature;

use App\Livewire\Workspaces\Index as WorkspacesIndex;
use App\Models\Project;
use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceInvitation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

class WorkspacesPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_open_workspaces_page(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('workspaces.index'))
            ->assertOk();
    }

    public function test_workspace_member_can_open_projects_page(): void
    {
        $user = User::factory()->create();

        $workspace = Workspace::factory()->create([
            'owner_id' => $user->id,
        ]);

        $workspace->members()->attach($user->id, [
            'role' => Workspace::ROLE_OWNER,
            'status' => Workspace::MEMBER_STATUS_ACTIVE,
        ]);

        $this->actingAs($user)
            ->get(route('workspaces.projects.index', $workspace))
            ->assertOk();
    }

    public function test_non_member_cannot_open_projects_page(): void
    {
        $owner = User::factory()->create();
        $outsider = User::factory()->create();

        $workspace = Workspace::factory()->create([
            'owner_id' => $owner->id,
        ]);

        $workspace->members()->attach($owner->id, [
            'role' => Workspace::ROLE_OWNER,
            'status' => Workspace::MEMBER_STATUS_ACTIVE,
        ]);

        $this->actingAs($outsider)
            ->get(route('workspaces.projects.index', $workspace))
            ->assertForbidden();
    }

    public function test_owner_cannot_create_two_workspaces_with_same_name(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        Livewire::test(WorkspacesIndex::class)
            ->set('name', 'Equipe Produit')
            ->call('createWorkspace')
            ->assertHasNoErrors();

        Livewire::test(WorkspacesIndex::class)
            ->set('name', 'Equipe Produit')
            ->call('createWorkspace')
            ->assertHasErrors(['name']);
    }

    public function test_workspace_creation_generates_join_code_in_expected_format(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        Livewire::test(WorkspacesIndex::class)
            ->set('name', 'Equipe Produit')
            ->call('createWorkspace')
            ->assertHasNoErrors()
            ->assertSee('Code d acces:');

        $workspace = Workspace::query()->where('owner_id', $user->id)->firstOrFail();

        $this->assertMatchesRegularExpression(Workspace::JOIN_CODE_PATTERN, $workspace->join_code);
    }

    public function test_user_can_join_workspace_with_valid_code(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();

        $workspace = Workspace::factory()->create([
            'owner_id' => $owner->id,
        ]);

        $workspace->members()->attach($owner->id, [
            'role' => Workspace::ROLE_OWNER,
            'status' => Workspace::MEMBER_STATUS_ACTIVE,
        ]);

        $this->actingAs($member);

        Livewire::test(WorkspacesIndex::class)
            ->set('joinCode', strtolower(str_replace('-', '', $workspace->join_code)))
            ->call('joinWorkspace')
            ->assertHasNoErrors()
            ->assertSee('Espace rejoint avec succes.');

        $this->assertDatabaseHas('workspace_user', [
            'workspace_id' => $workspace->id,
            'user_id' => $member->id,
            'role' => Workspace::ROLE_MEMBER,
            'status' => Workspace::MEMBER_STATUS_ACTIVE,
        ]);
    }

    public function test_user_cannot_join_workspace_with_unknown_code(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        Livewire::test(WorkspacesIndex::class)
            ->set('joinCode', 'ABC-123-XYZ')
            ->call('joinWorkspace')
            ->assertHasNoErrors()
            ->assertSee('Aucun espace ne correspond a ce code.');
    }

    public function test_user_cannot_join_workspace_twice_with_same_code(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();

        $workspace = Workspace::factory()->create([
            'owner_id' => $owner->id,
        ]);

        $workspace->members()->attach([
            $owner->id => [
                'role' => Workspace::ROLE_OWNER,
                'status' => Workspace::MEMBER_STATUS_ACTIVE,
            ],
            $member->id => [
                'role' => Workspace::ROLE_MEMBER,
                'status' => Workspace::MEMBER_STATUS_ACTIVE,
            ],
        ]);

        $this->actingAs($member);

        Livewire::test(WorkspacesIndex::class)
            ->set('joinCode', $workspace->join_code)
            ->call('joinWorkspace')
            ->assertHasNoErrors()
            ->assertSee('Vous etes deja membre de cet espace.');

        $membershipsCount = DB::table('workspace_user')
            ->where('workspace_id', $workspace->id)
            ->where('user_id', $member->id)
            ->count();

        $this->assertSame(1, $membershipsCount);
    }

    public function test_owner_can_rename_workspace(): void
    {
        $owner = User::factory()->create();

        $workspace = Workspace::factory()->create([
            'owner_id' => $owner->id,
            'name' => 'Equipe Produit',
        ]);

        $workspace->members()->attach($owner->id, [
            'role' => Workspace::ROLE_OWNER,
            'status' => Workspace::MEMBER_STATUS_ACTIVE,
        ]);

        $this->actingAs($owner);

        Livewire::test(WorkspacesIndex::class)
            ->call('startEditingWorkspace', $workspace->id)
            ->set('editWorkspaceName', 'Equipe Design')
            ->call('updateWorkspaceName')
            ->assertHasNoErrors()
            ->assertSee('Nom de l espace mis a jour.');

        $this->assertDatabaseHas('workspaces', [
            'id' => $workspace->id,
            'name' => 'Equipe Design',
        ]);
    }

    public function test_non_owner_cannot_rename_workspace(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();

        $workspace = Workspace::factory()->create([
            'owner_id' => $owner->id,
            'name' => 'Equipe Produit',
        ]);

        $workspace->members()->attach([
            $owner->id => [
                'role' => Workspace::ROLE_OWNER,
                'status' => Workspace::MEMBER_STATUS_ACTIVE,
            ],
            $member->id => [
                'role' => Workspace::ROLE_MEMBER,
                'status' => Workspace::MEMBER_STATUS_ACTIVE,
            ],
        ]);

        Livewire::actingAs($member)
            ->test(WorkspacesIndex::class)
            ->call('startEditingWorkspace', $workspace->id)
            ->assertForbidden();
    }

    public function test_different_owners_can_use_same_workspace_name(): void
    {
        $owner = User::factory()->create();
        $otherOwner = User::factory()->create();

        Workspace::factory()->create([
            'owner_id' => $owner->id,
            'name' => 'Equipe Produit',
        ]);

        $this->actingAs($otherOwner);

        Livewire::test(WorkspacesIndex::class)
            ->set('name', 'Equipe Produit')
            ->call('createWorkspace')
            ->assertHasNoErrors();
    }

    public function test_user_can_pin_workspace_and_pinned_items_are_sorted_first(): void
    {
        $user = User::factory()->create();

        $alphaWorkspace = Workspace::factory()->create([
            'owner_id' => $user->id,
            'name' => 'Alpha Studio',
        ]);

        $zetaWorkspace = Workspace::factory()->create([
            'owner_id' => $user->id,
            'name' => 'Zeta Ops',
        ]);

        $alphaWorkspace->members()->attach($user->id, [
            'role' => Workspace::ROLE_OWNER,
            'status' => Workspace::MEMBER_STATUS_ACTIVE,
        ]);

        $zetaWorkspace->members()->attach($user->id, [
            'role' => Workspace::ROLE_OWNER,
            'status' => Workspace::MEMBER_STATUS_ACTIVE,
        ]);

        Livewire::actingAs($user)
            ->test(WorkspacesIndex::class)
            ->call('togglePinned', $zetaWorkspace->id);

        $this->assertDatabaseHas('workspace_user', [
            'workspace_id' => $zetaWorkspace->id,
            'user_id' => $user->id,
            'is_pinned' => true,
        ]);

        $this->actingAs($user)
            ->get(route('workspaces.index'))
            ->assertSeeInOrder(['Zeta Ops', 'Alpha Studio']);
    }

    public function test_workspaces_page_shows_summary_metrics(): void
    {
        $user = User::factory()->create([
            'email' => 'owner@example.test',
        ]);

        $workspaceOne = Workspace::factory()->create([
            'owner_id' => $user->id,
            'name' => 'Produit',
        ]);

        $workspaceTwo = Workspace::factory()->create([
            'owner_id' => $user->id,
            'name' => 'Design',
        ]);

        $workspaceOne->members()->attach($user->id, [
            'role' => Workspace::ROLE_OWNER,
            'status' => Workspace::MEMBER_STATUS_ACTIVE,
            'is_pinned' => true,
        ]);

        $workspaceTwo->members()->attach($user->id, [
            'role' => Workspace::ROLE_OWNER,
            'status' => Workspace::MEMBER_STATUS_ACTIVE,
        ]);

        Project::factory()->count(2)->create([
            'workspace_id' => $workspaceOne->id,
        ]);

        Project::factory()->create([
            'workspace_id' => $workspaceTwo->id,
        ]);

        WorkspaceInvitation::query()->create([
            'workspace_id' => $workspaceOne->id,
            'email' => $user->email,
            'role' => Workspace::ROLE_MEMBER,
            'job_title' => null,
            'token' => 'invitation-token-1',
            'expires_at' => now()->addDays(7),
            'invited_by' => $user->id,
            'last_sent_at' => now(),
            'reminders_count' => 0,
        ]);

        $this->actingAs($user)
            ->get(route('workspaces.index'))
            ->assertSee('Espaces')
            ->assertSee('2')
            ->assertSee('Projets')
            ->assertSee('3')
            ->assertSee('Epingles')
            ->assertSee('1')
            ->assertSee('Invitations');
    }
}
