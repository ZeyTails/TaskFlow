<div class="mx-auto w-full max-w-6xl space-y-6">
    <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-800 dark:bg-zinc-900">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="space-y-2">
                <div class="space-y-[2px]">
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-zinc-500 dark:text-zinc-400">
                        {{ $task->project->workspace->name }} / {{ $task->project->name }}
                    </p>
                    <h1 class="text-2xl font-semibold text-zinc-900 dark:text-zinc-100">{{ $task->title }}</h1>
                </div>

                <p class="max-w-3xl text-sm leading-6 text-zinc-600 dark:text-zinc-400">
                    {{ $task->description ?: 'Aucune description pour cette tache.' }}
                </p>
            </div>

            <a
                href="{{ route('workspaces.projects.tasks.index', [$task->project->workspace, $task->project]) }}"
                wire:navigate
                class="text-sm font-medium text-zinc-700 transition hover:text-zinc-900 dark:text-zinc-300 dark:hover:text-zinc-100"
            >
                Retour aux taches
            </a>
        </div>

        <div class="mt-5 flex flex-wrap gap-2">
            <span class="inline-flex rounded-full border border-zinc-200 px-3 py-1 text-xs font-medium text-zinc-700 dark:border-zinc-700 dark:text-zinc-200">
                Statut: {{ str_replace('_', ' ', $task->status) }}
            </span>
            <span class="inline-flex rounded-full border border-zinc-200 px-3 py-1 text-xs font-medium text-zinc-700 dark:border-zinc-700 dark:text-zinc-200">
                Priorite: {{ ucfirst($task->priority) }}
            </span>
            <span class="inline-flex rounded-full border border-zinc-200 px-3 py-1 text-xs font-medium text-zinc-700 dark:border-zinc-700 dark:text-zinc-200">
                Echeance: {{ $task->due_date?->format('d/m/Y') ?? 'Non definie' }}
            </span>
            <span class="inline-flex rounded-full border border-zinc-200 px-3 py-1 text-xs font-medium text-zinc-700 dark:border-zinc-700 dark:text-zinc-200">
                Assigne: {{ $task->assignee?->name ?? 'Non assigne' }}
            </span>
        </div>

        <div class="mt-5 grid gap-3 text-sm text-zinc-600 dark:text-zinc-400 md:grid-cols-3">
            <div class="rounded-lg border border-zinc-200 bg-zinc-50 px-4 py-3 dark:border-zinc-800 dark:bg-zinc-950/60">
                <p class="text-xs uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Creee par</p>
                <p class="mt-1 font-medium text-zinc-900 dark:text-zinc-100">{{ $task->creator->name }}</p>
            </div>
            <div class="rounded-lg border border-zinc-200 bg-zinc-50 px-4 py-3 dark:border-zinc-800 dark:bg-zinc-950/60">
                <p class="text-xs uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Projet</p>
                <p class="mt-1 font-medium text-zinc-900 dark:text-zinc-100">{{ $task->project->name }}</p>
            </div>
            <div class="rounded-lg border border-zinc-200 bg-zinc-50 px-4 py-3 dark:border-zinc-800 dark:bg-zinc-950/60">
                <p class="text-xs uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Derniere mise a jour</p>
                <p class="mt-1 font-medium text-zinc-900 dark:text-zinc-100">{{ $task->updated_at->format('d/m/Y H:i') }}</p>
            </div>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-[minmax(0,1.15fr)_minmax(340px,0.85fr)]">
        <div class="space-y-6">
            <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-800 dark:bg-zinc-900">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <h2 class="text-base font-semibold text-zinc-900 dark:text-zinc-100">Commentaires</h2>
                        <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">Echanges autour de la tache.</p>
                    </div>
                    <span class="inline-flex rounded-full border border-zinc-200 px-2.5 py-1 text-xs font-medium text-zinc-700 dark:border-zinc-700 dark:text-zinc-200">
                        {{ $task->comments->count() }} commentaire{{ $task->comments->count() > 1 ? 's' : '' }}
                    </span>
                </div>

                @if (session('comment-status'))
                    <div class="mt-4 rounded-lg border border-green-200 bg-green-50 px-3 py-2 text-sm text-green-700 dark:border-green-900/60 dark:bg-green-900/20 dark:text-green-300">
                        {{ session('comment-status') }}
                    </div>
                @endif

                @if ($canComment)
                    <form wire:submit="addComment" class="mt-5 space-y-3">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Nouveau commentaire</label>
                            <textarea
                                wire:model="commentContent"
                                rows="4"
                                class="w-full rounded-xl border border-zinc-300 bg-white px-4 py-3 text-sm text-zinc-900 outline-none transition focus:border-zinc-400 focus:ring-0 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100 dark:focus:border-zinc-500"
                                placeholder="Ajouter un commentaire utile pour l'equipe"
                            ></textarea>
                            @error('commentContent')
                                <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex justify-end">
                            <button
                                type="submit"
                                class="inline-flex items-center justify-center rounded-lg border border-black bg-black px-4 py-2.5 text-sm font-medium text-white transition hover:bg-zinc-800 dark:border-zinc-100 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200"
                            >
                                Publier
                            </button>
                        </div>
                    </form>
                @else
                    <div class="mt-5 rounded-lg border border-zinc-200 bg-zinc-50 px-3 py-2 text-sm text-zinc-600 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">
                        Vous pouvez consulter les commentaires, mais pas en ajouter.
                    </div>
                @endif

                <div class="mt-6 space-y-4">
                    @forelse ($task->comments->sortBy('created_at') as $comment)
                        <article class="rounded-xl border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-800 dark:bg-zinc-950/60">
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex min-w-0 items-start gap-3">
                                    @php
                                        $avatarUrl = null;

                                        if ($comment->user->avatar_path) {
                                            $avatarUrl = \Illuminate\Support\Str::startsWith($comment->user->avatar_path, ['http://', 'https://'])
                                                ? $comment->user->avatar_path
                                                : \Illuminate\Support\Facades\Storage::url($comment->user->avatar_path);
                                        }
                                    @endphp

                                    @if ($avatarUrl)
                                        <img src="{{ $avatarUrl }}" alt="{{ $comment->user->name }}" class="h-10 w-10 rounded-full object-cover">
                                    @else
                                        <div class="flex h-10 w-10 items-center justify-center rounded-full border border-zinc-200 bg-white text-xs font-semibold text-zinc-700 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-200">
                                            {{ $comment->user->initials() }}
                                        </div>
                                    @endif

                                    <div class="min-w-0">
                                        <div class="flex flex-wrap items-center gap-x-2 gap-y-1">
                                            <p class="font-medium text-zinc-900 dark:text-zinc-100">{{ $comment->user->name }}</p>
                                            <span class="text-xs text-zinc-500 dark:text-zinc-400">{{ $comment->created_at->format('d/m/Y H:i') }}</span>
                                        </div>
                                        <p class="mt-2 whitespace-pre-line text-sm leading-6 text-zinc-700 dark:text-zinc-300">{{ $comment->content }}</p>
                                    </div>
                                </div>

                                @can('delete', $comment)
                                    <button
                                        type="button"
                                        wire:click="deleteComment({{ $comment->id }})"
                                        wire:confirm="Supprimer ce commentaire ?"
                                        class="inline-flex shrink-0 items-center justify-center rounded-lg border border-red-300 bg-red-50 px-3 py-2 text-xs font-medium text-red-700 transition hover:bg-red-100 dark:border-red-900/60 dark:bg-red-900/20 dark:text-red-300"
                                    >
                                        Supprimer
                                    </button>
                                @endcan
                            </div>
                        </article>
                    @empty
                        <div class="rounded-xl border border-dashed border-zinc-200 px-4 py-8 text-center text-sm text-zinc-600 dark:border-zinc-800 dark:text-zinc-400">
                            Aucun commentaire pour le moment.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-800 dark:bg-zinc-900">
                <div>
                    <h2 class="text-base font-semibold text-zinc-900 dark:text-zinc-100">Mise a jour rapide</h2>
                    <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">Statut, priorite, echeance et assignee.</p>
                </div>

                @if (session('task-status'))
                    <div class="mt-4 rounded-lg border border-green-200 bg-green-50 px-3 py-2 text-sm text-green-700 dark:border-green-900/60 dark:bg-green-900/20 dark:text-green-300">
                        {{ session('task-status') }}
                    </div>
                @endif

                @if ($canUpdate)
                    <form wire:submit="updateTask" class="mt-5 space-y-4">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Statut</label>
                            <select
                                wire:model="status"
                                class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100"
                            >
                                <option value="todo">Todo</option>
                                <option value="in_progress">In progress</option>
                                <option value="done">Done</option>
                            </select>
                            @error('status')
                                <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Priorite</label>
                            <select
                                wire:model="priority"
                                class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100"
                            >
                                <option value="low">Low</option>
                                <option value="medium">Medium</option>
                                <option value="high">High</option>
                            </select>
                            @error('priority')
                                <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Echeance</label>
                            <input
                                wire:model="dueDate"
                                type="date"
                                class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100"
                            >
                            @error('dueDate')
                                <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Assignee</label>
                            <select
                                wire:model="assigneeId"
                                class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100"
                            >
                                <option value="">Non assigne</option>
                                @foreach ($members as $member)
                                    <option value="{{ $member->id }}">{{ $member->name }}</option>
                                @endforeach
                            </select>
                            @error('assigneeId')
                                <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <button
                            type="submit"
                            class="inline-flex w-full items-center justify-center rounded-lg border border-black bg-black px-4 py-2.5 text-sm font-medium text-white transition hover:bg-zinc-800 dark:border-zinc-100 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200"
                        >
                            Enregistrer les changements
                        </button>
                    </form>
                @else
                    <div class="mt-5 rounded-lg border border-zinc-200 bg-zinc-50 px-3 py-2 text-sm text-zinc-600 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">
                        Vous avez un acces en lecture seule sur cette tache.
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
