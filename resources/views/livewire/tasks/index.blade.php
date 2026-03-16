<div class="mx-auto w-full max-w-6xl space-y-6">
    <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-800 dark:bg-zinc-900">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div class="flex items-start gap-3">
                <x-workspace-icon :workspace="$workspace" size="lg" />
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <h1 class="text-xl font-semibold text-zinc-900 dark:text-zinc-100">{{ $project->name }}</h1>
                        <x-workspace-theme-badge :workspace="$workspace" />
                    </div>
                    <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">Taches du projet</p>
                </div>
            </div>
            <a
                href="{{ route('workspaces.projects.index', $workspace) }}"
                wire:navigate
                class="text-sm font-medium text-zinc-700 hover:text-zinc-900 dark:text-zinc-300 dark:hover:text-zinc-100"
            >
                Retour aux projets
            </a>
        </div>

        <div class="mt-5 grid gap-4 lg:grid-cols-[minmax(0,1fr)_240px] lg:items-end">
            <div>
                <div class="flex flex-wrap items-center gap-2 text-xs text-zinc-500 dark:text-zinc-400">
                    <span class="rounded-full border border-zinc-200 px-2.5 py-1 dark:border-zinc-700">
                        {{ $projectTasksCount }} tache(s)
                    </span>
                    <span class="rounded-full border border-emerald-200 bg-emerald-50 px-2.5 py-1 text-emerald-700 dark:border-emerald-900/60 dark:bg-emerald-900/20 dark:text-emerald-300">
                        {{ $projectCompletedTasksCount }} terminee(s)
                    </span>
                    <span class="rounded-full border border-blue-200 bg-blue-50 px-2.5 py-1 text-blue-700 dark:border-blue-900/60 dark:bg-blue-900/20 dark:text-blue-300">
                        {{ $projectInProgressTasksCount }} en cours
                    </span>
                    <span class="rounded-full border border-zinc-200 px-2.5 py-1 dark:border-zinc-700">
                        {{ $projectTodoTasksCount }} a faire
                    </span>
                </div>

                <div class="mt-4">
                    <div class="flex items-center justify-between text-xs text-zinc-500 dark:text-zinc-400">
                        <span>Avancement du projet</span>
                        <span>{{ $projectProgressRate }}%</span>
                    </div>
                    <div class="mt-2 h-2.5 rounded-full bg-zinc-100 dark:bg-zinc-800">
                        <div
                            class="h-2.5 rounded-full bg-zinc-900 transition-all dark:bg-zinc-100"
                            style="width: {{ $projectProgressRate }}%"
                        ></div>
                    </div>
                </div>
            </div>

            <div class="rounded-xl border border-zinc-200 bg-zinc-50 px-4 py-3 dark:border-zinc-800 dark:bg-zinc-950/60">
                <p class="text-[11px] uppercase tracking-[0.18em] text-zinc-500 dark:text-zinc-400">Progression</p>
                <p class="mt-2 text-3xl font-semibold text-zinc-900 dark:text-zinc-100">{{ $projectProgressRate }}%</p>
                <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">Basee sur les taches terminees du projet.</p>
            </div>
        </div>

        @if (session('status'))
            <div class="mt-4 rounded-lg border border-green-200 bg-green-50 px-3 py-2 text-sm text-green-700 dark:border-green-900/60 dark:bg-green-900/20 dark:text-green-300">
                {{ session('status') }}
            </div>
        @endif

        @if ($canWrite)
            <form wire:submit="createTask" class="mt-5 space-y-5">
                <div class="grid gap-3 md:grid-cols-[1.2fr_1.2fr_140px_140px_180px_auto] md:items-end">
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
                        <option value="todo">A faire</option>
                        <option value="in_progress">En cours</option>
                        <option value="done">Terminee</option>
                    </select>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Priorite</label>
                    <select
                        wire:model="priority"
                        class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100"
                    >
                        <option value="low">Basse</option>
                        <option value="medium">Moyenne</option>
                        <option value="high">Haute</option>
                    </select>
                </div>

                <flux:input
                    wire:model="dueDate"
                    label="Echeance"
                    type="date"
                />

                <button
                    type="submit"
                    class="inline-flex items-center justify-center rounded-lg border border-black bg-black px-3 py-2.5 text-sm font-medium text-white transition hover:bg-zinc-800 dark:border-zinc-100 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200"
                >
                    Ajouter
                </button>
                </div>

                <div class="rounded-xl border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-800 dark:bg-zinc-950/60">
                    @include('livewire.tasks.partials.assignee-summary', [
                        'members' => $members,
                        'selected' => $assigneeIds,
                        'label' => 'Personnes assignees',
                        'modalName' => 'create-task-assignees',
                        'errorKey' => 'assigneeIds',
                        'errorItemKey' => 'assigneeIds.*',
                    ])
                </div>
            </form>

            <flux:modal name="create-task-assignees" :show="$errors->has('assigneeIds') || $errors->has('assigneeIds.*')" focusable class="max-w-2xl">
                <div class="space-y-5">
                    <div>
                        <flux:heading size="lg">Assigner la tache</flux:heading>
                        <flux:subheading>
                            Choisissez une ou plusieurs personnes du workspace.
                        </flux:subheading>
                    </div>

                    @include('livewire.tasks.partials.assignee-picker', [
                        'members' => $members,
                        'selected' => $assigneeIds,
                        'model' => 'assigneeIds',
                        'label' => 'Personnes assignees',
                        'errorKey' => 'assigneeIds',
                        'errorItemKey' => 'assigneeIds.*',
                    ])

                    <div class="flex justify-end">
                        <flux:modal.close>
                            <button
                                type="button"
                                class="inline-flex items-center justify-center rounded-lg border border-black bg-black px-4 py-2 text-sm font-medium text-white transition hover:bg-zinc-800 dark:border-zinc-100 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200"
                            >
                                Valider
                            </button>
                        </flux:modal.close>
                    </div>
                </div>
            </flux:modal>
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
                    <option value="todo">A faire</option>
                    <option value="in_progress">En cours</option>
                    <option value="done">Terminee</option>
                </select>
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Priorite</label>
                <select
                    wire:model.live="filterPriority"
                    class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100"
                >
                    <option value="all">Toutes</option>
                    <option value="low">Basse</option>
                    <option value="medium">Moyenne</option>
                    <option value="high">Haute</option>
                </select>
            </div>

            <div>
                    <label class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Assigne</label>
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
                        <th class="px-4 py-3">Assignes</th>
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
                                    <form wire:submit="updateTask" class="space-y-5">
                                        <div class="grid gap-3 md:grid-cols-[1.2fr_1.2fr_140px_140px_180px_auto_auto] md:items-end">
                                        <flux:input wire:model="editTitle" label="Titre" type="text" required />
                                        <flux:input wire:model="editDescription" label="Description" type="text" />

                                        <div>
                                            <label class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Statut</label>
                                            <select
                                                wire:model="editStatus"
                                                class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100"
                                            >
                                                <option value="todo">A faire</option>
                                                <option value="in_progress">En cours</option>
                                                <option value="done">Terminee</option>
                                            </select>
                                        </div>

                                        <div>
                                            <label class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Priorite</label>
                                            <select
                                                wire:model="editPriority"
                                                class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100"
                                            >
                                                <option value="low">Basse</option>
                                                <option value="medium">Moyenne</option>
                                                <option value="high">Haute</option>
                                            </select>
                                        </div>

                                        <flux:input wire:model="editDueDate" label="Echeance" type="date" />

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
                                        </div>

                                        <div class="rounded-xl border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-800 dark:bg-zinc-950/60">
                                            @include('livewire.tasks.partials.assignee-summary', [
                                                'members' => $members,
                                                'selected' => $editAssigneeIds,
                                                'label' => 'Personnes assignees',
                                                'modalName' => 'edit-task-assignees',
                                                'errorKey' => 'editAssigneeIds',
                                                'errorItemKey' => 'editAssigneeIds.*',
                                            ])
                                        </div>
                                    </form>

                                    <flux:modal name="edit-task-assignees" :show="$errors->has('editAssigneeIds') || $errors->has('editAssigneeIds.*')" focusable class="max-w-2xl">
                                        <div class="space-y-5">
                                            <div>
                                                <flux:heading size="lg">Modifier les assignes</flux:heading>
                                                <flux:subheading>
                                                    Mettez a jour les personnes liees a cette tache.
                                                </flux:subheading>
                                            </div>

                                            @include('livewire.tasks.partials.assignee-picker', [
                                                'members' => $members,
                                                'selected' => $editAssigneeIds,
                                                'model' => 'editAssigneeIds',
                                                'label' => 'Personnes assignees',
                                                'errorKey' => 'editAssigneeIds',
                                                'errorItemKey' => 'editAssigneeIds.*',
                                            ])

                                            <div class="flex justify-end">
                                                <flux:modal.close>
                                                    <button
                                                        type="button"
                                                        class="inline-flex items-center justify-center rounded-lg border border-black bg-black px-4 py-2 text-sm font-medium text-white transition hover:bg-zinc-800 dark:border-zinc-100 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200"
                                                    >
                                                        Valider
                                                    </button>
                                                </flux:modal.close>
                                            </div>
                                        </div>
                                    </flux:modal>
                                </td>
                            @else
                                <td class="px-4 py-3">
                                    <div class="font-medium text-zinc-900 dark:text-zinc-100">{{ $task->title }}</div>
                                    <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ $task->description ?: 'Sans description' }}</div>
                                </td>
                                <td class="px-4 py-3 text-zinc-600 dark:text-zinc-400">
                                    @if ($task->assignees->isNotEmpty())
                                        <div class="flex flex-wrap gap-1.5">
                                            @foreach ($task->assignees as $assignee)
                                                @php
                                                    $workspaceMember = $members->firstWhere('id', $assignee->id);
                                                @endphp
                                                <span class="inline-flex items-center rounded-full border border-zinc-200 px-2.5 py-1 text-xs font-medium text-zinc-700 dark:border-zinc-700 dark:text-zinc-200">
                                                    {{ $assignee->name }}
                                                    @if ($workspaceMember?->pivot?->job_title)
                                                        <span class="ml-1 text-zinc-500 dark:text-zinc-400">· {{ $workspaceMember->pivot->job_title }}</span>
                                                    @endif
                                                </span>
                                            @endforeach
                                        </div>
                                    @else
                                        Non assigne
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex rounded-full border border-zinc-200 px-2.5 py-1 text-xs font-medium text-zinc-700 dark:border-zinc-700 dark:text-zinc-200">
                                        {{ \App\Models\Task::statusLabel($task->status) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex rounded-full border border-zinc-200 px-2.5 py-1 text-xs font-medium text-zinc-700 dark:border-zinc-700 dark:text-zinc-200">
                                        {{ \App\Models\Task::priorityLabel($task->priority) }}
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
