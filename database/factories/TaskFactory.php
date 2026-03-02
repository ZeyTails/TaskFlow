<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Task>
 */
class TaskFactory extends Factory
{
    protected $model = Task::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'title' => fake()->sentence(4),
            'description' => fake()->optional()->paragraph(),
            'status' => fake()->randomElement(Task::STATUSES),
            'priority' => fake()->randomElement(Task::PRIORITIES),
            'due_date' => fake()->optional()->dateTimeBetween('-7 days', '+20 days')->format('Y-m-d'),
            'assignee_id' => User::factory(),
            'created_by' => User::factory(),
        ];
    }
}
