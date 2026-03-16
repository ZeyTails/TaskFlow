@php
    $statusLabels = [
        \App\Models\Task::STATUS_TODO => 'A faire',
        \App\Models\Task::STATUS_IN_PROGRESS => 'En cours',
        \App\Models\Task::STATUS_DONE => 'Terminee',
    ];

    $priorityLabels = [
        \App\Models\Task::PRIORITY_LOW => 'Basse',
        \App\Models\Task::PRIORITY_MEDIUM => 'Moyenne',
        \App\Models\Task::PRIORITY_HIGH => 'Haute',
    ];

    $statusThemes = [
        \App\Models\Task::STATUS_TODO => 'border-zinc-300 bg-zinc-50 text-zinc-700 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-200',
        \App\Models\Task::STATUS_IN_PROGRESS => 'border-blue-300 bg-blue-50 text-blue-700 dark:border-blue-900/70 dark:bg-blue-900/20 dark:text-blue-300',
        \App\Models\Task::STATUS_DONE => 'border-emerald-300 bg-emerald-50 text-emerald-700 dark:border-emerald-900/70 dark:bg-emerald-900/20 dark:text-emerald-300',
    ];

    $priorityThemes = [
        \App\Models\Task::PRIORITY_LOW => 'border-zinc-300 bg-zinc-50 text-zinc-700 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-200',
        \App\Models\Task::PRIORITY_MEDIUM => 'border-amber-300 bg-amber-50 text-amber-700 dark:border-amber-900/70 dark:bg-amber-900/20 dark:text-amber-300',
        \App\Models\Task::PRIORITY_HIGH => 'border-red-300 bg-red-50 text-red-700 dark:border-red-900/70 dark:bg-red-900/20 dark:text-red-300',
    ];

    $activeFilters = array_filter([
        request('search') ? 'Recherche: '.request('search') : null,
        request('status') ? 'Statut: '.($statusLabels[request('status')] ?? request('status')) : null,
        request('priority') ? 'Priorite: '.($priorityLabels[request('priority')] ?? request('priority')) : null,
        request('echeance') ? match (request('echeance')) {
            'retard' => 'Echeance: En retard',
            'aujourdhui' => 'Echeance: Aujourd hui',
            'semaine' => 'Echeance: 7 prochains jours',
            default => 'Echeance: '.request('echeance'),
        } : null,
    ]);
@endphp

