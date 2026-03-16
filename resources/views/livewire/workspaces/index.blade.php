<div class="mx-auto w-full max-w-5xl space-y-6">
    <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-800 dark:bg-zinc-900">
        <div class="flex items-center gap-3">
            <div class="flex size-10 items-center justify-center rounded-xl border border-zinc-200 bg-zinc-50 text-zinc-700 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-200">
                <flux:icon.briefcase class="size-5" />
            </div>
            <h1 class="text-xl font-semibold text-zinc-900 dark:text-zinc-100">Espaces de travail</h1>
        </div>
        <p class="mt-1 text-sm text-zinc-600 mb-4 dark:text-zinc-400">Creez et gerez vos espaces collaboratifs.</p>

        @if (session('status'))
            <div class="mt-4 rounded-lg border border-green-200 bg-green-50 px-3 py-2 text-sm text-green-700 dark:border-green-900/60 dark:bg-green-900/20 dark:text-green-300">
                {{ session('status') }}
            </div>
        @endif

        @if (session('error'))
            <div class="mt-4 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700 dark:border-red-900/60 dark:bg-red-900/20 dark:text-red-300">
                {{ session('error') }}
            </div>
        @endif

        <div class="mt-5 grid gap-6 lg:grid-cols-2">
            <div>
                <p class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Creer un espace</p>
                <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                    Donnez un nom a votre espace pour commencer a organiser vos projets.
                </p>

                <form id="create-workspace" wire:submit="createWorkspace" class="pt-6 flex flex-col gap-3 scroll-mt-24 sm:flex-row sm:items-end">
                    <div class="w-full sm:max-w-sm">
                        <flux:input
                            wire:model="name"
                            label="Nom de l espace"
                            type="text"
                            required
                            placeholder="Ex: Equipe Produit"
                        />
                    </div>

                    <button
                        type="submit"
                        class="inline-flex items-center justify-center rounded-lg border border-zinc-300 bg-white px-4 py-2.5 text-sm font-medium text-zinc-700 transition hover:bg-zinc-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-zinc-400 focus-visible:ring-offset-2 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100 dark:hover:bg-zinc-800 dark:focus-visible:ring-zinc-600 dark:focus-visible:ring-offset-zinc-900"
                    >
                        Creer
                    </button>
                </form>
            </div>

            <div id="join-workspace" class="scroll-mt-24 border-t border-zinc-200 pt-12 dark:border-zinc-800 lg:border-l lg:border-t-0 lg:pt-0 lg:pl-6">
                <p class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Rejoindre avec un code</p>
                <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                    Saisissez un code pour rejoindre un espace.
                </p>

                <form wire:submit="joinWorkspace" class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-end">
                    <div class="w-full sm:max-w-sm">
                        <flux:input
                            wire:model="joinCode"
                            label="Code d acces"
                            type="text"
                            required
                            placeholder="XU3-3HC-O28"
                        />
                    </div>

                    <button
                        type="submit"
                        class="inline-flex items-center justify-center rounded-lg border border-zinc-300 bg-white px-4 py-2.5 text-sm font-medium text-zinc-700 transition hover:bg-zinc-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-zinc-400 focus-visible:ring-offset-2 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100 dark:hover:bg-zinc-800 dark:focus-visible:ring-zinc-600 dark:focus-visible:ring-offset-zinc-900"
                    >
                        Rejoindre
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="grid items-stretch gap-4 md:grid-cols-2">
        @forelse ($workspaces as $workspace)
            @php $isPinned = (bool) $workspace->pivot->is_pinned; @endphp

            <article wire:key="workspace-{{ $workspace->id }}" class="rounded-xl border border-zinc-200 bg-white p-5 transition hover:border-zinc-400 dark:border-zinc-800 dark:bg-zinc-900 dark:hover:border-zinc-600">
                <div class="flex h-full gap-4">
                    <div class="flex min-w-0 flex-1 flex-col">
                        <div class="flex min-w-0 items-center gap-3">
                            <x-workspace-icon :workspace="$workspace" />
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <h2 class="truncate text-base font-semibold text-zinc-900 dark:text-zinc-100">{{ $workspace->name }}</h2>
                                    <x-workspace-theme-badge :workspace="$workspace" />
                                </div>
                            </div>
                        </div>

                        <div class="mt-2 space-y-1">
                            <p class="text-sm text-zinc-600 dark:text-zinc-400">
                                Proprietaire: {{ $workspace->owner->name }}
                            </p>
                            <p class="text-sm text-zinc-600 dark:text-zinc-400">
                                {{ $workspace->members_count }} membre(s)
                            </p>
                        </div>

                        <div class="mt-3 min-h-[72px]">
                            @can('manageMembers', $workspace)
                                <div x-data="{ revealCode: false }" class="rounded-lg border border-zinc-200 bg-zinc-50 px-3 py-2 dark:border-zinc-700 dark:bg-zinc-800/60">
                                    <div class="flex items-center justify-between gap-3">
                                        <p class="text-[11px] uppercase tracking-[0.18em] text-zinc-500 dark:text-zinc-400">Code d acces</p>
                                        <button
                                            type="button"
                                            x-on:click="revealCode = ! revealCode"
                                            class="inline-flex items-center justify-center rounded-md border border-zinc-200 bg-white p-1.5 text-zinc-600 transition hover:bg-zinc-100 hover:text-zinc-900 dark:border-zinc-600 dark:bg-zinc-900 dark:text-zinc-300 dark:hover:bg-zinc-800 dark:hover:text-zinc-100"
                                            x-bind:aria-label="revealCode ? 'Masquer le code d acces' : 'Afficher le code d acces'"
                                        >
                                            <flux:icon.eye x-show="! revealCode" class="size-4" />
                                            <flux:icon.eye-slash x-show="revealCode" class="size-4" />
                                        </button>
                                    </div>
                                    <p class="mt-1 font-mono text-sm font-semibold tracking-[0.2em] text-zinc-900 dark:text-zinc-100">
                                        <span x-show="revealCode">{{ $workspace->join_code }}</span>
                                        <span x-show="! revealCode">***-***-***</span>
                                    </p>
                                </div>
                            @else
                                <div class="h-full rounded-lg border border-dashed border-zinc-200 bg-zinc-50/60 px-3 py-2 dark:border-zinc-700 dark:bg-zinc-800/30">
                                    <p class="text-[11px] uppercase tracking-[0.18em] text-zinc-400 dark:text-zinc-500">Code d acces</p>
                                    <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Visible uniquement pour le proprietaire.</p>
                                </div>
                            @endcan
                        </div>

                        <div class="mt-auto flex items-center justify-between gap-2 pt-4">
                            <a
                                href="{{ route('workspaces.projects.index', $workspace) }}"
                                wire:navigate
                                class="text-sm font-medium text-zinc-700 transition hover:text-zinc-900 dark:text-zinc-300 dark:hover:text-zinc-100"
                            >
                                Ouvrir l espace
                            </a>

                            @can('update', $workspace)
                                <button
                                    type="button"
                                    wire:click="startEditingWorkspace({{ $workspace->id }})"
                                    class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-zinc-300 bg-white text-zinc-600 transition hover:bg-zinc-100 hover:text-zinc-900 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-300 dark:hover:bg-zinc-800 dark:hover:text-zinc-100"
                                    aria-label="Parametres de l espace"
                                    title="Parametres"
                                >
                                    @svg('icon-cog', 'h-4 w-4')
                                </button>
                            @endcan
                        </div>
                    </div>

                    <div class="flex shrink-0 flex-col items-end gap-2">
                        <span class="text-xs text-zinc-500 dark:text-zinc-400">{{ $workspace->projects_count }} projet(s)</span>
                        <button
                            type="button"
                            wire:click="togglePinned({{ $workspace->id }})"
                            @class([
                                'inline-flex h-9 w-9 items-center justify-center rounded-lg border transition',
                                'border-rose-300 bg-rose-50 text-rose-700 hover:bg-rose-100 dark:border-rose-900/60 dark:bg-rose-950/30 dark:text-rose-300 dark:hover:bg-rose-950/50' => $isPinned,
                                'border-zinc-300 bg-white text-zinc-500 hover:bg-zinc-100 hover:text-zinc-800 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-zinc-100' => ! $isPinned,
                            ])
                            aria-label="{{ $isPinned ? 'Desepingler l espace' : 'Epingler l espace' }}"
                            title="{{ $isPinned ? 'Desepingler' : 'Epingler' }}"
                        >
                            @svg('icon-pin', 'h-5 w-5')
                        </button>
                    </div>
                </div>
            </article>
        @empty
            <div class="rounded-xl border border-dashed border-zinc-300 bg-white p-8 text-center text-sm text-zinc-600 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-400 md:col-span-2">
                Aucun espace pour le moment. Creez le premier ci-dessus.
            </div>
        @endforelse
    </div>

    @if ($editingWorkspaceId !== null)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-zinc-950/45 p-4">
            <div class="w-full max-w-2xl rounded-2xl border border-zinc-200 bg-white p-6 shadow-2xl dark:border-zinc-800 dark:bg-zinc-900">
                <div class="space-y-5">
                    <div>
                        <h2 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">Parametres de l espace</h2>
                        <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                            Mettez a jour le nom et l icone de l espace depuis ce panneau.
                        </p>
                    </div>

                    <form wire:submit="saveWorkspaceSettings" class="space-y-5">
                        <flux:input
                            wire:model="editWorkspaceName"
                            label="Nom de l espace"
                            type="text"
                            required
                        />

                        <div>
                            @php $editTheme = \App\Models\Workspace::themeFor($editWorkspaceIconKey); @endphp
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <p class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Photo de profil</p>
                                    <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">Choisissez l icone qui representera cet espace.</p>
                                </div>
                                <div class="inline-flex h-11 w-11 items-center justify-center rounded-xl border {{ $editTheme['icon'] }}">
                                    @svg('icon-' . $editWorkspaceIconKey, 'h-5 w-5')
                                </div>
                            </div>

                            <div class="mt-4 grid grid-cols-4 gap-2 sm:grid-cols-8">
                                @foreach (\App\Models\Workspace::ICON_KEYS as $iconKey)
                                    @php
                                        $isActive = $editWorkspaceIconKey === $iconKey;
                                        $theme = \App\Models\Workspace::themeFor($iconKey);
                                    @endphp
                                    <button
                                        type="button"
                                        wire:click="updateWorkspaceIcon('{{ $iconKey }}')"
                                        @class([
                                            'flex h-11 w-11 items-center justify-center rounded-xl border transition',
                                            $theme['icon'].' ring-2 ring-offset-2 ring-zinc-900 dark:ring-zinc-100 dark:ring-offset-zinc-900' => $isActive,
                                            'border-zinc-200 bg-white text-zinc-700 hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-200 dark:hover:bg-zinc-800' => ! $isActive,
                                        ])
                                        aria-label="Choisir l icone {{ $iconKey }}"
                                    >
                                        @svg('icon-' . $iconKey, 'h-4 w-4')
                                    </button>
                                @endforeach
                            </div>
                        </div>

                        <div class="flex justify-end gap-2">
                            <button
                                type="button"
                                wire:click="cancelEditingWorkspace"
                                class="inline-flex items-center justify-center rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 transition hover:bg-zinc-100 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100 dark:hover:bg-zinc-800"
                            >
                                Annuler
                            </button>

                            <button
                                type="submit"
                                class="inline-flex items-center justify-center rounded-lg border border-black bg-black px-4 py-2 text-sm font-medium text-white transition hover:bg-zinc-800 dark:border-zinc-100 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200"
                            >
                                Enregistrer
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
