@php
    use App\Models\Task;

    $statusLabels = [
        Task::STATUS_TODO => 'A faire',
        Task::STATUS_IN_PROGRESS => 'En cours',
        Task::STATUS_DONE => 'Terminee',
    ];

    $priorityClasses = [
        Task::PRIORITY_LOW => 'border-zinc-200 bg-zinc-50 text-zinc-700 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-200',
        Task::PRIORITY_MEDIUM => 'border-blue-200 bg-blue-50 text-blue-700 dark:border-blue-900/40 dark:bg-blue-950/20 dark:text-blue-300',
        Task::PRIORITY_HIGH => 'border-red-200 bg-red-50 text-red-700 dark:border-red-900/40 dark:bg-red-950/20 dark:text-red-300',
    ];

    $weekdayLabels = ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'];

    $days = collect();

    for ($date = $gridStart->copy(); $date->lte($gridEnd); $date = $date->addDay()) {
        $days->push($date->copy());
    }

    $weeks = $days->chunk(7);

    $monthStart = $selectedMonth->copy()->startOfMonth();
    $monthEnd = $selectedMonth->copy()->endOfMonth();

    $monthTasks = $tasksByDate
        ->flatten(1)
        ->filter(fn (Task $task) => $task->due_date && $task->due_date->betweenIncluded($monthStart, $monthEnd));

    $today = today();
    $monthOverdueCount = $monthTasks->filter(fn (Task $task) => $task->due_date && $task->due_date->isBefore($today) && $task->status !== Task::STATUS_DONE)->count();
    $monthTodayCount = $monthTasks->filter(fn (Task $task) => $task->due_date && $task->due_date->isSameDay($today))->count();
    $monthDoneCount = $monthTasks->where('status', Task::STATUS_DONE)->count();
@endphp

