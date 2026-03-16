@php
    $selectedIds = collect($selected ?? [])->map(fn ($id) => (int) $id)->all();
@endphp

<div class="space-y-3">
    <div class="flex items-center justify-between gap-3">
        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ $label ?? 'Assignes' }}</label>
        <span class="text-xs text-zinc-500 dark:text-zinc-400">
            {{ count($selectedIds) > 0 ? count($selectedIds).' selection(s)' : 'Aucune selection' }}
        </span>
    </div>

    @if (count($selectedIds) > 0)
        <div class="flex flex-wrap gap-2">
            @foreach ($members as $member)
                @if (in_array($member->id, $selectedIds, true))
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
                @endif
            @endforeach
        </div>
    @endif

    <div class="grid gap-2 sm:grid-cols-2">
        @foreach ($members as $member)
            @php
                $avatarUrl = null;

                if ($member->avatar_path) {
                    $avatarUrl = \Illuminate\Support\Str::startsWith($member->avatar_path, ['http://', 'https://'])
                        ? $member->avatar_path
                        : \Illuminate\Support\Facades\Storage::url($member->avatar_path);
                }

                $isSelected = in_array($member->id, $selectedIds, true);
            @endphp

            <label class="flex cursor-pointer items-center gap-3 rounded-xl border px-3 py-3 transition {{ $isSelected ? 'border-black bg-zinc-50 dark:border-zinc-100 dark:bg-zinc-800/80' : 'border-zinc-200 bg-white hover:border-zinc-400 dark:border-zinc-700 dark:bg-zinc-900 dark:hover:border-zinc-500' }}">
                <input
                    type="checkbox"
                    wire:model.live="{{ $model }}"
                    value="{{ $member->id }}"
                    class="h-4 w-4 rounded border-zinc-300 text-black focus:ring-black dark:border-zinc-600 dark:bg-zinc-900 dark:text-white dark:focus:ring-zinc-100"
                    @checked($isSelected)
                >

                @if ($avatarUrl)
                    <img
                        src="{{ $avatarUrl }}"
                        alt="Avatar {{ $member->name }}"
                        class="h-9 w-9 rounded-full border border-zinc-200 object-cover dark:border-zinc-700"
                    />
                @else
                    <span class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-zinc-200 bg-zinc-100 text-xs font-semibold text-zinc-700 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-200">
                        {{ $member->initials() }}
                    </span>
                @endif

                <span class="min-w-0">
                    <span class="block truncate text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $member->name }}</span>
                    <span class="block truncate text-xs text-zinc-500 dark:text-zinc-400">
                        {{ $member->pivot->job_title ?: 'Sans titre' }}
                    </span>
                    <span class="block truncate text-[11px] text-zinc-400 dark:text-zinc-500">{{ $member->email }}</span>
                </span>
            </label>
        @endforeach
    </div>

    <p class="text-xs text-zinc-500 dark:text-zinc-400">Clique sur une ou plusieurs cartes pour assigner la tache.</p>

    @error($errorKey)
        <p class="text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
    @enderror
    @error($errorItemKey)
        <p class="text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
    @enderror
</div>
