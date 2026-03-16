<?php

namespace Tests\Feature;

use App\Livewire\Tasks\Index as TasksIndex;
use App\Livewire\Tasks\Show as TaskShow;
use App\Models\ActivityLog;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class TaskCollaborationTest extends TestCase
{
    use RefreshDatabase;

    public function test_task_creation_generates_activity_and_assignment_notification(): void
    {
        [$owner, $workspace, $project, $member] = $this->workspaceProjectContext();

        Livewire::actingAs($owner)
            ->test(TasksIndex::class, [
                'workspace' => $workspace,
                'project' => $project,
            ])
            ->set('title', 'Initialiser la doc')
            ->set('assigneeIds', [$member->id])
            ->call('createTask')
            ->assertHasNoErrors();

        $task = Task::query()->where('title', 'Initialiser la doc')->firstOrFail();

        $this->assertDatabaseHas('activity_logs', [
            'workspace_id' => $workspace->id,
            'project_id' => $project->id,
            'task_id' => $task->id,
            'user_id' => $owner->id,
            'type' => 'task_created',
        ]);

        $this->assertDatabaseHas('user_notifications', [
            'user_id' => $member->id,
            'workspace_id' => $workspace->id,
            'task_id' => $task->id,
            'type' => 'task_assigned',
        ]);
    }

    public function test_task_update_generates_activity_and_update_notification(): void
    {
        [$owner, $workspace, $project, $member] = $this->workspaceProjectContext();

        $task = Task::factory()->create([
            'project_id' => $project->id,
            'created_by' => $owner->id,
            'assignee_id' => $member->id,
            'status' => Task::STATUS_TODO,
            'priority' => Task::PRIORITY_MEDIUM,
        ]);

        $task->syncAssignees([$member->id]);

        Livewire::actingAs($owner)
            ->test(TaskShow::class, ['task' => $task])
            ->set('status', Task::STATUS_DONE)
            ->call('updateTask')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('activity_logs', [
            'workspace_id' => $workspace->id,
            'project_id' => $project->id,
            'task_id' => $task->id,
            'user_id' => $owner->id,
            'type' => 'task_updated',
        ]);

        $this->assertDatabaseHas('user_notifications', [
            'user_id' => $member->id,
            'workspace_id' => $workspace->id,
            'task_id' => $task->id,
            'type' => 'task_updated',
        ]);

        $activity = ActivityLog::query()
            ->where('task_id', $task->id)
            ->where('type', 'task_updated')
            ->latest()
            ->firstOrFail();

        $this->assertStringNotContainsString('titre', $activity->description);
        $this->assertStringContainsString('statut', $activity->description);
        $this->assertIsArray($activity->meta['change_set'] ?? null);
        $this->assertSame('statut', $activity->meta['change_set'][0]['label'] ?? null);
        $this->assertSame('A faire', $activity->meta['change_set'][0]['before'] ?? null);
        $this->assertSame('Terminee', $activity->meta['change_set'][0]['after'] ?? null);
    }

    public function test_adding_comment_generates_activity_log(): void
    {
        [$owner, $workspace, $project, $member] = $this->workspaceProjectContext();

        $task = Task::factory()->create([
            'project_id' => $project->id,
            'created_by' => $owner->id,
            'assignee_id' => $member->id,
        ]);

        $task->syncAssignees([$member->id]);

        Livewire::actingAs($member)
            ->test(TaskShow::class, ['task' => $task])
            ->set('commentContent', 'Mise a jour importante.')
            ->call('addComment')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('activity_logs', [
            'workspace_id' => $workspace->id,
            'project_id' => $project->id,
            'task_id' => $task->id,
            'user_id' => $member->id,
            'type' => 'comment_added',
        ]);
    }

    private function workspaceProjectContext(): array
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

        $project = Project::factory()->create([
            'workspace_id' => $workspace->id,
        ]);

        return [$owner, $workspace, $project, $member];
    }
}