<x-layouts::app title="Calendrier">
    <div class="mx-auto w-full max-w-7xl space-y-6 p-4 sm:p-6 lg:p-8">
        <section class="rounded-2xl border border-zinc-200 bg-white p-6 dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-semibold text-zinc-900 dark:text-zinc-100">Calendrier</h1>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <a
                        href="{{ route('calendar', ['month' => $selectedMonth->copy()->subMonth()->format('Y-m')]) }}"
                        wire:navigate
                        class="inline-flex items-center rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm font-medium text-zinc-700 transition hover:bg-zinc-100 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-200 dark:hover:bg-zinc-800"
                    >
                        Mois precedent
                    </a>

                    <a
                        href="{{ route('calendar', ['month' => today()->format('Y-m')]) }}"
                        wire:navigate
                        class="inline-flex items-center rounded-lg border border-black bg-black px-3 py-2 text-sm font-medium text-white transition hover:bg-zinc-800 dark:border-zinc-100 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200"
                    >
                        Aujourd hui
                    </a>

                    <a
                        href="{{ route('calendar', ['month' => $selectedMonth->copy()->addMonth()->format('Y-m')]) }}"
                        wire:navigate
                        class="inline-flex items-center rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm font-medium text-zinc-700 transition hover:bg-zinc-100 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-200 dark:hover:bg-zinc-800"
                    >
                        Mois suivant
                    </a>
                </div>
            </div>

            <div class="mt-5 flex flex-wrap items-end justify-between gap-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-zinc-500 dark:text-zinc-400">Mois affiche</p>
                    <h2 class="mt-1 text-3xl font-semibold text-zinc-900 dark:text-zinc-100">
                        {{ ucfirst($selectedMonth->locale('fr')->isoFormat('MMMM YYYY')) }}
                    </h2>
                </div>

                <div class="grid gap-3 sm:grid-cols-3">
                    <article class="rounded-xl border border-zinc-200 bg-zinc-50 px-4 py-3 dark:border-zinc-800 dark:bg-zinc-950/60">
                        <p class="text-[11px] uppercase tracking-[0.18em] text-zinc-500 dark:text-zinc-400">Aujourd hui</p>
                        <p class="mt-2 text-2xl font-semibold text-zinc-900 dark:text-zinc-100">{{ $monthTodayCount }}</p>
                    </article>
                    <article class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 dark:border-red-900/40 dark:bg-red-950/20">
                        <p class="text-[11px] uppercase tracking-[0.18em] text-red-600 dark:text-red-400">En retard</p>
                        <p class="mt-2 text-2xl font-semibold text-red-700 dark:text-red-300">{{ $monthOverdueCount }}</p>
                    </article>
                    <article class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 dark:border-emerald-900/40 dark:bg-emerald-950/20">
                        <p class="text-[11px] uppercase tracking-[0.18em] text-emerald-600 dark:text-emerald-400">Terminees</p>
                        <p class="mt-2 text-2xl font-semibold text-emerald-700 dark:text-emerald-300">{{ $monthDoneCount }}</p>
                    </article>
                </div>
            </div>
        </section>

        <section class="overflow-x-auto">
            <div class="min-w-[980px] rounded-2xl border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
                <div class="grid grid-cols-7 border-b border-zinc-200 dark:border-zinc-800">
                    @foreach ($weekdayLabels as $weekdayLabel)
                        <div class="px-4 py-3 text-xs font-semibold uppercase tracking-[0.18em] text-zinc-500 dark:text-zinc-400">
                            {{ $weekdayLabel }}
                        </div>
                    @endforeach
                </div>

                @foreach ($weeks as $week)
                    <div class="grid grid-cols-7">
                        @foreach ($week as $day)
                            @php
                                $dateKey = $day->format('Y-m-d');
                                $dayTasks = $tasksByDate->get($dateKey, collect());
                                $isCurrentMonth = $day->month === $selectedMonth->month;
                                $isToday = $day->isSameDay($today);
                                $extraTasksCount = max($dayTasks->count() - 3, 0);
                            @endphp

                            <div @class([
                                'min-h-44 border-b border-r border-zinc-200 p-3 align-top dark:border-zinc-800',
                                'bg-white dark:bg-zinc-900' => $isCurrentMonth,
                                'bg-zinc-50/70 dark:bg-zinc-950/60' => ! $isCurrentMonth,
                            ])>
                                <div class="mb-3 flex items-center justify-between gap-2">
                                    <span @class([
                                        'inline-flex h-8 w-8 items-center justify-center rounded-full text-sm font-semibold',
                                        'bg-black text-white dark:bg-zinc-100 dark:text-zinc-900' => $isToday,
                                        'text-zinc-900 dark:text-zinc-100' => ! $isToday && $isCurrentMonth,
                                        'text-zinc-400 dark:text-zinc-500' => ! $isToday && ! $isCurrentMonth,
                                    ])>
                                        {{ $day->day }}
                                    </span>

                                    @if ($dayTasks->isNotEmpty())
                                        <span class="text-[11px] font-medium text-zinc-500 dark:text-zinc-400">
                                            {{ $dayTasks->count() }} tache{{ $dayTasks->count() > 1 ? 's' : '' }}
                                        </span>
                                    @endif
                                </div>

                                <div class="space-y-2">
                                    @foreach ($dayTasks->take(3) as $task)
                                        <a
                                            href="{{ route('tasks.show', $task) }}"
                                            wire:navigate
                                            class="block rounded-xl border px-3 py-2 transition hover:border-zinc-400 hover:bg-zinc-50 dark:hover:border-zinc-600 dark:hover:bg-zinc-950"
                                            @class([$priorityClasses[$task->priority] ?? $priorityClasses[Task::PRIORITY_LOW]])
                                        >
                                            <p class="truncate text-sm font-medium">{{ $task->title }}</p>
                                            <p class="mt-1 truncate text-[11px] opacity-80">
                                                {{ $task->project->name }}
                                            </p>
                                            <p class="mt-1 text-[11px] opacity-70">
                                                {{ $statusLabels[$task->status] ?? $task->status }}
                                            </p>
                                        </a>
                                    @endforeach

                                    @if ($extraTasksCount > 0)
                                        <div class="rounded-xl border border-dashed border-zinc-200 px-3 py-2 text-xs font-medium text-zinc-500 dark:border-zinc-700 dark:text-zinc-400">
                                            + {{ $extraTasksCount }} autre{{ $extraTasksCount > 1 ? 's' : '' }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endforeach
            </div>
        </section>

        @if ($monthTasks->isEmpty())
            <section class="rounded-2xl border border-dashed border-zinc-300 bg-white p-8 text-center dark:border-zinc-700 dark:bg-zinc-900">
                <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">Aucune echeance pour ce mois</h3>
                <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">
                    Ce mois ne contient aucune tache assignee avec date d’echeance.
                </p>
            </section>
        @endif
    </div>
</x-layouts::app>
