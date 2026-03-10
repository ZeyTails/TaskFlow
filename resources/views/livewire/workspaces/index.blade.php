<div class="mx-auto w-full max-w-5xl space-y-6">
    <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-800 dark:bg-zinc-900">
        <h1 class="text-xl font-semibold text-zinc-900 dark:text-zinc-100">Espaces de travail</h1>
        <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">Creez et gerez vos espaces collaboratifs.</p>

        @if (session('status'))
            <div class="mt-4 rounded-lg border border-green-200 bg-green-50 px-3 py-2 text-sm text-green-700 dark:border-green-900/60 dark:bg-green-900/20 dark:text-green-300">
                {{ session('status') }}
            </div>
        @endif

        <form wire:submit="createWorkspace" class="mt-5 flex flex-col gap-3 sm:flex-row sm:items-end">
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
                class="inline-flex items-center justify-center rounded-lg border border-black bg-black px-4 py-2.5 text-sm font-medium text-white transition hover:bg-zinc-800 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-black focus-visible:ring-offset-2 dark:border-zinc-100 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200 dark:focus-visible:ring-zinc-100 dark:focus-visible:ring-offset-zinc-900"
            >
                Creer un espace
            </button>
        </form>
    </div>

    <div class="grid gap-4 md:grid-cols-2">
        @forelse ($workspaces as $workspace)
            <a
                href="{{ route('workspaces.projects.index', $workspace) }}"
                wire:navigate
                class="group rounded-xl border border-zinc-200 bg-white p-5 transition hover:border-zinc-400 dark:border-zinc-800 dark:bg-zinc-900 dark:hover:border-zinc-600"
            >
                <div class="flex items-center justify-between gap-4">
                    <h2 class="text-base font-semibold text-zinc-900 dark:text-zinc-100">{{ $workspace->name }}</h2>
                    <span class="text-xs text-zinc-500 dark:text-zinc-400">{{ $workspace->projects_count }} projet(s)</span>
                </div>

                <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">
                    Proprietaire: {{ $workspace->owner->name }}
                </p>
                <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                    {{ $workspace->members_count }} membre(s)
                </p>
            </a>
        @empty
            <div class="rounded-xl border border-dashed border-zinc-300 bg-white p-8 text-center text-sm text-zinc-600 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-400 md:col-span-2">
                Aucun espace pour le moment. Creez le premier ci-dessus.
            </div>
        @endforelse
    </div>
</div>
