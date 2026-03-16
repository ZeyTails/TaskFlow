<div
    x-data="{ mode: $wire.entangle('modalTab') }"
    x-on:open-create-workspace.window="mode = 'create'; $dispatch('open-modal', 'workspace-quick-actions')"
    x-on:open-join-workspace.window="mode = 'join'; $dispatch('open-modal', 'workspace-quick-actions')"
>
    <flux:modal.trigger name="workspace-quick-actions">
        <button
            type="button"
            x-on:click.prevent="mode = 'create'; $dispatch('open-modal', 'workspace-quick-actions')"
            class="inline-flex items-center gap-2 rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm font-medium text-zinc-800 transition hover:bg-zinc-100 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100 dark:hover:bg-zinc-800"
        >
            <span class="text-base leading-none">+</span>
            <span>Nouveau</span>
        </button>
    </flux:modal.trigger>

    <flux:modal name="workspace-quick-actions" :show="$errors->isNotEmpty() || session()->has('quick-actions-modal')" focusable class="max-w-xl">
        <div class="space-y-5">
            <div>
                <flux:heading size="lg">Espace</flux:heading>
                <flux:subheading>
                    Creez un nouvel espace ou rejoignez-en un avec un code d acces.
                </flux:subheading>
            </div>

            @if (session('quick-actions-error'))
                <div class="rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700 dark:border-red-900/60 dark:bg-red-900/20 dark:text-red-300">
                    {{ session('quick-actions-error') }}
                </div>
            @endif

            <div class="grid grid-cols-2 gap-2 rounded-xl border border-zinc-200 bg-zinc-50 p-1 dark:border-zinc-800 dark:bg-zinc-950/40">
                <button
                    type="button"
                    x-on:click="mode = 'create'"
                    x-bind:class="mode === 'create' ? 'bg-white text-zinc-900 shadow-sm dark:bg-zinc-900 dark:text-zinc-100' : 'text-zinc-500 dark:text-zinc-400'"
                    class="rounded-lg px-3 py-2 text-sm font-medium transition"
                >
                    Creer
                </button>
                <button
                    type="button"
                    x-on:click="mode = 'join'"
                    x-bind:class="mode === 'join' ? 'bg-white text-zinc-900 shadow-sm dark:bg-zinc-900 dark:text-zinc-100' : 'text-zinc-500 dark:text-zinc-400'"
                    class="rounded-lg px-3 py-2 text-sm font-medium transition"
                >
                    Rejoindre
                </button>
            </div>

            <form x-show="mode === 'create'" wire:submit="createWorkspace" class="space-y-4">
                <flux:input
                    wire:model="name"
                    label="Nom de l espace"
                    type="text"
                    required
                    placeholder="Ex: Equipe Produit"
                />

                <div class="flex justify-end gap-2">
                    <flux:modal.close>
                        <button
                            type="button"
                            class="inline-flex items-center justify-center rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 transition hover:bg-zinc-100 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100 dark:hover:bg-zinc-800"
                        >
                            Annuler
                        </button>
                    </flux:modal.close>

                    <button
                        type="submit"
                        class="inline-flex items-center justify-center rounded-lg border border-black bg-black px-4 py-2 text-sm font-medium text-white transition hover:bg-zinc-800 dark:border-zinc-100 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200"
                    >
                        Creer l espace
                    </button>
                </div>
            </form>

            <form x-show="mode === 'join'" wire:submit="joinWorkspace" class="space-y-4">
                <flux:input
                    wire:model="joinCode"
                    label="Code d acces"
                    type="text"
                    required
                    placeholder="XU3-3HC-O28"
                />

                <div class="flex justify-end gap-2">
                    <flux:modal.close>
                        <button
                            type="button"
                            class="inline-flex items-center justify-center rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 transition hover:bg-zinc-100 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100 dark:hover:bg-zinc-800"
                        >
                            Annuler
                        </button>
                    </flux:modal.close>

                    <button
                        type="submit"
                        class="inline-flex items-center justify-center rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 transition hover:bg-zinc-100 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100 dark:hover:bg-zinc-800"
                    >
                        Rejoindre l espace
                    </button>
                </div>
            </form>
        </div>
    </flux:modal>
</div>
