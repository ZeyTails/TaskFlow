<?php

namespace Tests\Feature\Authorization;

use App\Models\Project;
use App\Models\Task;
use App\Models\TaskComment;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class TaskCommentPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_and_member_can_create_comment_but_viewer_cannot(): void
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

        $this->assertTrue(Gate::forUser($owner)->allows('create', [TaskComment::class, $task]));
        $this->assertTrue(Gate::forUser($member)->allows('create', [TaskComment::class, $task]));
        $this->assertFalse(Gate::forUser($viewer)->allows('create', [TaskComment::class, $task]));
    }

    public function test_comment_can_be_deleted_by_author_or_workspace_owner(): void
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

        $comment = TaskComment::factory()->create([
            'task_id' => $task->id,
            'user_id' => $member->id,
        ]);

        $this->assertTrue(Gate::forUser($member)->allows('delete', $comment));
        $this->assertTrue(Gate::forUser($owner)->allows('delete', $comment));
        $this->assertFalse(Gate::forUser($viewer)->allows('delete', $comment));
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
