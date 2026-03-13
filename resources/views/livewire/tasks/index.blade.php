<div class="mx-auto w-full max-w-6xl space-y-6">
    <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-800 dark:bg-zinc-900">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="text-xl font-semibold text-zinc-900 dark:text-zinc-100">{{ $project->name }}</h1>
                <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">Taches du projet</p>
            </div>
            <a
                href="{{ route('workspaces.projects.index', $workspace) }}"
                wire:navigate
                class="text-sm font-medium text-zinc-700 hover:text-zinc-900 dark:text-zinc-300 dark:hover:text-zinc-100"
            >
                Retour aux projets
            </a>
        </div>

        @if (session('status'))
            <div class="mt-4 rounded-lg border border-green-200 bg-green-50 px-3 py-2 text-sm text-green-700 dark:border-green-900/60 dark:bg-green-900/20 dark:text-green-300">
                {{ session('status') }}
            </div>
        @endif

        @if ($canWrite)
            <form wire:submit="createTask" class="mt-5 grid gap-3 md:grid-cols-[1.2fr_1.2fr_140px_140px_180px_180px_auto] md:items-end">
                <flux:input
                    wire:model="title"
                    label="Titre"
                    type="text"
                    required
                    placeholder="Ex: Integrer API"
                />

                <flux:input
                    wire:model="description"
                    label="Description"
                    type="text"
                    placeholder="Details (optionnel)"
                />

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
                </div>

                <flux:input
                    wire:model="dueDate"
                    label="Echeance"
                    type="date"
                />

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
                </div>

                <button
                    type="submit"
                    class="inline-flex items-center justify-center rounded-lg border border-black bg-black px-3 py-2.5 text-sm font-medium text-white transition hover:bg-zinc-800 dark:border-zinc-100 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200"
                >
                    Ajouter
                </button>
            </form>
        @else
            <div class="mt-5 rounded-lg border border-zinc-200 bg-zinc-50 px-3 py-2 text-sm text-zinc-600 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">
                Vous avez un acces en lecture seule sur ce projet.
            </div>
        @endif
    </div>

    <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-800 dark:bg-zinc-900">
        <h2 class="text-sm font-semibold uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Recherche et filtres</h2>
        <div class="mt-3 grid gap-3 md:grid-cols-[1fr_180px_180px_220px]">
            <flux:input
                wire:model.live.debounce.300ms="search"
                label="Recherche"
                type="text"
                placeholder="Titre"
            />

            <div>
                <label class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Statut</label>
                <select
                    wire:model.live="filterStatus"
                    class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100"
                >
                    <option value="all">Tous</option>
                    <option value="todo">Todo</option>
                    <option value="in_progress">In progress</option>
                    <option value="done">Done</option>
                </select>
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Priorite</label>
                <select
                    wire:model.live="filterPriority"
                    class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100"
                >
                    <option value="all">Toutes</option>
                    <option value="low">Low</option>
                    <option value="medium">Medium</option>
                    <option value="high">High</option>
                </select>
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Assignee</label>
                <select
                    wire:model.live="filterAssignee"
                    class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100"
                >
                    <option value="">Tous</option>
                    @foreach ($members as $member)
                        <option value="{{ $member->id }}">{{ $member->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <div class="rounded-xl border border-zinc-200 bg-white p-2 dark:border-zinc-800 dark:bg-zinc-900">
        <div class="overflow-x-auto">
            <table class="min-w-full border-collapse">
                <thead>
                    <tr class="text-left text-xs uppercase tracking-wide text-zinc-500 dark:text-zinc-400">
                        <th class="px-4 py-3">Titre</th>
                        <th class="px-4 py-3">Assignee</th>
                        <th class="px-4 py-3">Statut</th>
                        <th class="px-4 py-3">Priorite</th>
                        <th class="px-4 py-3">Echeance</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($tasks as $task)
                        <tr class="border-t border-zinc-200 text-sm dark:border-zinc-800">
                            @if ($editingTaskId === $task->id)
                                <td class="px-4 py-3" colspan="6">
                                    <form wire:submit="updateTask" class="grid gap-3 md:grid-cols-[1.2fr_1.2fr_140px_140px_180px_180px_auto_auto] md:items-end">
                                        <flux:input wire:model="editTitle" label="Titre" type="text" required />
                                        <flux:input wire:model="editDescription" label="Description" type="text" />

                                        <div>
                                            <label class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Statut</label>
                                            <select
                                                wire:model="editStatus"
                                                class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100"
                                            >
                                                <option value="todo">Todo</option>
                                                <option value="in_progress">In progress</option>
                                                <option value="done">Done</option>
                                            </select>
                                        </div>

                                        <div>
                                            <label class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Priorite</label>
                                            <select
                                                wire:model="editPriority"
                                                class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100"
                                            >
                                                <option value="low">Low</option>
                                                <option value="medium">Medium</option>
                                                <option value="high">High</option>
                                            </select>
                                        </div>

                                        <flux:input wire:model="editDueDate" label="Echeance" type="date" />

                                        <div>
                                            <label class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Assignee</label>
                                            <select
                                                wire:model="editAssigneeId"
                                                class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100"
                                            >
                                                <option value="">Non assigne</option>
                                                @foreach ($members as $member)
                                                    <option value="{{ $member->id }}">{{ $member->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <button
                                            type="submit"
                                            class="inline-flex items-center justify-center rounded-lg border border-black bg-black px-3 py-2 text-sm font-medium text-white transition hover:bg-zinc-800"
                                        >
                                            Enregistrer
                                        </button>

                                        <button
                                            type="button"
                                            wire:click="cancelEditing"
                                            class="inline-flex items-center justify-center rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm font-medium text-zinc-700 transition hover:bg-zinc-100 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-200 dark:hover:bg-zinc-800"
                                        >
                                            Annuler
                                        </button>
                                    </form>
                                </td>
                            @else
                                <td class="px-4 py-3">
                                    <div class="font-medium text-zinc-900 dark:text-zinc-100">{{ $task->title }}</div>
                                    <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ $task->description ?: 'Sans description' }}</div>
                                </td>
                                <td class="px-4 py-3 text-zinc-600 dark:text-zinc-400">
                                    {{ $task->assignee?->name ?? 'Non assigne' }}
                                </td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex rounded-full border border-zinc-200 px-2.5 py-1 text-xs font-medium text-zinc-700 dark:border-zinc-700 dark:text-zinc-200">
                                        {{ str_replace('_', ' ', $task->status) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex rounded-full border border-zinc-200 px-2.5 py-1 text-xs font-medium text-zinc-700 dark:border-zinc-700 dark:text-zinc-200">
                                        {{ ucfirst($task->priority) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-zinc-600 dark:text-zinc-400">
                                    {{ $task->due_date?->format('d/m/Y') ?? '—' }}
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <div class="flex justify-end gap-2">
                                        <a
                                            href="{{ route('tasks.show', $task) }}"
                                            wire:navigate
                                            class="inline-flex items-center justify-center rounded-lg border border-zinc-300 bg-white px-3 py-2 text-xs font-medium text-zinc-700 transition hover:bg-zinc-100 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-200 dark:hover:bg-zinc-800"
                                        >
                                            Voir
                                        </a>
                                        @if ($canWrite)
                                            <button
                                                type="button"
                                                wire:click="startEditing({{ $task->id }})"
                                                class="inline-flex items-center justify-center rounded-lg border border-zinc-300 bg-white px-3 py-2 text-xs font-medium text-zinc-700 transition hover:bg-zinc-100 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-200 dark:hover:bg-zinc-800"
                                            >
                                                Modifier
                                            </button>
                                            <button
                                                type="button"
                                                wire:click="deleteTask({{ $task->id }})"
                                                wire:confirm="Supprimer cette tache ?"
                                                class="inline-flex items-center justify-center rounded-lg border border-red-300 bg-red-50 px-3 py-2 text-xs font-medium text-red-700 transition hover:bg-red-100 dark:border-red-900/60 dark:bg-red-900/20 dark:text-red-300"
                                            >
                                                Supprimer
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-10 text-center text-sm text-zinc-600 dark:text-zinc-400">
                                Aucune tache pour le moment.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
