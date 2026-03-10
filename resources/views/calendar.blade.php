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

<x-layouts::app title="Calendrier">
    <div class="mx-auto w-full max-w-6xl space-y-6 p-4 sm:p-6 lg:p-8">
        <section class="rounded-2xl border border-zinc-200 bg-white p-6 dark:border-zinc-800 dark:bg-zinc-900">
            <h1 class="text-2xl font-semibold text-zinc-900 dark:text-zinc-100">Calendrier</h1>
            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                Regroupement de vos taches assignees par date d echeance.
            </p>
        </section>

        <section class="space-y-4">
            @forelse ($tasksByDate as $date => $tasks)
                <div class="rounded-2xl border border-zinc-200 bg-white p-5 dark:border-zinc-800 dark:bg-zinc-900">
                    <h2 class="text-base font-semibold text-zinc-900 dark:text-zinc-100">
                        {{ \Illuminate\Support\Carbon::parse($date)->locale('fr')->isoFormat('dddd D MMMM YYYY') }}
                    </h2>

                    <div class="mt-4 space-y-3">
                        @foreach ($tasks as $task)
                            <article class="rounded-xl border border-zinc-200 p-4 dark:border-zinc-800">
                                <div class="flex flex-wrap items-start justify-between gap-2">
                                    <div>
                                        <p class="font-medium text-zinc-900 dark:text-zinc-100">{{ $task->title }}</p>
                                        <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">{{ $task->project->name }}</p>
                                    </div>
                                    <div class="flex flex-wrap gap-2">
                                        <span class="rounded-full border border-zinc-300 px-2.5 py-1 text-xs font-medium text-zinc-700 dark:border-zinc-700 dark:text-zinc-200">
                                            {{ $statusLabels[$task->status] ?? $task->status }}
                                        </span>
                                        <span class="rounded-full border border-zinc-300 px-2.5 py-1 text-xs font-medium text-zinc-700 dark:border-zinc-700 dark:text-zinc-200">
                                            Priorite {{ $priorityLabels[$task->priority] ?? $task->priority }}
                                        </span>
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </div>
            @empty
                <div class="rounded-2xl border border-dashed border-zinc-300 bg-white p-8 text-center text-sm text-zinc-600 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-400">
                    Aucune tache avec date d echeance.
                </div>
            @endforelse
        </section>
    </div>
</x-layouts::app>
