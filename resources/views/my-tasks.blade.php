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
@endphp

<x-layouts::app title="Mes taches">
    <div class="mx-auto w-full max-w-6xl space-y-6 p-4 sm:p-6 lg:p-8">
        <section class="rounded-2xl border border-zinc-200 bg-white p-6 dark:border-zinc-800 dark:bg-zinc-900">
            <h1 class="text-2xl font-semibold text-zinc-900 dark:text-zinc-100">Mes taches</h1>
            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                Liste des taches qui vous sont assignees avec filtres rapides.
            </p>

            <form method="GET" action="{{ route('my-tasks') }}" class="mt-5 grid gap-3 md:grid-cols-4">
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
                        Filtrer
                    </button>
                    <a
                        href="{{ route('my-tasks') }}"
                        wire:navigate
                        class="inline-flex items-center justify-center rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm font-medium text-zinc-700 transition hover:bg-zinc-100 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-200 dark:hover:bg-zinc-800"
                    >
                        Reinitialiser
                    </a>
                </div>
            </form>
        </section>

        <section class="space-y-3">
            @forelse ($tasks as $task)
                <article class="rounded-2xl border border-zinc-200 bg-white p-5 dark:border-zinc-800 dark:bg-zinc-900">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <h2 class="text-base font-semibold text-zinc-900 dark:text-zinc-100">{{ $task->title }}</h2>
                            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                                {{ $task->project->workspace->name }} . {{ $task->project->name }}
                            </p>
                            @if ($task->description)
                                <p class="mt-2 text-sm text-zinc-700 dark:text-zinc-300">{{ $task->description }}</p>
                            @endif
                        </div>

                        <div class="flex flex-wrap items-center gap-2">
                            <span class="rounded-full border border-zinc-300 px-2.5 py-1 text-xs font-medium text-zinc-700 dark:border-zinc-700 dark:text-zinc-200">
                                {{ $statusLabels[$task->status] ?? $task->status }}
                            </span>
                            <span class="rounded-full border border-zinc-300 px-2.5 py-1 text-xs font-medium text-zinc-700 dark:border-zinc-700 dark:text-zinc-200">
                                Priorite {{ $priorityLabels[$task->priority] ?? $task->priority }}
                            </span>
                            <span class="rounded-full border border-zinc-300 px-2.5 py-1 text-xs font-medium text-zinc-700 dark:border-zinc-700 dark:text-zinc-200">
                                {{ $task->due_date ? 'Echeance '.$task->due_date->format('d/m/Y') : 'Sans date' }}
                            </span>
                        </div>
                    </div>
                </article>
            @empty
                <div class="rounded-2xl border border-dashed border-zinc-300 bg-white p-8 text-center text-sm text-zinc-600 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-400">
                    Aucune tache trouvee pour ces filtres.
                </div>
            @endforelse
        </section>

        <div>
            {{ $tasks->links() }}
        </div>
    </div>
</x-layouts::app>
