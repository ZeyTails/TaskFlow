<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskUniquenessTest extends TestCase
{
    use RefreshDatabase;

    public function test_project_cannot_have_two_tasks_with_same_title(): void
    {
        $user = User::factory()->create();
        $project = Project::factory()->create();

        Task::create([
            'project_id' => $project->id,
            'title' => 'Revue API',
            'description' => null,
            'status' => Task::STATUS_TODO,
            'priority' => Task::PRIORITY_MEDIUM,
            'due_date' => null,
            'assignee_id' => $user->id,
            'created_by' => $user->id,
        ]);

        $this->expectException(QueryException::class);

        Task::create([
            'project_id' => $project->id,
            'title' => 'Revue API',
            'description' => null,
            'status' => Task::STATUS_TODO,
            'priority' => Task::PRIORITY_MEDIUM,
            'due_date' => null,
            'assignee_id' => $user->id,
            'created_by' => $user->id,
        ]);
    }

    public function test_different_projects_can_use_same_task_title(): void
    {
        $user = User::factory()->create();
        $project = Project::factory()->create();
        $otherProject = Project::factory()->create();

        Task::create([
            'project_id' => $project->id,
            'title' => 'Revue API',
            'description' => null,
            'status' => Task::STATUS_TODO,
            'priority' => Task::PRIORITY_MEDIUM,
            'due_date' => null,
            'assignee_id' => $user->id,
            'created_by' => $user->id,
        ]);

        Task::create([
            'project_id' => $otherProject->id,
            'title' => 'Revue API',
            'description' => null,
            'status' => Task::STATUS_TODO,
            'priority' => Task::PRIORITY_MEDIUM,
            'due_date' => null,
            'assignee_id' => $user->id,
            'created_by' => $user->id,
        ]);

        $this->assertDatabaseCount('tasks', 2);
    }
}
