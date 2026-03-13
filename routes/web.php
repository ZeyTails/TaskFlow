<?php

use App\Livewire\Projects\Index as ProjectIndex;
use App\Livewire\Tasks\Index as TaskIndex;
use App\Livewire\Tasks\Show as TaskShow;
use App\Livewire\Workspaces\Index as WorkspaceIndex;
use App\Livewire\Workspaces\Members as WorkspaceMembers;
use App\Models\Project;
use App\Models\Task;
use App\Models\Workspace;
use App\Models\WorkspaceInvitation;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function () {
        $user = auth()->user();

        $assignedTasksCount = $user->assignedTasks()->count();
        $overdueTasksCount = $user->assignedTasks()
            ->whereNotNull('due_date')
            ->whereDate('due_date', '<', today())
            ->where('status', '!=', Task::STATUS_DONE)
            ->count();
        $dueTodayTasksCount = $user->assignedTasks()
            ->whereDate('due_date', today())
            ->where('status', '!=', Task::STATUS_DONE)
            ->count();

        $recentWorkspaces = $user->workspaces()
            ->withCount(['projects', 'members'])
            ->orderByDesc('workspace_user.created_at')
            ->limit(4)
            ->get();

        $recentProjects = Project::query()
            ->whereHas('workspace.members', fn ($query) => $query
                ->whereKey($user->id)
                ->where('workspace_user.status', Workspace::MEMBER_STATUS_ACTIVE))
            ->with(['workspace:id,name'])
            ->withCount('tasks')
            ->latest()
            ->limit(6)
            ->get();

        return view('dashboard', compact(
            'assignedTasksCount',
            'overdueTasksCount',
            'dueTodayTasksCount',
            'recentWorkspaces',
            'recentProjects'
        ));
    })->name('dashboard');

    Route::get('my-tasks', function () {
        $user = auth()->user();

        $status = request('status');
        $priority = request('priority');
        $echeance = request('echeance');

        $tasks = Task::query()
            ->where('assignee_id', $user->id)
            ->whereHas('project.workspace.members', fn ($query) => $query
                ->whereKey($user->id)
                ->where('workspace_user.status', Workspace::MEMBER_STATUS_ACTIVE))
            ->with(['project:id,name,workspace_id', 'project.workspace:id,name'])
            ->when(in_array($status, Task::STATUSES, true), fn ($query) => $query->where('status', $status))
            ->when(in_array($priority, Task::PRIORITIES, true), fn ($query) => $query->where('priority', $priority))
            ->when($echeance === 'retard', fn ($query) => $query
                ->whereNotNull('due_date')
                ->whereDate('due_date', '<', today())
                ->where('status', '!=', Task::STATUS_DONE))
            ->when($echeance === 'aujourdhui', fn ($query) => $query->whereDate('due_date', today()))
            ->when($echeance === 'semaine', fn ($query) => $query
                ->whereNotNull('due_date')
                ->whereBetween('due_date', [today(), today()->addDays(7)]))
            ->orderByRaw('due_date IS NULL, due_date ASC')
            ->latest('updated_at')
            ->paginate(12)
            ->withQueryString();

        return view('my-tasks', compact('tasks'));
    })->name('my-tasks');

    Route::get('calendar', function () {
        $user = auth()->user();

        $tasksByDate = Task::query()
            ->where('assignee_id', $user->id)
            ->whereNotNull('due_date')
            ->whereHas('project.workspace.members', fn ($query) => $query
                ->whereKey($user->id)
                ->where('workspace_user.status', Workspace::MEMBER_STATUS_ACTIVE))
            ->with(['project:id,name'])
            ->orderBy('due_date')
            ->get()
            ->groupBy(fn (Task $task) => $task->due_date?->format('Y-m-d'));

        return view('calendar', compact('tasksByDate'));
    })->name('calendar');

    Route::get('notifications', function () {
        $user = auth()->user();

        $invitations = WorkspaceInvitation::query()
            ->pending()
            ->where('email', $user->email)
            ->with('workspace:id,name', 'inviter:id,name,email')
            ->latest('created_at')
            ->get();

        return view('notifications', compact('invitations'));
    })->name('notifications');

    Route::get('workspaces', WorkspaceIndex::class)->name('workspaces.index');
    Route::get('workspaces/{workspace}/members', WorkspaceMembers::class)->name('workspaces.members.index');
    Route::get('workspaces/{workspace}/projects', ProjectIndex::class)->name('workspaces.projects.index');
    Route::get('workspaces/{workspace}/projects/{project}/tasks', TaskIndex::class)->name('workspaces.projects.tasks.index');
    Route::get('tasks/{task}', TaskShow::class)->name('tasks.show');
});

require __DIR__.'/settings.php';
