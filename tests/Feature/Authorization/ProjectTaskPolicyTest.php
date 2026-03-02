<?php

namespace Tests\Feature\Authorization;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class ProjectTaskPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_can_manage_projects_and_tasks_but_viewer_cannot_write(): void
    {
        [$owner, $member, $viewer, $workspace] = $this->workspaceWithMembers();

        $project = Project::factory()->create([
            'workspace_id' => $workspace->id,
        ]);

        $task = Task::factory()->create([
            'project_id' => $project->id,
            'created_by' => $owner->id,
            'assignee_id' => $member->id,
        ]);

        $this->assertTrue(Gate::forUser($member)->allows('create', [Project::class, $workspace]));
        $this->assertTrue(Gate::forUser($member)->allows('update', $project));

        $this->assertFalse(Gate::forUser($viewer)->allows('create', [Project::class, $workspace]));
        $this->assertFalse(Gate::forUser($viewer)->allows('update', $project));

        $this->assertTrue(Gate::forUser($member)->allows('create', [Task::class, $project]));
        $this->assertTrue(Gate::forUser($member)->allows('update', $task));
        $this->assertTrue(Gate::forUser($member)->allows('assign', $task));

        $this->assertFalse(Gate::forUser($viewer)->allows('create', [Task::class, $project]));
        $this->assertFalse(Gate::forUser($viewer)->allows('update', $task));
        $this->assertFalse(Gate::forUser($viewer)->allows('assign', $task));
    }

    public function test_non_member_cannot_view_task(): void
    {
        [$owner, , , $workspace] = $this->workspaceWithMembers();
        $outsider = User::factory()->create();

        $project = Project::factory()->create([
            'workspace_id' => $workspace->id,
        ]);

        $task = Task::factory()->create([
            'project_id' => $project->id,
            'created_by' => $owner->id,
            'assignee_id' => $owner->id,
        ]);

        $this->assertFalse(Gate::forUser($outsider)->allows('view', $task));
    }

    private function workspaceWithMembers(): array
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $viewer = User::factory()->create();

        $workspace = Workspace::factory()->create([
            'owner_id' => $owner->id,
        ]);

        $workspace->members()->attach([
            $owner->id => ['role' => Workspace::ROLE_OWNER],
            $member->id => ['role' => Workspace::ROLE_MEMBER],
            $viewer->id => ['role' => Workspace::ROLE_VIEWER],
        ]);

        return [$owner, $member, $viewer, $workspace];
    }
}
