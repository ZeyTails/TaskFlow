<?php

use App\Livewire\Projects\Index as ProjectIndex;
use App\Livewire\Tasks\Index as TaskIndex;
use App\Livewire\Tasks\Show as TaskShow;
use App\Livewire\Workspaces\Index as WorkspaceIndex;
use App\Livewire\Workspaces\Members as WorkspaceMembers;
use App\Models\ActivityLog;
use App\Models\Project;
use App\Models\Task;
use App\Models\UserNotification;
use App\Models\Workspace;
use App\Models\WorkspaceInvitation;
use App\Support\WorkspaceAnalytics;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function () {
        $user = auth()->user();
        $analytics = WorkspaceAnalytics::forUser($user);

        $assignedTasksCount = $user->assignedTasks()->count();
        $overdueTasksCount = $user->assignedTasks()
            ->whereNotNull('tasks.due_date')
            ->whereDate('tasks.due_date', '<', today())
            ->where('tasks.status', '!=', Task::STATUS_DONE)
            ->count();
        $dueTodayTasksCount = $user->assignedTasks()
            ->whereDate('tasks.due_date', today())
            ->where('tasks.status', '!=', Task::STATUS_DONE)
            ->count();

        $totalWorkspacesCount = $user->workspaces()->count();
        $pinnedWorkspacesCount = $user->workspaces()
            ->wherePivot('is_pinned', true)
            ->count();
        $pendingInvitationsCount = WorkspaceInvitation::query()
            ->pending()
            ->where('email', $user->email)
            ->count();
        $unreadTaskNotificationsCount = $user->taskNotifications()
            ->whereNull('read_at')
            ->count();

        $recentWorkspaces = $user->workspaces()
            ->withCount(['projects', 'members'])
            ->orderByDesc('workspace_user.created_at')
            ->limit(4)
            ->get();

        $totalProjectsCount = Project::query()
            ->whereHas('workspace.members', fn ($query) => $query
                ->whereKey($user->id)
                ->where('workspace_user.status', Workspace::MEMBER_STATUS_ACTIVE))
            ->count();

        $recentProjects = $analytics['projectStats']->take(6);

        return view('dashboard', compact(
            'assignedTasksCount',
            'overdueTasksCount',
            'dueTodayTasksCount',
            'totalWorkspacesCount',
            'pinnedWorkspacesCount',
            'pendingInvitationsCount',
            'unreadTaskNotificationsCount',
            'totalProjectsCount',
            'recentWorkspaces',
            'recentProjects',
            'analytics'
        ));
    })->name('dashboard');

    Route::get('my-tasks', function () {
        $user = auth()->user();

        $status = request('status');
        $priority = request('priority');
        $echeance = request('echeance');
        $search = trim((string) request('search'));

        $baseQuery = Task::query()
            ->whereHas('assignees', fn ($query) => $query->whereKey($user->id))
            ->whereHas('project.workspace.members', fn ($query) => $query
                ->whereKey($user->id)
                ->where('workspace_user.status', Workspace::MEMBER_STATUS_ACTIVE));

        $assignedTasksCount = (clone $baseQuery)->count();
        $todoTasksCount = (clone $baseQuery)->where('status', Task::STATUS_TODO)->count();
        $inProgressTasksCount = (clone $baseQuery)->where('status', Task::STATUS_IN_PROGRESS)->count();
        $completedTasksCount = (clone $baseQuery)->where('status', Task::STATUS_DONE)->count();
        $overdueTasksCount = (clone $baseQuery)
            ->whereNotNull('due_date')
            ->whereDate('due_date', '<', today())
            ->where('status', '!=', Task::STATUS_DONE)
            ->count();

        $tasks = (clone $baseQuery)
            ->with(['project:id,name,workspace_id', 'project.workspace:id,name,icon_key'])
            ->when($search !== '', fn ($query) => $query->where(function ($searchQuery) use ($search): void {
                $searchQuery
                    ->where('title', 'like', '%'.$search.'%')
                    ->orWhere('description', 'like', '%'.$search.'%');
            }))
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

        return view('my-tasks', compact(
            'tasks',
            'assignedTasksCount',
            'todoTasksCount',
            'inProgressTasksCount',
            'completedTasksCount',
            'overdueTasksCount'
        ));
    })->name('my-tasks');

    Route::get('calendar', function () {
        $user = auth()->user();
        $monthParam = request('month');

        $selectedMonth = is_string($monthParam) && preg_match('/^\d{4}-\d{2}$/', $monthParam)
            ? Carbon::createFromFormat('Y-m', $monthParam)->startOfMonth()
            : today()->startOfMonth();

        $gridStart = $selectedMonth->copy()->startOfWeek(Carbon::MONDAY);
        $gridEnd = $selectedMonth->copy()->endOfMonth()->endOfWeek(Carbon::SUNDAY);

        $tasksByDate = Task::query()
            ->whereHas('assignees', fn ($query) => $query->whereKey($user->id))
            ->whereBetween('due_date', [$gridStart->toDateString(), $gridEnd->toDateString()])
            ->whereHas('project.workspace.members', fn ($query) => $query
                ->whereKey($user->id)
                ->where('workspace_user.status', Workspace::MEMBER_STATUS_ACTIVE))
            ->with(['project:id,name,workspace_id', 'project.workspace:id,name'])
            ->orderBy('due_date')
            ->get()
            ->groupBy(fn (Task $task) => $task->due_date?->format('Y-m-d'));

        return view('calendar', compact('tasksByDate', 'selectedMonth', 'gridStart', 'gridEnd'));
    })->name('calendar');

    Route::get('notifications', function () {
        $user = auth()->user();

        $invitations = WorkspaceInvitation::query()
            ->pending()
            ->where('email', $user->email)
            ->with('workspace:id,name', 'inviter:id,name,email')
            ->latest('created_at')
            ->get();

        $taskNotifications = $user->taskNotifications()
            ->with(['task.project.workspace'])
            ->latest()
            ->get();

        return view('notifications', compact('invitations', 'taskNotifications'));
    })->name('notifications');

    Route::post('notifications/tasks/read-all', function () {
        auth()->user()->taskNotifications()
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return redirect()
            ->route('notifications')
            ->with('status', 'Les alertes ont ete marquees comme lues.');
    })->name('notifications.tasks.read-all');

    Route::post('notifications/tasks/{notification}/read', function (UserNotification $notification) {
        abort_unless($notification->user_id === auth()->id(), 403);

        if ($notification->read_at === null) {
            $notification->update(['read_at' => now()]);
        }

        return redirect()
            ->route('notifications')
            ->with('status', 'Alerte marquee comme lue.');
    })->name('notifications.tasks.read');

    Route::post('notifications/invitations/{invitation}/accept', function (WorkspaceInvitation $invitation) {
        $user = auth()->user();

        abort_unless(
            $invitation->email === $user->email && $invitation->accepted_at === null && $invitation->cancelled_at === null,
            403
        );

        if ($invitation->isExpired()) {
            return redirect()
                ->route('notifications')
                ->with('error', 'Cette invitation a expire.');
        }

        DB::transaction(function () use ($invitation, $user): void {
            $alreadyMember = $invitation->workspace->members()
                ->whereKey($user->id)
                ->exists();

            if (! $alreadyMember) {
                $invitation->workspace->members()->attach($user->id, [
                    'role' => $invitation->role,
                    'job_title' => $invitation->job_title,
                    'status' => Workspace::MEMBER_STATUS_ACTIVE,
                    'suspended_at' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $invitation->update([
                'accepted_at' => now(),
            ]);
        });

        return redirect()
            ->route('notifications')
            ->with('status', 'Invitation acceptee. Vous avez rejoint le workspace.');
    })->name('notifications.invitations.accept');

    Route::post('notifications/invitations/{invitation}/decline', function (WorkspaceInvitation $invitation) {
        $user = auth()->user();

        abort_unless(
            $invitation->email === $user->email && $invitation->accepted_at === null && $invitation->cancelled_at === null,
            403
        );

        $invitation->update([
            'cancelled_at' => now(),
        ]);

        return redirect()
            ->route('notifications')
            ->with('status', 'Invitation refusee.');
    })->name('notifications.invitations.decline');

    Route::get('reports', function () {
        $analytics = WorkspaceAnalytics::forUser(auth()->user());
        $activityHistory = ActivityLog::query()
            ->whereIn('workspace_id', $analytics['workspaceIds'])
            ->with(['actor:id,name', 'workspace:id,name', 'project:id,name', 'task:id,title'])
            ->latest()
            ->paginate(7)
            ->withQueryString();

        return view('reports', [
            'analytics' => $analytics,
            'activityHistory' => $activityHistory,
        ]);
    })->name('reports');

    Route::get('reports/export', function () {
        $user = auth()->user();
        $analytics = WorkspaceAnalytics::forUser($user);
        $activities = ActivityLog::query()
            ->whereIn('workspace_id', $analytics['workspaceIds'])
            ->with(['actor:id,name', 'workspace:id,name', 'project:id,name', 'task:id,title'])
            ->latest()
            ->limit(200)
            ->get();

        return response()->streamDownload(function () use ($analytics, $activities): void {
            $stream = fopen('php://output', 'w');

            // UTF-8 BOM for Excel compatibility with French accents.
            fwrite($stream, "\xEF\xBB\xBF");

            fputcsv($stream, ['Rapport d activite TaskFlow'], ';');
            fputcsv($stream, ['Genere le', now()->format('d/m/Y H:i:s')], ';');
            fputcsv($stream, [], ';');

            fputcsv($stream, ['Resume general'], ';');
            fputcsv($stream, ['Nombre total de taches', $analytics['totalTasksCount']], ';');
            fputcsv($stream, ['Nombre de taches terminees', $analytics['completedTasksCount']], ';');
            fputcsv($stream, ['Taux de progression global', $analytics['completionRate'].'%'], ';');
            fputcsv($stream, [], ';');

            fputcsv($stream, ['Statistiques par projet'], ';');
            fputcsv($stream, ['Espace de travail', 'Projet', 'Nombre de taches', 'Taches terminees', 'Taches en retard', 'Taux d avancement'], ';');
            foreach ($analytics['projectStats'] as $project) {
                fputcsv($stream, [
                    $project->workspace->name,
                    $project->name,
                    $project->tasks_count,
                    $project->completed_tasks_count,
                    $project->overdue_tasks_count,
                    $project->progress_rate.'%',
                ], ';');
            }

            fputcsv($stream, [], ';');
            fputcsv($stream, ['Statistiques par membre'], ';');
            fputcsv($stream, ['Membre', 'Adresse email', 'Taches assignees', 'Taches terminees', 'Taux de completion'], ';');
            foreach ($analytics['memberStats'] as $member) {
                fputcsv($stream, [
                    $member->name,
                    $member->email,
                    $member->assigned_tasks_count,
                    $member->completed_tasks_count,
                    $member->completion_rate.'%',
                ], ';');
            }

            fputcsv($stream, [], ';');
            fputcsv($stream, ['Historique recent des activites'], ';');
            fputcsv($stream, ['Date', 'Auteur de l action', 'Type d activite', 'Espace de travail', 'Projet', 'Tache', 'Description', 'Champ modifie', 'Avant', 'Apres'], ';');
            foreach ($activities as $activity) {
                $baseColumns = [
                    $activity->created_at->format('d/m/Y H:i'),
                    $activity->actor?->name ?? 'Systeme',
                    match ($activity->type) {
                        'task_created' => 'Creation de tache',
                        'task_updated' => 'Mise a jour de tache',
                        'task_assigned' => 'Attribution de tache',
                        'comment_added' => 'Ajout de commentaire',
                        'project_created' => 'Creation de projet',
                        'project_updated' => 'Mise a jour de projet',
                        'project_deleted' => 'Suppression de projet',
                        default => $activity->type,
                    },
                    $activity->workspace?->name,
                    $activity->project?->name,
                    $activity->task?->title,
                    $activity->description,
                ];

                $changeSet = $activity->meta['change_set'] ?? null;

                if (is_array($changeSet) && $changeSet !== []) {
                    foreach ($changeSet as $change) {
                        fputcsv($stream, [
                            ...$baseColumns,
                            $change['label'] ?? $change['field'] ?? 'Modification',
                            $change['before'] ?? '',
                            $change['after'] ?? '',
                        ], ';');
                    }

                    continue;
                }

                fputcsv($stream, [
                    ...$baseColumns,
                    '',
                    '',
                    '',
                ], ';');
            }

            fclose($stream);
        }, 'taskflow-report-'.now()->format('Ymd-His').'.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    })->name('reports.export');

    Route::get('workspaces', WorkspaceIndex::class)->name('workspaces.index');
    Route::get('workspaces/{workspace}/members', WorkspaceMembers::class)->name('workspaces.members.index');
    Route::get('workspaces/{workspace}/projects', ProjectIndex::class)->name('workspaces.projects.index');
    Route::get('workspaces/{workspace}/projects/{project}/tasks', TaskIndex::class)->name('workspaces.projects.tasks.index');
    Route::get('tasks/{task}', TaskShow::class)->name('tasks.show');
});

require __DIR__.'/settings.php';
