<?php

namespace Tests\Feature;

use App\Livewire\Tasks\Index as TasksIndex;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class TasksPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_workspace_member_can_open_tasks_page(): void
    {
        [$owner, $workspace, $project] = $this->projectWithWorkspace();

        $this->actingAs($owner)
            ->get(route('workspaces.projects.tasks.index', [$workspace, $project]))
            ->assertOk()
            ->assertSee('Avancement du projet');
    }

    public function test_non_member_cannot_open_tasks_page(): void
    {
        [, $workspace, $project] = $this->projectWithWorkspace();
        $outsider = User::factory()->create();

        $this->actingAs($outsider)
            ->get(route('workspaces.projects.tasks.index', [$workspace, $project]))
            ->assertForbidden();
    }

    public function test_member_can_create_task_with_active_workspace_assignees(): void
    {
        $owner = User::factory()->create();
        $assignee = User::factory()->create();
        $secondAssignee = User::factory()->create();

        $workspace = Workspace::factory()->create([
            'owner_id' => $owner->id,
        ]);

        $workspace->members()->attach([
            $owner->id => [
                'role' => Workspace::ROLE_OWNER,
                'status' => Workspace::MEMBER_STATUS_ACTIVE,
            ],
            $assignee->id => [
                'role' => Workspace::ROLE_MEMBER,
                'status' => Workspace::MEMBER_STATUS_ACTIVE,
            ],
            $secondAssignee->id => [
                'role' => Workspace::ROLE_MEMBER,
                'status' => Workspace::MEMBER_STATUS_ACTIVE,
            ],
        ]);

        $project = Project::factory()->create([
            'workspace_id' => $workspace->id,
        ]);

        $this->actingAs($owner);

        Livewire::test(TasksIndex::class, [
            'workspace' => $workspace,
            'project' => $project,
        ])
            ->set('title', 'Configurer le flux')
            ->set('assigneeIds', [$assignee->id, $secondAssignee->id])
            ->call('createTask')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('tasks', [
            'project_id' => $project->id,
            'title' => 'Configurer le flux',
            'assignee_id' => $assignee->id,
            'status' => Task::STATUS_TODO,
            'priority' => Task::PRIORITY_MEDIUM,
            'created_by' => $owner->id,
        ]);

        $task = Task::query()->where('project_id', $project->id)->where('title', 'Configurer le flux')->firstOrFail();

        $this->assertDatabaseHas('task_user', [
            'task_id' => $task->id,
            'user_id' => $assignee->id,
        ]);

        $this->assertDatabaseHas('task_user', [
            'task_id' => $task->id,
            'user_id' => $secondAssignee->id,
        ]);
    }

    public function test_viewer_cannot_create_task(): void
    {
        [, $workspace, $project, $viewer] = $this->projectWithWorkspace(Workspace::ROLE_VIEWER);

        Livewire::actingAs($viewer)
            ->test(TasksIndex::class, [
                'workspace' => $workspace,
                'project' => $project,
            ])
            ->set('title', 'Lecture seule')
            ->call('createTask')
            ->assertForbidden();
    }

    public function test_member_can_update_and_delete_task(): void
    {
        [$owner, $workspace, $project, $member] = $this->projectWithWorkspace(Workspace::ROLE_MEMBER);

        $task = Task::factory()->create([
            'project_id' => $project->id,
            'created_by' => $owner->id,
            'assignee_id' => $member->id,
        ]);

        Livewire::actingAs($member)
            ->test(TasksIndex::class, [
                'workspace' => $workspace,
                'project' => $project,
            ])
            ->call('startEditing', $task->id)
            ->set('editTitle', 'Titre modifie')
            ->set('editStatus', Task::STATUS_DONE)
            ->set('editPriority', Task::PRIORITY_HIGH)
            ->call('updateTask')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'title' => 'Titre modifie',
            'status' => Task::STATUS_DONE,
            'priority' => Task::PRIORITY_HIGH,
        ]);

        Livewire::actingAs($member)
            ->test(TasksIndex::class, [
                'workspace' => $workspace,
                'project' => $project,
            ])
            ->call('deleteTask', $task->id);

        $this->assertDatabaseMissing('tasks', [
            'id' => $task->id,
        ]);
    }

    public function test_assignee_must_be_active_workspace_member(): void
    {
        $owner = User::factory()->create();
        $suspended = User::factory()->create();
        $outsider = User::factory()->create();

        $workspace = Workspace::factory()->create([
            'owner_id' => $owner->id,
        ]);

        $workspace->members()->attach([
            $owner->id => [
                'role' => Workspace::ROLE_OWNER,
                'status' => Workspace::MEMBER_STATUS_ACTIVE,
            ],
            $suspended->id => [
                'role' => Workspace::ROLE_MEMBER,
                'status' => Workspace::MEMBER_STATUS_SUSPENDED,
            ],
        ]);

        $project = Project::factory()->create([
            'workspace_id' => $workspace->id,
        ]);

        Livewire::actingAs($owner)
            ->test(TasksIndex::class, [
            'workspace' => $workspace,
            'project' => $project,
        ])
            ->set('title', 'Tache invalide')
            ->set('assigneeIds', [$suspended->id])
            ->call('createTask')
            ->assertHasErrors(['assigneeIds.0']);

        Livewire::actingAs($owner)
            ->test(TasksIndex::class, [
                'workspace' => $workspace,
                'project' => $project,
            ])
            ->set('title', 'Autre tache invalide')
            ->set('assigneeIds', [$outsider->id])
            ->call('createTask')
            ->assertHasErrors(['assigneeIds.0']);
    }

    private function projectWithWorkspace(string $role = Workspace::ROLE_OWNER): array
    {
        $owner = User::factory()->create();
        $user = $role === Workspace::ROLE_OWNER ? $owner : User::factory()->create();

        $workspace = Workspace::factory()->create([
            'owner_id' => $owner->id,
        ]);

        $workspace->members()->attach($owner->id, [
            'role' => Workspace::ROLE_OWNER,
            'status' => Workspace::MEMBER_STATUS_ACTIVE,
        ]);

        if ($user->id !== $owner->id) {
            $workspace->members()->attach($user->id, [
                'role' => $role,
                'status' => Workspace::MEMBER_STATUS_ACTIVE,
            ]);
        }

        $project = Project::factory()->create([
            'workspace_id' => $workspace->id,
        ]);

        return [$owner, $workspace, $project, $user];
    }
}
