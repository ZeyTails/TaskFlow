<?php

namespace Database\Seeders;

use App\Models\Project;
use App\Models\Task;
use App\Models\TaskComment;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TaskFlowDemoSeeder extends Seeder
{
    /**
     * Seed demo data for TaskFlow MVP.
     */
    public function run(): void
    {
        $owner = User::factory()->create([
            'first_name' => 'Owner',
            'last_name' => 'User',
            'name' => 'Owner User',
            'email' => 'owner@taskflow.test',
            'password' => Hash::make('password'),
        ]);

        $member = User::factory()->create([
            'first_name' => 'Member',
            'last_name' => 'User',
            'name' => 'Member User',
            'email' => 'member@taskflow.test',
            'password' => Hash::make('password'),
        ]);

        $viewer = User::factory()->create([
            'first_name' => 'Viewer',
            'last_name' => 'User',
            'name' => 'Viewer User',
            'email' => 'viewer@taskflow.test',
            'password' => Hash::make('password'),
        ]);

        $workspace = Workspace::create([
            'name' => 'Groupe Info',
            'owner_id' => $owner->id,
        ]);

        $workspace->members()->attach([
            $owner->id => ['role' => Workspace::ROLE_OWNER],
            $member->id => ['role' => Workspace::ROLE_MEMBER],
            $viewer->id => ['role' => Workspace::ROLE_VIEWER],
        ]);

        $frontendProject = Project::create([
            'workspace_id' => $workspace->id,
            'name' => 'Frontend',
            'description' => 'User interface and UX implementation.',
        ]);

        $backendProject = Project::create([
            'workspace_id' => $workspace->id,
            'name' => 'Backend',
            'description' => 'API, business rules and database logic.',
        ]);

        $tasks = [
            [
                'project_id' => $frontendProject->id,
                'title' => 'Create dashboard layout',
                'description' => 'Set up sidebar, topbar and widgets placeholders.',
                'status' => Task::STATUS_IN_PROGRESS,
                'priority' => Task::PRIORITY_HIGH,
                'due_date' => now()->addDays(2)->toDateString(),
                'assignee_ids' => [$member->id],
                'created_by' => $owner->id,
            ],
            [
                'project_id' => $frontendProject->id,
                'title' => 'Implement task list filters',
                'description' => 'Filter by status, priority and assignee.',
                'status' => Task::STATUS_TODO,
                'priority' => Task::PRIORITY_MEDIUM,
                'due_date' => now()->addDays(5)->toDateString(),
                'assignee_ids' => [$member->id, $owner->id],
                'created_by' => $owner->id,
            ],
            [
                'project_id' => $frontendProject->id,
                'title' => 'Polish responsive UI',
                'description' => 'Improve mobile breakpoints and spacing.',
                'status' => Task::STATUS_DONE,
                'priority' => Task::PRIORITY_LOW,
                'due_date' => now()->subDays(1)->toDateString(),
                'assignee_ids' => [$owner->id],
                'created_by' => $member->id,
            ],
            [
                'project_id' => $backendProject->id,
                'title' => 'Create workspace policies',
                'description' => 'Enforce owner/member/viewer permissions.',
                'status' => Task::STATUS_IN_PROGRESS,
                'priority' => Task::PRIORITY_HIGH,
                'due_date' => now()->addDays(1)->toDateString(),
                'assignee_ids' => [$owner->id, $member->id],
                'created_by' => $owner->id,
            ],
            [
                'project_id' => $backendProject->id,
                'title' => 'Implement task CRUD endpoints',
                'description' => 'Create, update and delete tasks with validation.',
                'status' => Task::STATUS_TODO,
                'priority' => Task::PRIORITY_HIGH,
                'due_date' => now()->addDays(4)->toDateString(),
                'assignee_ids' => [$member->id],
                'created_by' => $owner->id,
            ],
            [
                'project_id' => $backendProject->id,
                'title' => 'Write Pest policy tests',
                'description' => 'Add authorization tests for workspace and tasks.',
                'status' => Task::STATUS_TODO,
                'priority' => Task::PRIORITY_MEDIUM,
                'due_date' => now()->addDays(6)->toDateString(),
                'assignee_ids' => [$member->id],
                'created_by' => $owner->id,
            ],
            [
                'project_id' => $backendProject->id,
                'title' => 'Refactor validation rules',
                'description' => 'Move validation to Form Requests.',
                'status' => Task::STATUS_DONE,
                'priority' => Task::PRIORITY_LOW,
                'due_date' => now()->subDays(2)->toDateString(),
                'assignee_ids' => [$owner->id],
                'created_by' => $member->id,
            ],
            [
                'project_id' => $backendProject->id,
                'title' => 'Prepare demo script',
                'description' => 'Document the feature flow for presentation.',
                'status' => Task::STATUS_TODO,
                'priority' => Task::PRIORITY_MEDIUM,
                'due_date' => now()->addDays(3)->toDateString(),
                'assignee_ids' => [$viewer->id],
                'created_by' => $owner->id,
            ],
        ];

        $createdTasks = collect($tasks)->map(function (array $taskData) {
            $assigneeIds = $taskData['assignee_ids'] ?? [];
            unset($taskData['assignee_ids']);

            $task = Task::create($taskData);
            $task->syncAssignees($assigneeIds);

            return $task;
        });

        TaskComment::create([
            'task_id' => $createdTasks[0]->id,
            'user_id' => $owner->id,
            'content' => 'Great start. Keep the layout modular for reuse.',
        ]);

        TaskComment::create([
            'task_id' => $createdTasks[0]->id,
            'user_id' => $member->id,
            'content' => 'Understood, I will split components this afternoon.',
        ]);

        TaskComment::create([
            'task_id' => $createdTasks[3]->id,
            'user_id' => $member->id,
            'content' => 'I can help with tests once the policy methods are ready.',
        ]);
    }
}
