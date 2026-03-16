@php
    $selectedIds = collect($selected ?? [])->map(fn ($id) => (int) $id)->all();
    $selectedMembers = $members->filter(fn ($member) => in_array($member->id, $selectedIds, true))->values();
@endphp

<div class="space-y-3">
    <div class="flex items-center justify-between gap-3">
        <div>
            <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $label ?? 'Personnes assignees' }}</p>
            <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                {{ $selectedMembers->count() > 0 ? $selectedMembers->count().' personne(s) selectionnee(s)' : 'Aucune personne selectionnee' }}
            </p>
        </div>

        <flux:modal.trigger :name="$modalName">
            <button
                type="button"
                class="inline-flex items-center justify-center rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm font-medium text-zinc-700 transition hover:bg-zinc-100 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100 dark:hover:bg-zinc-800"
            >
                Choisir
            </button>
        </flux:modal.trigger>
    </div>

    @if ($selectedMembers->isNotEmpty())
        <div class="flex flex-wrap gap-2">
            @foreach ($selectedMembers as $member)
                <span class="inline-flex items-center gap-2 rounded-full border border-zinc-200 bg-zinc-50 px-2.5 py-1 text-xs font-medium text-zinc-700 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-200">
                    <span class="inline-flex h-5 w-5 items-center justify-center rounded-full bg-zinc-900 text-[10px] font-semibold text-white dark:bg-zinc-100 dark:text-zinc-900">
                        {{ $member->initials() }}
                    </span>
                    <span>
                        {{ $member->name }}
                        @if ($member->pivot->job_title)
                            <span class="text-zinc-500 dark:text-zinc-400">· {{ $member->pivot->job_title }}</span>
                        @endif
                    </span>
                </span>
            @endforeach
        </div>
    @endif

    @error($errorKey)
        <p class="text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
    @enderror
    @error($errorItemKey)
        <p class="text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
    @enderror
</div>
