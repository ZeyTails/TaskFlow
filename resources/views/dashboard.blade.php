<x-layouts::app title="Tableau de bord">
    <div class="mx-auto w-full max-w-6xl space-y-6 p-4 sm:p-6 lg:p-8">
        <section class="rounded-2xl border border-zinc-200 bg-white p-6 dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-semibold text-zinc-900 dark:text-zinc-100">Tableau de bord</h1>
                    <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                        Vue rapide de vos taches assignees et de vos espaces recents.
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
                    <a
                        href="{{ route('workspaces.index') }}"
                        wire:navigate
                        class="inline-flex items-center rounded-lg border border-black bg-black px-3 py-2 text-sm font-medium text-white transition hover:bg-zinc-800 dark:border-zinc-100 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200"
                    >
                        Creer ou rejoindre un espace
                    </a>
                </div>
            </div>
        </section>

        <section class="grid gap-4 md:grid-cols-3">
            <article class="rounded-2xl border border-zinc-200 bg-white p-5 dark:border-zinc-800 dark:bg-zinc-900">
                <p class="text-sm text-zinc-500 dark:text-zinc-400">Taches assignees</p>
                <p class="mt-2 text-3xl font-semibold text-zinc-900 dark:text-zinc-100">{{ $assignedTasksCount }}</p>
                <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">Toutes vos taches actuelles.</p>
            </article>

            <article class="rounded-2xl border border-zinc-200 bg-white p-5 dark:border-zinc-800 dark:bg-zinc-900">
                <p class="text-sm text-zinc-500 dark:text-zinc-400">En retard</p>
                <p class="mt-2 text-3xl font-semibold text-zinc-900 dark:text-zinc-100">{{ $overdueTasksCount }}</p>
                <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">A traiter en priorite.</p>
            </article>

            <article class="rounded-2xl border border-zinc-200 bg-white p-5 dark:border-zinc-800 dark:bg-zinc-900">
                <p class="text-sm text-zinc-500 dark:text-zinc-400">A faire aujourd hui</p>
                <p class="mt-2 text-3xl font-semibold text-zinc-900 dark:text-zinc-100">{{ $dueTodayTasksCount }}</p>
                <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">Echeance du jour.</p>
            </article>
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
                            <p class="font-medium text-zinc-900 dark:text-zinc-100">{{ $workspace->name }}</p>
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
                            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                                {{ $project->workspace->name }} . {{ $project->tasks_count }} tache(s)
                            </p>
                        </div>
                    @empty
                        <p class="rounded-xl border border-dashed border-zinc-300 p-6 text-sm text-zinc-600 dark:border-zinc-700 dark:text-zinc-400">
                            Aucun projet recent a afficher.
                        </p>
                    @endforelse
                </div>
            </div>
        </section>
    </div>
</x-layouts::app>
