<div class="mx-auto w-full max-w-5xl space-y-6">
    <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-800 dark:bg-zinc-900">
        <div class="flex w-full flex-wrap items-start gap-4">
            <div class="flex flex-1 items-start gap-3">
                <flux:dropdown position="bottom" align="start" class="group">
                    <button
                        type="button"
                        class="relative inline-flex h-11 w-11 items-center justify-center rounded-xl border border-zinc-200 bg-white text-zinc-700 transition hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-200"
                        @if (! $canManageMembers) disabled @endif
                        aria-label="Changer l'icone du workspace"
                    >
                        @svg('icon-' . ($workspace->icon_key ?? 'briefcase'), 'h-5 w-5')
                        @if ($canManageMembers)
                            <span class="pointer-events-none absolute inset-0 flex items-center justify-center rounded-xl bg-black/60 text-white opacity-0 transition group-hover:opacity-100">
                                @svg('icon-pencil', 'h-4 w-4')
                            </span>
                        @endif
                    </button>

                    @if ($canManageMembers)
                        <flux:menu class="w-64 rounded-2xl border border-zinc-200 bg-white p-4 shadow-[0_18px_50px_-30px_rgba(0,0,0,0.6)] dark:border-zinc-800 dark:bg-zinc-900">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <div class="text-xs font-semibold uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Icones</div>
                                    <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">Cliquez pour changer l'icone du workspace.</p>
                                </div>
                                <span class="rounded-full border border-zinc-200 px-2 py-0.5 text-[10px] font-semibold text-zinc-500 dark:border-zinc-700 dark:text-zinc-400">
                                    {{ strtoupper($workspace->icon_key ?? 'briefcase') }}
                                </span>
                            </div>
                            <div class="mt-4 grid grid-cols-4 gap-2">
                                @foreach (\App\Models\Workspace::ICON_KEYS as $iconKey)
                                    @php $isActive = ($workspace->icon_key ?? 'briefcase') === $iconKey; @endphp
                                    <button
                                        type="button"
                                        wire:click="updateWorkspaceIcon('{{ $iconKey }}')"
                                        class="flex h-11 w-11 items-center justify-center rounded-xl border text-zinc-700 transition dark:text-zinc-200
                                            {{ $isActive ? 'border-zinc-900 bg-zinc-900 text-white shadow-sm dark:border-zinc-100 dark:bg-zinc-100 dark:text-zinc-900' : 'border-zinc-200 bg-white hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900 dark:hover:bg-zinc-800' }}"
                                        aria-label="Choisir l'icone {{ $iconKey }}"
                                    >
                                        @svg('icon-' . $iconKey, 'h-4 w-4')
                                    </button>
                                @endforeach
                            </div>
                        </flux:menu>
                    @endif
                </flux:dropdown>

                <div>
                    <h1 class="text-xl font-semibold text-zinc-900 dark:text-zinc-100">{{ $workspace->name }}</h1>
                    <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">Projets dans cet espace</p>
                    <div class="mt-3 flex flex-wrap items-center gap-2 text-xs text-zinc-500 dark:text-zinc-400">
                        <span class="rounded-full border border-zinc-200 px-2 py-0.5 dark:border-zinc-700">
                            Proprietaire: {{ $workspace->owner?->name ?? '—' }}
                        </span>
                        <span class="rounded-full border border-zinc-200 px-2 py-0.5 dark:border-zinc-700">
                            {{ $workspace->members_count ?? 0 }} membre(s)
                        </span>
                        <span class="rounded-full border border-zinc-200 px-2 py-0.5 dark:border-zinc-700">
                            {{ $workspace->projects_count ?? 0 }} projet(s)
                        </span>
                        <span class="rounded-full border border-zinc-200 px-2 py-0.5 dark:border-zinc-700">
                            Cree le {{ optional($workspace->created_at)->format('d/m/Y') }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="flex flex-col items-end gap-2">
                @if ($canWrite)
                    <button
                        type="button"
                        wire:click="toggleCreateForm"
                        class="inline-flex items-center gap-2 rounded-lg border border-black bg-black px-3 py-2 text-sm font-medium text-white transition hover:bg-zinc-800 dark:border-zinc-100 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200"
                    >
                        {{ $showCreateForm ? 'Fermer' : 'Nouveau projet' }}
                    </button>
                @endif

                <flux:dropdown position="bottom" align="end">
                    <button
                        type="button"
                        class="inline-flex items-center gap-2 rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm font-medium text-zinc-700 transition hover:bg-zinc-100 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-200 dark:hover:bg-zinc-800"
                    >
                        Gerer le workspace
                    </button>

                    <flux:menu>
                        <flux:menu.item :href="route('workspaces.members.index', $workspace)" wire:navigate>
                            {{ $canManageMembers ? 'Gerer les membres' : 'Voir les membres' }}
                        </flux:menu.item>

                        @can('delete', $workspace)
                            <flux:menu.separator />
                            <flux:menu.item
                                as="button"
                                type="button"
                                variant="danger"
                                wire:click="deleteWorkspace"
                                wire:confirm="Supprimer ce workspace et tous ses projets ?"
                            >
                                Supprimer le workspace
                            </flux:menu.item>
                        @endcan
                    </flux:menu>
                </flux:dropdown>

                <a
                    href="{{ route('workspaces.index') }}"
                    wire:navigate
                    class="text-sm font-medium text-zinc-700 hover:text-zinc-900 dark:text-zinc-300 dark:hover:text-zinc-100"
                >
                    Retour aux espaces
                </a>
            </div>
        </div>

        @if (session('status'))
            <div class="mt-4 rounded-lg border border-green-200 bg-green-50 px-3 py-2 text-sm text-green-700 dark:border-green-900/60 dark:bg-green-900/20 dark:text-green-300">
                {{ session('status') }}
            </div>
        @endif
    </div>

    @if ($canWrite)
        @if ($showCreateForm)
            <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-800 dark:bg-zinc-900">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <h2 class="text-sm font-semibold uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Nouveau projet</h2>
                        <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">Renseignez les informations essentielles.</p>
                    </div>
                    <button
                        type="button"
                        wire:click="cancelCreateForm"
                        class="inline-flex items-center justify-center rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm font-medium text-zinc-700 transition hover:bg-zinc-100 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-200 dark:hover:bg-zinc-800"
                    >
                        Annuler
                    </button>
                </div>

                <form wire:submit="createProject" class="mt-5 grid gap-3 md:grid-cols-[1fr_1fr_160px_160px]">
                    <flux:input
                        wire:model="name"
                        label="Nom du projet"
                        type="text"
                        required
                        placeholder="Ex: API Backend"
                    />

                    <flux:input
                        wire:model="description"
                        label="Description"
                        type="text"
                        placeholder="Description courte (optionnelle)"
                    />

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

                    <div>
                        <label class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Statut</label>
                        <select
                            wire:model="status"
                            class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100"
                        >
                            <option value="active">Active</option>
                            <option value="on_hold">On hold</option>
                            <option value="archived">Archived</option>
                        </select>
                    </div>

                    <button
                        type="submit"
                        class="inline-flex items-center justify-center rounded-lg border border-black bg-black px-4 py-2.5 text-sm font-medium text-white transition hover:bg-zinc-800 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-black focus-visible:ring-offset-2 dark:border-zinc-100 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200 dark:focus-visible:ring-zinc-100 dark:focus-visible:ring-offset-zinc-900"
                    >
                        Ajouter le projet
                    </button>
                </form>
            </div>
        @endif
    @else
        <div class="rounded-xl border border-zinc-200 bg-zinc-50 px-3 py-2 text-sm text-zinc-600 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">
            Vous avez un acces en lecture seule sur cet espace.
        </div>
    @endif

    <div class="space-y-3">
        @forelse ($projects as $project)
            <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
                @if ($editingProjectId === $project->id)
                <form wire:submit="updateProject" class="grid gap-3 md:grid-cols-[1fr_1fr_160px_160px]">
                    <flux:input wire:model="editName" label="Nom du projet" type="text" required />
                    <flux:input wire:model="editDescription" label="Description" type="text" />

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

                    <div>
                        <label class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Statut</label>
                        <select
                            wire:model="editStatus"
                            class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100"
                        >
                            <option value="active">Active</option>
                            <option value="on_hold">On hold</option>
                            <option value="archived">Archived</option>
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
                @else
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <h2 class="text-base font-semibold text-zinc-900 dark:text-zinc-100">{{ $project->name }}</h2>
                            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">{{ $project->description ?: 'Sans description' }}</p>
                            <div class="mt-2 flex flex-wrap items-center gap-2 text-xs text-zinc-500 dark:text-zinc-400">
                                <span class="rounded-full border border-zinc-200 px-2 py-0.5 dark:border-zinc-700">
                                    {{ $project->tasks_count }} tache(s)
                                </span>
                                <span class="rounded-full border border-zinc-200 px-2 py-0.5 dark:border-zinc-700">
                                    Priorite: {{ ucfirst($project->priority) }}
                                </span>
                                <span class="rounded-full border border-zinc-200 px-2 py-0.5 dark:border-zinc-700">
                                    Statut: {{ str_replace('_', ' ', $project->status) }}
                                </span>
                            </div>
                        </div>

                        @if ($canWrite)
                            <div class="flex items-center gap-2">
                                <a
                                    href="{{ route('workspaces.projects.tasks.index', [$workspace, $project]) }}"
                                    wire:navigate
                                    class="inline-flex items-center justify-center rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm font-medium text-zinc-700 transition hover:bg-zinc-100 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-200 dark:hover:bg-zinc-800"
                                >
                                    Taches
                                </a>
                                <button
                                    type="button"
                                    wire:click="startEditing({{ $project->id }})"
                                    class="inline-flex items-center justify-center rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm font-medium text-zinc-700 transition hover:bg-zinc-100 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-200 dark:hover:bg-zinc-800"
                                >
                                    Modifier
                                </button>
                                <button
                                    type="button"
                                    wire:click="deleteProject({{ $project->id }})"
                                    wire:confirm="Supprimer ce projet ?"
                                    class="inline-flex items-center justify-center rounded-lg border border-red-300 bg-red-50 px-3 py-2 text-sm font-medium text-red-700 transition hover:bg-red-100 dark:border-red-900/60 dark:bg-red-900/20 dark:text-red-300"
                                >
                                    Supprimer
                                </button>
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        @empty
            <div class="rounded-xl border border-dashed border-zinc-300 bg-white p-8 text-center text-sm text-zinc-600 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-400">
                Aucun projet pour le moment dans cet espace.
            </div>
        @endforelse
    </div>
</div>
