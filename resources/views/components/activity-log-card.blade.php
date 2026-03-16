@props([
    'activity',
    'dateFormat' => 'd/m/Y H:i',
])

@php
$isCommentActivity = $activity->type === 'comment_added' && $activity->comment;
@endphp

<article class="rounded-xl border border-zinc-200 p-4 dark:border-zinc-800 {{ $isCommentActivity ? 'cursor-pointer transition hover:border-zinc-400 dark:hover:border-zinc-600' : '' }}"
    @if ($isCommentActivity)
        onclick="window.location.href='{{ route('tasks.show', $activity->task) }}'"
    @endif
>
    <div class="flex flex-wrap items-center justify-between gap-2">
        <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $activity->description }}</p>
        <span class="text-xs text-zinc-500 dark:text-zinc-400">{{ $activity->created_at->format($dateFormat) }}</span>
    </div>

    @if ($isCommentActivity)
        <div class="mt-2 rounded-lg border border-zinc-200 bg-zinc-50 px-3 py-2 text-sm text-zinc-700 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-300">
            {{ Str::limit($activity->comment->content, 150) }}
        </div>
    @endif

    <div class="mt-2 flex flex-wrap items-center gap-2 text-xs text-zinc-500 dark:text-zinc-400">
        <span>{{ $activity->actor?->name ?? 'Systeme' }}</span>
        @if ($activity->workspace)
            <span>• {{ $activity->workspace->name }}</span>
        @endif
        @if ($activity->project)
            <span>• {{ $activity->project->name }}</span>
        @endif
        @if ($activity->task)
            <span>• {{ $activity->task->title }}</span>
        @endif
    </div>

    @if (($activity->meta['change_set'] ?? null) && is_array($activity->meta['change_set']))
        <div class="mt-3 space-y-2">
            @foreach ($activity->meta['change_set'] as $change)
                <div class="rounded-lg border border-zinc-200 bg-zinc-50 px-3 py-2 text-xs dark:border-zinc-700 dark:bg-zinc-900">
                    <div class="font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">
                        {{ $change['label'] ?? $change['field'] ?? 'Modification' }}
                    </div>
                    <div class="mt-1 grid gap-2 md:grid-cols-2">
                        <div class="rounded-md border border-zinc-200 bg-white px-2.5 py-2 dark:border-zinc-700 dark:bg-zinc-950/60">
                            <div class="text-[10px] uppercase tracking-wide text-zinc-400 dark:text-zinc-500">Avant</div>
                            <div class="mt-1 text-zinc-700 dark:text-zinc-200">{{ $change['before'] ?? '—' }}</div>
                        </div>
                        <div class="rounded-md border border-zinc-200 bg-white px-2.5 py-2 dark:border-zinc-700 dark:bg-zinc-950/60">
                            <div class="text-[10px] uppercase tracking-wide text-zinc-400 dark:text-zinc-500">Apres</div>
                            <div class="mt-1 text-zinc-700 dark:text-zinc-200">{{ $change['after'] ?? '—' }}</div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @elseif (($activity->meta['changes'] ?? null) && is_array($activity->meta['changes']))
        <div class="mt-3 flex flex-wrap gap-2">
            @foreach ($activity->meta['changes'] as $change)
                <span class="inline-flex rounded-full border border-zinc-200 px-2.5 py-1 text-[11px] font-medium text-zinc-600 dark:border-zinc-700 dark:text-zinc-300">
                    {{ $change }}
                </span>
            @endforeach
        </div>
    @endif
</article>
