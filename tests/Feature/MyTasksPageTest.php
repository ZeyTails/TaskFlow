<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MyTasksPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_open_my_tasks_page(): void
    {
        $user = User::factory()->create();

        $workspace = Workspace::factory()->create([
            'owner_id' => $user->id,
        ]);

        $workspace->members()->attach($user->id, [
            'role' => Workspace::ROLE_OWNER,
            'status' => Workspace::MEMBER_STATUS_ACTIVE,
        ]);

        $project = Project::factory()->create([
            'workspace_id' => $workspace->id,
        ]);

        $task = Task::factory()->create([
            'project_id' => $project->id,
            'created_by' => $user->id,
        ]);

        $task->syncAssignees([$user->id]);

        $this->actingAs($user)
            ->get(route('my-tasks'))
            ->assertOk()
            ->assertSee('Mes taches')
            ->assertSee($task->title);
    }
}
