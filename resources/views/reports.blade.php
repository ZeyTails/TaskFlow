<x-layouts::app title="Rapports">
    <div class="mx-auto w-full max-w-6xl space-y-6 p-4 sm:p-6 lg:p-8">
        <section class="rounded-2xl border border-zinc-200 bg-white p-6 dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-semibold text-zinc-900 dark:text-zinc-100">Rapports d activite</h1>
                    <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                        Synthese des projets, des membres et de l activite recente.
                    </p>
                </div>

                <a
                    href="{{ route('reports.export') }}"
                    class="inline-flex items-center rounded-lg border border-black bg-black px-3 py-2 text-sm font-medium text-white transition hover:bg-zinc-800 dark:border-zinc-100 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200"
                >
                    Exporter en CSV
                </a>
            </div>
        </section>

        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <article class="rounded-2xl border border-zinc-200 bg-white p-5 dark:border-zinc-800 dark:bg-zinc-900">
                <p class="text-[11px] uppercase tracking-[0.18em] text-zinc-500 dark:text-zinc-400">Taches totales</p>
                <p class="mt-2 text-3xl font-semibold text-zinc-900 dark:text-zinc-100">{{ $analytics['totalTasksCount'] }}</p>
            </article>
            <article class="rounded-2xl border border-zinc-200 bg-white p-5 dark:border-zinc-800 dark:bg-zinc-900">
                <p class="text-[11px] uppercase tracking-[0.18em] text-zinc-500 dark:text-zinc-400">Taches terminees</p>
                <p class="mt-2 text-3xl font-semibold text-zinc-900 dark:text-zinc-100">{{ $analytics['completedTasksCount'] }}</p>
            </article>
            <article class="rounded-2xl border border-zinc-200 bg-white p-5 dark:border-zinc-800 dark:bg-zinc-900">
                <p class="text-[11px] uppercase tracking-[0.18em] text-zinc-500 dark:text-zinc-400">Progression globale</p>
                <p class="mt-2 text-3xl font-semibold text-zinc-900 dark:text-zinc-100">{{ $analytics['completionRate'] }}%</p>
            </article>
            <article class="rounded-2xl border border-zinc-200 bg-white p-5 dark:border-zinc-800 dark:bg-zinc-900">
                <p class="text-[11px] uppercase tracking-[0.18em] text-zinc-500 dark:text-zinc-400">Activites recentes</p>
                <p class="mt-2 text-3xl font-semibold text-zinc-900 dark:text-zinc-100">{{ $analytics['recentActivities']->count() }}</p>
            </article>
        </section>

        <section class="grid gap-4 xl:grid-cols-2">
            <div class="rounded-2xl border border-zinc-200 bg-white p-5 dark:border-zinc-800 dark:bg-zinc-900">
                <div class="mb-4">
                    <h2 class="text-base font-semibold text-zinc-900 dark:text-zinc-100">Statistiques par projet</h2>
                    <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">Avancement, volume et retard par projet.</p>
                </div>

                <div class="space-y-3">
                    @forelse ($analytics['projectStats'] as $project)
                        <article class="rounded-xl border border-zinc-200 p-4 dark:border-zinc-800">
                            <div class="flex flex-wrap items-center justify-between gap-3">
                                <div>
                                    <p class="font-medium text-zinc-900 dark:text-zinc-100">{{ $project->name }}</p>
                                    <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">{{ $project->workspace->name }}</p>
                                </div>
                                <span class="text-sm font-medium text-zinc-700 dark:text-zinc-200">{{ $project->progress_rate }}%</span>
                            </div>
                            <div class="mt-3 h-2 rounded-full bg-zinc-100 dark:bg-zinc-800">
                                <div class="h-2 rounded-full bg-zinc-900 dark:bg-zinc-100" style="width: {{ $project->progress_rate }}%"></div>
                            </div>
                            <div class="mt-3 flex flex-wrap items-center gap-2 text-xs text-zinc-500 dark:text-zinc-400">
                                <span>{{ $project->tasks_count }} tache(s)</span>
                                <span>• {{ $project->completed_tasks_count }} terminee(s)</span>
                                <span>• {{ $project->overdue_tasks_count }} en retard</span>
                            </div>
                        </article>
                    @empty
                        <div class="rounded-xl border border-dashed border-zinc-300 px-4 py-8 text-center text-sm text-zinc-600 dark:border-zinc-700 dark:text-zinc-400">
                            Aucune statistique projet disponible.
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="rounded-2xl border border-zinc-200 bg-white p-5 dark:border-zinc-800 dark:bg-zinc-900">
                <div class="mb-4">
                    <h2 class="text-base font-semibold text-zinc-900 dark:text-zinc-100">Statistiques par utilisateur</h2>
                    <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">Repartition des taches et taux de completion.</p>
                </div>

                <div class="space-y-3">
                    @forelse ($analytics['memberStats'] as $member)
                        <article class="rounded-xl border border-zinc-200 p-4 dark:border-zinc-800">
                            <div class="flex flex-wrap items-center justify-between gap-3">
                                <div>
                                    <p class="font-medium text-zinc-900 dark:text-zinc-100">{{ $member->name }}</p>
                                    <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">{{ $member->email }}</p>
                                </div>
                                <span class="text-sm font-medium text-zinc-700 dark:text-zinc-200">{{ $member->completion_rate }}%</span>
                            </div>
                            <div class="mt-3 h-2 rounded-full bg-zinc-100 dark:bg-zinc-800">
                                <div class="h-2 rounded-full bg-zinc-900 dark:bg-zinc-100" style="width: {{ $member->completion_rate }}%"></div>
                            </div>
                            <div class="mt-3 flex flex-wrap items-center gap-2 text-xs text-zinc-500 dark:text-zinc-400">
                                <span>{{ $member->assigned_tasks_count }} tache(s) assignee(s)</span>
                                <span>• {{ $member->completed_tasks_count }} terminee(s)</span>
                            </div>
                        </article>
                    @empty
                        <div class="rounded-xl border border-dashed border-zinc-300 px-4 py-8 text-center text-sm text-zinc-600 dark:border-zinc-700 dark:text-zinc-400">
                            Aucune statistique utilisateur disponible.
                        </div>
                    @endforelse
                </div>
            </div>
        </section>

        <section class="rounded-2xl border border-zinc-200 bg-white p-5 dark:border-zinc-800 dark:bg-zinc-900">
            <div class="mb-4">
                <h2 class="text-base font-semibold text-zinc-900 dark:text-zinc-100">Historique des activites</h2>
                <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">Historique complet avec pagination de 7 elements par page.</p>
            </div>

            <div class="space-y-3">
                @forelse ($activityHistory as $activity)
                    <x-activity-log-card :activity="$activity" />
                @empty
                    <div class="rounded-xl border border-dashed border-zinc-300 px-4 py-8 text-center text-sm text-zinc-600 dark:border-zinc-700 dark:text-zinc-400">
                        Aucun historique disponible pour le moment.
                    </div>
                @endforelse
            </div>

            @if ($activityHistory->hasPages())
                <div class="mt-5">
                    {{ $activityHistory->links() }}
                </div>
            @endif
        </section>
    </div>
</x-layouts::app>
