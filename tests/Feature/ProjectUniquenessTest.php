<?php

namespace Tests\Feature;

use App\Livewire\Projects\Index as ProjectIndex;
use App\Models\Project;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ProjectUniquenessTest extends TestCase
{
    use RefreshDatabase;

    public function test_workspace_cannot_have_two_projects_with_same_name(): void
    {
        $owner = User::factory()->create();
        $workspace = Workspace::factory()->create([
            'owner_id' => $owner->id,
        ]);

        $workspace->members()->attach($owner->id, [
            'role' => Workspace::ROLE_OWNER,
            'status' => Workspace::MEMBER_STATUS_ACTIVE,
        ]);

        $this->actingAs($owner);

        Livewire::test(ProjectIndex::class, ['workspace' => $workspace])
            ->set('name', 'Produit')
            ->set('description', null)
            ->set('priority', Project::PRIORITY_MEDIUM)
            ->set('status', Project::STATUS_ACTIVE)
            ->call('createProject')
            ->assertHasNoErrors();

        Livewire::test(ProjectIndex::class, ['workspace' => $workspace])
            ->set('name', 'Produit')
            ->set('description', null)
            ->set('priority', Project::PRIORITY_MEDIUM)
            ->set('status', Project::STATUS_ACTIVE)
            ->call('createProject')
            ->assertHasErrors(['name']);
    }

    public function test_different_workspaces_can_use_same_project_name(): void
    {
        $owner = User::factory()->create();
        $workspace = Workspace::factory()->create([
            'owner_id' => $owner->id,
        ]);
        $otherWorkspace = Workspace::factory()->create([
            'owner_id' => $owner->id,
        ]);

        $workspace->members()->attach($owner->id, [
            'role' => Workspace::ROLE_OWNER,
            'status' => Workspace::MEMBER_STATUS_ACTIVE,
        ]);
        $otherWorkspace->members()->attach($owner->id, [
            'role' => Workspace::ROLE_OWNER,
            'status' => Workspace::MEMBER_STATUS_ACTIVE,
        ]);

        $this->actingAs($owner);

        Livewire::test(ProjectIndex::class, ['workspace' => $workspace])
            ->set('name', 'Produit')
            ->set('description', null)
            ->set('priority', Project::PRIORITY_MEDIUM)
            ->set('status', Project::STATUS_ACTIVE)
            ->call('createProject')
            ->assertHasNoErrors();

        Livewire::test(ProjectIndex::class, ['workspace' => $otherWorkspace])
            ->set('name', 'Produit')
            ->set('description', null)
            ->set('priority', Project::PRIORITY_MEDIUM)
            ->set('status', Project::STATUS_ACTIVE)
            ->call('createProject')
            ->assertHasNoErrors();
    }
}
