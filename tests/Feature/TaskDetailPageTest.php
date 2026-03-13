<?php

namespace Tests\Feature;

use App\Livewire\Tasks\Show as TaskShow;
use App\Models\Project;
use App\Models\Task;
use App\Models\TaskComment;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class TaskDetailPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_workspace_member_can_open_task_detail_page(): void
    {
        [, $member, , , , $task] = $this->taskContext();

        $this->actingAs($member)
            ->get(route('tasks.show', $task))
            ->assertOk()
            ->assertSee($task->title);
    }

    public function test_non_member_cannot_open_task_detail_page(): void
    {
        [, , , , , $task] = $this->taskContext();
        $outsider = User::factory()->create();

        $this->actingAs($outsider)
            ->get(route('tasks.show', $task))
            ->assertForbidden();
    }

    public function test_member_can_add_comment_from_task_detail_page(): void
    {
        [, $member, , , , $task] = $this->taskContext();

        Livewire::actingAs($member)
            ->test(TaskShow::class, ['task' => $task])
            ->set('commentContent', 'Le endpoint backend est pret.')
            ->call('addComment')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('task_comments', [
            'task_id' => $task->id,
            'user_id' => $member->id,
            'content' => 'Le endpoint backend est pret.',
        ]);
    }

    public function test_viewer_cannot_add_comment_from_task_detail_page(): void
    {
        [, , $viewer, , , $task] = $this->taskContext();

        Livewire::actingAs($viewer)
            ->test(TaskShow::class, ['task' => $task])
            ->set('commentContent', 'Je suis en lecture seule.')
            ->call('addComment')
            ->assertForbidden();
    }

    public function test_member_can_update_task_metadata_from_task_detail_page(): void
    {
        [$owner, $member, , $workspace, , $task] = $this->taskContext();
        $newAssignee = User::factory()->create();

        $workspace->members()->attach($newAssignee->id, [
            'role' => Workspace::ROLE_MEMBER,
            'status' => Workspace::MEMBER_STATUS_ACTIVE,
        ]);

        Livewire::actingAs($member)
            ->test(TaskShow::class, ['task' => $task])
            ->set('status', Task::STATUS_DONE)
            ->set('priority', Task::PRIORITY_HIGH)
            ->set('dueDate', now()->addDays(3)->format('Y-m-d'))
            ->set('assigneeId', $newAssignee->id)
            ->call('updateTask')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'status' => Task::STATUS_DONE,
            'priority' => Task::PRIORITY_HIGH,
            'assignee_id' => $newAssignee->id,
            'created_by' => $owner->id,
        ]);
    }

    private function taskContext(): array
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $viewer = User::factory()->create();

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
            $viewer->id => [
                'role' => Workspace::ROLE_VIEWER,
                'status' => Workspace::MEMBER_STATUS_ACTIVE,
            ],
        ]);

        $project = Project::factory()->create([
            'workspace_id' => $workspace->id,
        ]);

        $task = Task::factory()->create([
            'project_id' => $project->id,
            'created_by' => $owner->id,
            'assignee_id' => $member->id,
        ]);

        TaskComment::factory()->create([
            'task_id' => $task->id,
            'user_id' => $owner->id,
        ]);

        return [$owner, $member, $viewer, $workspace, $project, $task];
    }
}
