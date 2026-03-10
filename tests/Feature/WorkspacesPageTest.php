<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use App\Livewire\Workspaces\Index as WorkspacesIndex;
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
}
