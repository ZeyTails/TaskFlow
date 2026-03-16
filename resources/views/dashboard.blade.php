<x-layouts::app title="Tableau de bord">
    <div class="mx-auto w-full max-w-6xl space-y-6 p-4 sm:p-6 lg:p-8">
        <section class="rounded-2xl border border-zinc-200 bg-white p-6 dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-semibold text-zinc-900 dark:text-zinc-100">Tableau de bord</h1>
                    <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                        Vue rapide de vos taches, de la progression des projets et de l activite recente.
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <a
                        href="{{ route('my-tasks') }}"
                        wire:navigate
                        class="inline-flex items-center rounded-lg border border-zinc-300 px-3 py-2 text-sm font-medium text-zinc-700 transition hover:bg-zinc-100 dark:border-zinc-700 dark:text-zinc-200 dark:hover:bg-zinc-800"
                    >
                        Voir mes taches
                    </a>
                    <flux:modal.trigger name="workspace-quick-actions">
                        <button
                            type="button"
                            class="inline-flex items-center rounded-lg border border-black bg-black px-3 py-2 text-sm font-medium text-white transition hover:bg-zinc-800 dark:border-zinc-100 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200"
                        >
                            Creer ou rejoindre un espace
                        </button>
                    </flux:modal.trigger>
                </div>
            </div>
        </section>

        <section class="grid gap-4 lg:grid-cols-[1.4fr_0.9fr]">
            <div class="rounded-2xl border border-zinc-200 bg-white p-5 dark:border-zinc-800 dark:bg-zinc-900">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <h2 class="text-base font-semibold text-zinc-900 dark:text-zinc-100">Vue d ensemble</h2>
                        <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">Les chiffres utiles de votre espace de travail.</p>
                    </div>
                    <a href="{{ route('workspaces.index') }}" wire:navigate class="text-sm font-medium text-zinc-600 hover:text-zinc-900 dark:text-zinc-300 dark:hover:text-zinc-100">
                        Ouvrir les espaces
                    </a>
                </div>

                <div class="mt-5 grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
                    <article class="rounded-xl border border-zinc-200 px-4 py-3 dark:border-zinc-800">
                        <p class="text-[11px] uppercase tracking-[0.18em] text-zinc-500 dark:text-zinc-400">Taches assignees</p>
                        <p class="mt-2 text-3xl font-semibold text-zinc-900 dark:text-zinc-100">{{ $assignedTasksCount }}</p>
                    </article>
                    <article class="rounded-xl border border-zinc-200 px-4 py-3 dark:border-zinc-800">
                        <p class="text-[11px] uppercase tracking-[0.18em] text-zinc-500 dark:text-zinc-400">A faire aujourd hui</p>
                        <p class="mt-2 text-3xl font-semibold text-zinc-900 dark:text-zinc-100">{{ $dueTodayTasksCount }}</p>
                    </article>
                    <article class="rounded-xl border border-zinc-200 px-4 py-3 dark:border-zinc-800">
                        <p class="text-[11px] uppercase tracking-[0.18em] text-zinc-500 dark:text-zinc-400">En retard</p>
                        <p class="mt-2 text-3xl font-semibold text-zinc-900 dark:text-zinc-100">{{ $overdueTasksCount }}</p>
                    </article>
                    <article class="rounded-xl border border-zinc-200 px-4 py-3 dark:border-zinc-800">
                        <p class="text-[11px] uppercase tracking-[0.18em] text-zinc-500 dark:text-zinc-400">Espaces</p>
                        <p class="mt-2 text-3xl font-semibold text-zinc-900 dark:text-zinc-100">{{ $totalWorkspacesCount }}</p>
                    </article>
                    <article class="rounded-xl border border-zinc-200 px-4 py-3 dark:border-zinc-800">
                        <p class="text-[11px] uppercase tracking-[0.18em] text-zinc-500 dark:text-zinc-400">Projets</p>
                        <p class="mt-2 text-3xl font-semibold text-zinc-900 dark:text-zinc-100">{{ $totalProjectsCount }}</p>
                    </article>
                    <article class="rounded-xl border border-zinc-200 px-4 py-3 dark:border-zinc-800">
                        <p class="text-[11px] uppercase tracking-[0.18em] text-zinc-500 dark:text-zinc-400">Invitations</p>
                        <p class="mt-2 text-3xl font-semibold text-zinc-900 dark:text-zinc-100">{{ $pendingInvitationsCount }}</p>
                    </article>
                    <article class="rounded-xl border border-zinc-200 px-4 py-3 dark:border-zinc-800">
                        <p class="text-[11px] uppercase tracking-[0.18em] text-zinc-500 dark:text-zinc-400">Terminees</p>
                        <p class="mt-2 text-3xl font-semibold text-zinc-900 dark:text-zinc-100">{{ $analytics['completedTasksCount'] }}</p>
                    </article>
                    <article class="rounded-xl border border-zinc-200 px-4 py-3 dark:border-zinc-800">
                        <p class="text-[11px] uppercase tracking-[0.18em] text-zinc-500 dark:text-zinc-400">Progression globale</p>
                        <p class="mt-2 text-3xl font-semibold text-zinc-900 dark:text-zinc-100">{{ $analytics['completionRate'] }}%</p>
                    </article>
                    <article class="rounded-xl border border-zinc-200 px-4 py-3 dark:border-zinc-800">
                        <p class="text-[11px] uppercase tracking-[0.18em] text-zinc-500 dark:text-zinc-400">Alertes</p>
                        <p class="mt-2 text-3xl font-semibold text-zinc-900 dark:text-zinc-100">{{ $unreadTaskNotificationsCount }}</p>
                    </article>
                </div>
            </div>

            <div class="rounded-2xl border border-zinc-200 bg-white p-5 dark:border-zinc-800 dark:bg-zinc-900">
                <h2 class="text-base font-semibold text-zinc-900 dark:text-zinc-100">Raccourcis</h2>
                <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">Acces rapides aux actions les plus frequentes.</p>

                <div class="mt-5 space-y-3">
                    <a
                        href="{{ route('notifications') }}"
                        wire:navigate
                        class="flex items-center justify-between rounded-xl border border-zinc-200 px-4 py-3 transition hover:border-zinc-400 dark:border-zinc-800 dark:hover:border-zinc-600"
                    >
                        <span>
                            <span class="block text-sm font-medium text-zinc-900 dark:text-zinc-100">Notifications</span>
                            <span class="mt-1 block text-xs text-zinc-500 dark:text-zinc-400">Invitations et alertes sur les taches</span>
                        </span>
                        <span class="inline-flex rounded-full border border-zinc-200 px-2.5 py-1 text-xs font-medium text-zinc-700 dark:border-zinc-700 dark:text-zinc-200">
                            {{ $pendingInvitationsCount + $unreadTaskNotificationsCount }}
                        </span>
                    </a>

                    <a
                        href="{{ route('reports') }}"
                        wire:navigate
                        class="flex items-center justify-between rounded-xl border border-zinc-200 px-4 py-3 transition hover:border-zinc-400 dark:border-zinc-800 dark:hover:border-zinc-600"
                    >
                        <span>
                            <span class="block text-sm font-medium text-zinc-900 dark:text-zinc-100">Rapports</span>
                            <span class="mt-1 block text-xs text-zinc-500 dark:text-zinc-400">Statistiques par projet et par membre</span>
                        </span>
                        <span class="text-zinc-400">CSV</span>
                    </a>

                    <div class="rounded-xl border border-zinc-200 px-4 py-3 dark:border-zinc-800">
                        <p class="text-[11px] uppercase tracking-[0.18em] text-zinc-500 dark:text-zinc-400">Espaces epingles</p>
                        <p class="mt-2 text-2xl font-semibold text-zinc-900 dark:text-zinc-100">{{ $pinnedWorkspacesCount }}</p>
                        <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">Retrouvez-les en tete de votre liste.</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="grid gap-4 lg:grid-cols-2">
            <div class="rounded-2xl border border-zinc-200 bg-white p-5 dark:border-zinc-800 dark:bg-zinc-900">
                <div class="mb-4 flex items-center justify-between gap-3">
                    <h2 class="text-base font-semibold text-zinc-900 dark:text-zinc-100">Espaces recents</h2>
                    <a href="{{ route('workspaces.index') }}" wire:navigate class="text-sm font-medium text-zinc-600 hover:text-zinc-900 dark:text-zinc-300 dark:hover:text-zinc-100">
                        Voir tout
                    </a>
                </div>

                <div class="space-y-3">
                    @forelse ($recentWorkspaces as $workspace)
                        <a
                            href="{{ route('workspaces.projects.index', $workspace) }}"
                            wire:navigate
                            class="block rounded-xl border border-zinc-200 p-4 transition hover:border-zinc-400 dark:border-zinc-800 dark:hover:border-zinc-600"
                        >
                            <div class="flex items-center gap-3">
                                <x-workspace-icon :workspace="$workspace" size="sm" />
                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <p class="font-medium text-zinc-900 dark:text-zinc-100">{{ $workspace->name }}</p>
                                        <x-workspace-theme-badge :workspace="$workspace" />
                                    </div>
                                </div>
                            </div>
                            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                                {{ $workspace->projects_count }} projet(s) . {{ $workspace->members_count }} membre(s)
                            </p>
                        </a>
                    @empty
                        <p class="rounded-xl border border-dashed border-zinc-300 p-6 text-sm text-zinc-600 dark:border-zinc-700 dark:text-zinc-400">
                            Aucun espace pour le moment.
                        </p>
                    @endforelse
                </div>
            </div>

            <div class="rounded-2xl border border-zinc-200 bg-white p-5 dark:border-zinc-800 dark:bg-zinc-900">
                <div class="mb-4 flex items-center justify-between gap-3">
                    <h2 class="text-base font-semibold text-zinc-900 dark:text-zinc-100">Projets recents</h2>
                    <a href="{{ route('workspaces.index') }}" wire:navigate class="text-sm font-medium text-zinc-600 hover:text-zinc-900 dark:text-zinc-300 dark:hover:text-zinc-100">
                        Ouvrir un espace
                    </a>
                </div>

                <div class="space-y-3">
                    @forelse ($recentProjects as $project)
                        <div class="rounded-xl border border-zinc-200 p-4 dark:border-zinc-800">
                            <p class="font-medium text-zinc-900 dark:text-zinc-100">{{ $project->name }}</p>
                            <div class="mt-1 flex flex-wrap items-center gap-2 text-sm text-zinc-600 dark:text-zinc-400">
                                <span>{{ $project->workspace->name }}</span>
                                <x-workspace-theme-badge :workspace="$project->workspace" />
                                <span>{{ $project->tasks_count }} tache(s)</span>
                                <span>{{ $project->completed_tasks_count }} terminee(s)</span>
                            </div>
                            <div class="mt-3">
                                <div class="flex items-center justify-between text-xs text-zinc-500 dark:text-zinc-400">
                                    <span>Avancement</span>
                                    <span>{{ $project->progress_rate }}%</span>
                                </div>
                                <div class="mt-1 h-2 rounded-full bg-zinc-100 dark:bg-zinc-800">
                                    <div class="h-2 rounded-full bg-zinc-900 dark:bg-zinc-100" style="width: {{ $project->progress_rate }}%"></div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <p class="rounded-xl border border-dashed border-zinc-300 p-6 text-sm text-zinc-600 dark:border-zinc-700 dark:text-zinc-400">
                            Aucun projet recent a afficher.
                        </p>
                    @endforelse
                </div>
            </div>
        </section>

        <section class="grid gap-4 xl:grid-cols-[1.15fr_0.85fr]">
            <div class="rounded-2xl border border-zinc-200 bg-white p-5 dark:border-zinc-800 dark:bg-zinc-900">
                <div class="mb-4 flex items-center justify-between gap-3">
                    <div>
                        <h2 class="text-base font-semibold text-zinc-900 dark:text-zinc-100">Activite recente</h2>
                        <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">Les 5 dernieres actions des membres sur vos espaces.</p>
                    </div>
                    <a href="{{ route('reports') }}" wire:navigate class="text-sm font-medium text-zinc-600 hover:text-zinc-900 dark:text-zinc-300 dark:hover:text-zinc-100">
                        Voir le rapport
                    </a>
                </div>

                <div class="space-y-3">
                    @forelse ($analytics['recentActivities']->take(5) as $activity)
                        <x-activity-log-card :activity="$activity" date-format="d/m H:i" />
                    @empty
                        <p class="rounded-xl border border-dashed border-zinc-300 p-6 text-sm text-zinc-600 dark:border-zinc-700 dark:text-zinc-400">
                            Aucune activite recente pour le moment.
                        </p>
                    @endforelse
                </div>
            </div>

            <div class="space-y-4">
                <div class="rounded-2xl border border-zinc-200 bg-white p-5 dark:border-zinc-800 dark:bg-zinc-900">
                    <div class="mb-4 flex items-center justify-between gap-3">
                        <div>
                            <h2 class="text-base font-semibold text-zinc-900 dark:text-zinc-100">Statistiques membres</h2>
                            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">Taches realisees par utilisateur.</p>
                        </div>
                    </div>

                    <div class="space-y-3">
                        @forelse ($analytics['memberStats']->take(6) as $member)
                            <div class="rounded-xl border border-zinc-200 px-4 py-3 dark:border-zinc-800">
                                <div class="flex items-center justify-between gap-3">
                                    <div>
                                        <p class="font-medium text-zinc-900 dark:text-zinc-100">{{ $member->name }}</p>
                                        <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ $member->email }}</p>
                                    </div>
                                    <span class="text-sm font-medium text-zinc-700 dark:text-zinc-200">{{ $member->completed_tasks_count }}/{{ $member->assigned_tasks_count }}</span>
                                </div>
                                <div class="mt-3 h-2 rounded-full bg-zinc-100 dark:bg-zinc-800">
                                    <div class="h-2 rounded-full bg-zinc-900 dark:bg-zinc-100" style="width: {{ $member->completion_rate }}%"></div>
                                </div>
                            </div>
                        @empty
                            <p class="rounded-xl border border-dashed border-zinc-300 p-6 text-sm text-zinc-600 dark:border-zinc-700 dark:text-zinc-400">
                                Aucune statistique membre disponible.
                            </p>
                        @endforelse
                    </div>
                </div>

                <div class="rounded-2xl border border-zinc-200 bg-white p-5 dark:border-zinc-800 dark:bg-zinc-900">
                    <div class="mb-4 flex items-center justify-between gap-3">
                        <div>
                            <h2 class="text-base font-semibold text-zinc-900 dark:text-zinc-100">Taches terminees</h2>
                            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">Dernieres taches cloturees.</p>
                        </div>
                    </div>

                    <div class="space-y-3">
                        @forelse ($analytics['recentCompletedTasks'] as $task)
                            <a href="{{ route('tasks.show', $task) }}" wire:navigate class="block rounded-xl border border-zinc-200 p-4 transition hover:border-zinc-400 dark:border-zinc-800 dark:hover:border-zinc-600">
                                <p class="font-medium text-zinc-900 dark:text-zinc-100">{{ $task->title }}</p>
                                <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">{{ $task->project->workspace->name }} • {{ $task->project->name }}</p>
                            </a>
                        @empty
                            <p class="rounded-xl border border-dashed border-zinc-300 p-6 text-sm text-zinc-600 dark:border-zinc-700 dark:text-zinc-400">
                                Aucune tache terminee recente.
                            </p>
                        @endforelse
                    </div>
                </div>
            </div>
        </section>
    </div>
</x-layouts::app>