<x-layouts::app title="Mes taches">
    <div class="mx-auto w-full max-w-6xl space-y-6 p-4 sm:p-6 lg:p-8">
        <section class="rounded-2xl border border-zinc-200 bg-white p-6 dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-semibold text-zinc-900 dark:text-zinc-100">Mes taches</h1>
                    <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                        Espace personnel pour suivre vos priorites, votre charge active et vos taches terminees.
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <a
                        href="{{ route('calendar') }}"
                        wire:navigate
                        class="inline-flex items-center rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm font-medium text-zinc-700 transition hover:bg-zinc-100 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-200 dark:hover:bg-zinc-800"
                    >
                        Ouvrir le calendrier
                    </a>
                    <a
                        href="{{ route('reports') }}"
                        wire:navigate
                        class="inline-flex items-center rounded-lg border border-black bg-black px-3 py-2 text-sm font-medium text-white transition hover:bg-zinc-800 dark:border-zinc-100 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200"
                    >
                        Voir les rapports
                    </a>
                </div>
            </div>
        </section>

        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
            <article class="rounded-2xl border border-zinc-200 bg-white p-5 dark:border-zinc-800 dark:bg-zinc-900">
                <p class="text-[11px] uppercase tracking-[0.18em] text-zinc-500 dark:text-zinc-400">Toutes</p>
                <p class="mt-2 text-3xl font-semibold text-zinc-900 dark:text-zinc-100">{{ $assignedTasksCount }}</p>
                <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">Charge totale actuelle</p>
            </article>
            <article class="rounded-2xl border border-zinc-200 bg-white p-5 dark:border-zinc-800 dark:bg-zinc-900">
                <p class="text-[11px] uppercase tracking-[0.18em] text-zinc-500 dark:text-zinc-400">A faire</p>
                <p class="mt-2 text-3xl font-semibold text-zinc-900 dark:text-zinc-100">{{ $todoTasksCount }}</p>
                <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">Taches en attente</p>
            </article>
            <article class="rounded-2xl border border-zinc-200 bg-white p-5 dark:border-zinc-800 dark:bg-zinc-900">
                <p class="text-[11px] uppercase tracking-[0.18em] text-zinc-500 dark:text-zinc-400">En cours</p>
                <p class="mt-2 text-3xl font-semibold text-zinc-900 dark:text-zinc-100">{{ $inProgressTasksCount }}</p>
                <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">Execution active</p>
            </article>
            <article class="rounded-2xl border border-zinc-200 bg-white p-5 dark:border-zinc-800 dark:bg-zinc-900">
                <p class="text-[11px] uppercase tracking-[0.18em] text-zinc-500 dark:text-zinc-400">Terminees</p>
                <p class="mt-2 text-3xl font-semibold text-zinc-900 dark:text-zinc-100">{{ $completedTasksCount }}</p>
                <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">Taches cloturees</p>
            </article>
            <article class="rounded-2xl border border-zinc-200 bg-white p-5 dark:border-zinc-800 dark:bg-zinc-900">
                <p class="text-[11px] uppercase tracking-[0.18em] text-zinc-500 dark:text-zinc-400">En retard</p>
                <p class="mt-2 text-3xl font-semibold text-zinc-900 dark:text-zinc-100">{{ $overdueTasksCount }}</p>
                <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">A traiter rapidement</p>
            </article>
        </section>

        <section class="rounded-2xl border border-zinc-200 bg-white p-6 dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <h2 class="text-base font-semibold text-zinc-900 dark:text-zinc-100">Filtres</h2>
                    <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">Affinez votre vue sans perdre le contexte.</p>
                </div>
                @if ($activeFilters !== [])
                    <div class="flex flex-wrap gap-2">
                        @foreach ($activeFilters as $filter)
                            <span class="inline-flex rounded-full border border-zinc-200 bg-zinc-50 px-3 py-1 text-xs font-medium text-zinc-700 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-200">
                                {{ $filter }}
                            </span>
                        @endforeach
                    </div>
                @endif
            </div>

            <form method="GET" action="{{ route('my-tasks') }}" class="mt-5 grid gap-3 md:grid-cols-[1.2fr_180px_180px_200px_auto] md:items-end">
                <div>
                    <label for="search" class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Recherche</label>
                    <input
                        id="search"
                        name="search"
                        type="search"
                        value="{{ request('search') }}"
                        placeholder="Titre ou description"
                        class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 placeholder:text-zinc-500 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100 dark:placeholder:text-zinc-400"
                    >
                </div>

                <div>
                    <label for="status" class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Statut</label>
                    <select id="status" name="status" class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-700 dark:bg-zinc-900">
                        <option value="">Tous</option>
                        @foreach (\App\Models\Task::STATUSES as $status)
                            <option value="{{ $status }}" @selected(request('status') === $status)>{{ $statusLabels[$status] }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="priority" class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Priorite</label>
                    <select id="priority" name="priority" class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-700 dark:bg-zinc-900">
                        <option value="">Toutes</option>
                        @foreach (\App\Models\Task::PRIORITIES as $priority)
                            <option value="{{ $priority }}" @selected(request('priority') === $priority)>{{ $priorityLabels[$priority] }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="echeance" class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Echeance</label>
                    <select id="echeance" name="echeance" class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-700 dark:bg-zinc-900">
                        <option value="">Toutes</option>
                        <option value="retard" @selected(request('echeance') === 'retard')>En retard</option>
                        <option value="aujourdhui" @selected(request('echeance') === 'aujourdhui')>Aujourd hui</option>
                        <option value="semaine" @selected(request('echeance') === 'semaine')>7 prochains jours</option>
                    </select>
                </div>

                <div class="flex items-end gap-2">
                    <button
                        type="submit"
                        class="inline-flex w-full items-center justify-center rounded-lg border border-black bg-black px-3 py-2 text-sm font-medium text-white transition hover:bg-zinc-800 dark:border-zinc-100 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200"
                    >
                        Appliquer
                    </button>
                    <a
                        href="{{ route('my-tasks') }}"
                        wire:navigate
                        class="inline-flex items-center justify-center rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm font-medium text-zinc-700 transition hover:bg-zinc-100 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-200 dark:hover:bg-zinc-800"
                    >
                        Reset
                    </a>
                </div>
            </form>
        </section>

        <section class="space-y-4">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 class="text-base font-semibold text-zinc-900 dark:text-zinc-100">Liste des taches</h2>
                    <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">{{ $tasks->total() }} resultat(s) correspondant a votre selection.</p>
                </div>
            </div>

            @if ($tasks->count() > 0)
                <div class="grid gap-4 xl:grid-cols-2">
                    @foreach ($tasks as $task)
                        @php
                            $isOverdue = $task->due_date && $task->due_date->isPast() && $task->status !== \App\Models\Task::STATUS_DONE;
                            $isToday = $task->due_date && $task->due_date->isToday();
                        @endphp
                        <article class="rounded-2xl border border-zinc-200 bg-white p-5 transition hover:border-zinc-400 dark:border-zinc-800 dark:bg-zinc-900 dark:hover:border-zinc-600">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0 flex-1">
                                    <div class="flex flex-wrap items-center gap-2 text-xs text-zinc-500 dark:text-zinc-400">
                                        <x-workspace-icon :workspace="$task->project->workspace" size="sm" />
                                        <span>{{ $task->project->workspace->name }}</span>
                                        <span>•</span>
                                        <span>{{ $task->project->name }}</span>
                                    </div>
                                    <h3 class="mt-3 text-lg font-semibold text-zinc-900 dark:text-zinc-100">{{ $task->title }}</h3>
                                    <p class="mt-2 line-clamp-3 text-sm leading-6 text-zinc-600 dark:text-zinc-400">
                                        {{ $task->description ?: 'Aucune description pour cette tache.' }}
                                    </p>
                                </div>

                                <a
                                    href="{{ route('tasks.show', $task) }}"
                                    wire:navigate
                                    class="inline-flex shrink-0 items-center justify-center rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm font-medium text-zinc-700 transition hover:bg-zinc-100 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-200 dark:hover:bg-zinc-800"
                                >
                                    Voir
                                </a>
                            </div>

                            <div class="mt-4 flex flex-wrap items-center gap-2">
                                <span class="inline-flex rounded-full border px-3 py-1 text-xs font-medium {{ $statusThemes[$task->status] ?? $statusThemes[\App\Models\Task::STATUS_TODO] }}">
                                    {{ $statusLabels[$task->status] ?? $task->status }}
                                </span>
                                <span class="inline-flex rounded-full border px-3 py-1 text-xs font-medium {{ $priorityThemes[$task->priority] ?? $priorityThemes[\App\Models\Task::PRIORITY_MEDIUM] }}">
                                    Priorite {{ $priorityLabels[$task->priority] ?? $task->priority }}
                                </span>
                                @if ($task->due_date)
                                    <span @class([
                                        'inline-flex rounded-full border px-3 py-1 text-xs font-medium',
                                        'border-red-300 bg-red-50 text-red-700 dark:border-red-900/70 dark:bg-red-900/20 dark:text-red-300' => $isOverdue,
                                        'border-amber-300 bg-amber-50 text-amber-700 dark:border-amber-900/70 dark:bg-amber-900/20 dark:text-amber-300' => ! $isOverdue && $isToday,
                                        'border-zinc-300 bg-zinc-50 text-zinc-700 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-200' => ! $isOverdue && ! $isToday,
                                    ])>
                                        {{ $isOverdue ? 'En retard' : ($isToday ? 'Aujourd hui' : 'Echeance') }} {{ $task->due_date->format('d/m/Y') }}
                                    </span>
                                @else
                                    <span class="inline-flex rounded-full border border-zinc-300 bg-zinc-50 px-3 py-1 text-xs font-medium text-zinc-700 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-200">
                                        Sans date
                                    </span>
                                @endif
                            </div>

                            <div class="mt-5">
                                <div class="flex items-center justify-between text-xs text-zinc-500 dark:text-zinc-400">
                                    <span>Avancement</span>
                                    <span>{{ \App\Models\Task::progressPercentage($task->status) }}%</span>
                                </div>
                                <div class="mt-2 h-2 rounded-full bg-zinc-100 dark:bg-zinc-800">
                                    <div class="h-2 rounded-full bg-zinc-900 dark:bg-zinc-100" style="width: {{ \App\Models\Task::progressPercentage($task->status) }}%"></div>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            @else
                <div class="rounded-2xl border border-dashed border-zinc-300 bg-white p-10 text-center dark:border-zinc-700 dark:bg-zinc-900">
                    <h3 class="text-base font-semibold text-zinc-900 dark:text-zinc-100">Aucune tache trouvee</h3>
                    <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">
                        Essayez d enlever un filtre ou d ouvrir le calendrier pour retrouver une echeance precise.
                    </p>
                </div>
            @endif
        </section>

        <div>
            {{ $tasks->links() }}
        </div>
    </div>
</x-layouts::app>
