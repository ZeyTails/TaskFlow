@php
    $roleLabels = [
        \App\Models\Workspace::ROLE_OWNER => 'Proprietaire',
        \App\Models\Workspace::ROLE_MEMBER => 'Membre',
        \App\Models\Workspace::ROLE_VIEWER => 'Lecteur',
    ];
@endphp

<div class="mx-auto w-full max-w-6xl space-y-6">
    <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-800 dark:bg-zinc-900">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="inline-flex h-11 w-11 items-center justify-center rounded-xl border border-zinc-200 bg-white text-zinc-700 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-200">
                    @svg('icon-' . ($workspace->icon_key ?? 'briefcase'), 'h-5 w-5')
                </div>
                <div>
                    <h1 class="text-xl font-semibold text-zinc-900 dark:text-zinc-100">Membres du workspace</h1>
                    <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">{{ $workspace->name }}</p>
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
    </div>

    <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-800 dark:bg-zinc-900">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <h2 class="text-sm font-semibold uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Membres</h2>
            <span class="text-xs text-zinc-500 dark:text-zinc-400">{{ $members->count() }} membre(s)</span>
        </div>

        <div class="mt-4 grid gap-3 md:grid-cols-[1fr_220px_220px]">
            <flux:input
                wire:model.live.debounce.350ms="search"
                label="Recherche"
                type="text"
                placeholder="Nom ou email"
            />

            <div>
                <label for="filterRole" class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Role</label>
                <select
                    id="filterRole"
                    wire:model.live="filterRole"
                    class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100"
                >
                    <option value="all">Tous</option>
                    <option value="{{ \App\Models\Workspace::ROLE_OWNER }}">Proprietaire</option>
                    <option value="{{ \App\Models\Workspace::ROLE_MEMBER }}">Membre</option>
                    <option value="{{ \App\Models\Workspace::ROLE_VIEWER }}">Lecteur</option>
                </select>
            </div>

            <div>
                <label for="filterStatus" class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Statut</label>
                <select
                    id="filterStatus"
                    wire:model.live="filterStatus"
                    class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100"
                >
                    <option value="all">Tous</option>
                    <option value="{{ \App\Models\Workspace::MEMBER_STATUS_ACTIVE }}">Actif</option>
                    <option value="invited">Invite</option>
                    <option value="{{ \App\Models\Workspace::MEMBER_STATUS_SUSPENDED }}">Suspendu</option>
                </select>
            </div>
        </div>

        @if ($filterStatus !== 'invited')
            <div class="mt-4 rounded-xl border border-zinc-200 bg-white p-2 dark:border-zinc-800 dark:bg-zinc-900">
                <div class="overflow-x-auto md:overflow-visible">
                    <table class="min-w-full border-collapse">
                    <thead>
                        <tr class="text-left text-xs uppercase tracking-wide text-zinc-500 dark:text-zinc-400">
                            <th class="px-4 py-3">Nom</th>
                            <th class="px-4 py-3">Email</th>
                            <th class="px-4 py-3">Role</th>
                            <th class="px-4 py-3">Titre</th>
                            <th class="px-4 py-3">Statut</th>
                            <th class="px-4 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($members as $member)
                            <tr class="border-t border-zinc-200 text-sm dark:border-zinc-800">
                                <td class="px-4 py-3 font-medium text-zinc-900 dark:text-zinc-100">
                                    <div class="flex items-center gap-2.5">
                                        @php
                                            $avatarUrl = null;

                                            if ($member->avatar_path) {
                                                $avatarUrl = \Illuminate\Support\Str::startsWith($member->avatar_path, ['http://', 'https://'])
                                                    ? $member->avatar_path
                                                    : \Illuminate\Support\Facades\Storage::url($member->avatar_path);
                                            }
                                        @endphp

                                        @if ($avatarUrl)
                                            <img
                                                src="{{ $avatarUrl }}"
                                                alt="Avatar {{ $member->name }}"
                                                class="h-8 w-8 rounded-full border border-zinc-200 object-cover dark:border-zinc-700"
                                            />
                                        @else
                                            <span class="inline-flex h-8 w-8 items-center justify-center rounded-full border border-zinc-200 bg-zinc-100 text-zinc-600 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">
                                                <x-icon-user class="h-4 w-4" />
                                            </span>
                                        @endif

                                        <span>{{ $member->name }}</span>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-zinc-600 dark:text-zinc-400">
                                    {{ $member->email }}
                                </td>
                                <td class="px-4 py-3">
                                    @if ($canManageMembers && $editingMemberId === $member->id)
                                        <select
                                            wire:model="memberRoles.{{ $member->id }}"
                                            class="w-full min-w-36 rounded-lg border border-zinc-300 bg-white px-3 py-1.5 text-xs text-zinc-900 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100"
                                        >
                                            <option value="{{ \App\Models\Workspace::ROLE_MEMBER }}">Membre</option>
                                            <option value="{{ \App\Models\Workspace::ROLE_VIEWER }}">Lecteur</option>
                                        </select>
                                    @else
                                        <span class="inline-flex rounded-full border border-zinc-300 px-2.5 py-1 text-xs font-medium text-zinc-700 dark:border-zinc-700 dark:text-zinc-200">
                                            {{ $roleLabels[$member->pivot->role] ?? $member->pivot->role }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-zinc-600 dark:text-zinc-400">
                                    @if ($canManageMembers && $editingMemberId === $member->id)
                                        <input
                                            type="text"
                                            wire:model.defer="jobTitles.{{ $member->id }}"
                                            placeholder="Sans titre"
                                            class="w-full min-w-44 rounded-lg border border-zinc-300 bg-white px-3 py-1.5 text-xs text-zinc-900 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100"
                                        />
                                    @else
                                        {{ $member->pivot->job_title ?: 'Sans titre' }}
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    @if ($member->pivot->status === \App\Models\Workspace::MEMBER_STATUS_ACTIVE)
                                        <span class="inline-flex rounded-full border border-green-300 bg-green-50 px-2.5 py-1 text-xs font-medium text-green-700 dark:border-green-900/70 dark:bg-green-900/20 dark:text-green-300">
                                            Actif
                                        </span>
                                    @else
                                        <span class="inline-flex rounded-full border border-amber-300 bg-amber-50 px-2.5 py-1 text-xs font-medium text-amber-700 dark:border-amber-900/70 dark:bg-amber-900/20 dark:text-amber-300">
                                            Suspendu
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right">
                                    @if ($canManageMembers && $member->id !== $workspace->owner_id)
                                        <div class="relative z-20 flex justify-end">
                                            <flux:dropdown position="bottom" align="end">
                                                <button
                                                    type="button"
                                                    class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-zinc-300 bg-white text-zinc-700 transition hover:bg-zinc-100 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-200 dark:hover:bg-zinc-800"
                                                    aria-label="Actions membre"
                                                >
                                                    <x-icon-dots-horizontal class="h-4 w-4" />
                                                </button>

                                                <flux:menu>
                                                    @if ($editingMemberId === $member->id)
                                                        <flux:menu.item
                                                            as="button"
                                                            type="button"
                                                            wire:click="saveMemberChanges({{ $member->id }})"
                                                            class="!text-emerald-700 dark:!text-emerald-300 [&[data-active]]:!bg-emerald-50 dark:[&[data-active]]:!bg-emerald-900/20"
                                                        >
                                                            Enregistrer
                                                        </flux:menu.item>
                                                        <flux:menu.item
                                                            as="button"
                                                            type="button"
                                                            wire:click="cancelEditing"
                                                            class="!text-zinc-700 dark:!text-zinc-200 [&[data-active]]:!bg-zinc-100 dark:[&[data-active]]:!bg-zinc-800"
                                                        >
                                                            Annuler
                                                        </flux:menu.item>
                                                    @else
                                                        <flux:menu.item
                                                            as="button"
                                                            type="button"
                                                            wire:click="startEditing({{ $member->id }})"
                                                            class="!text-blue-700 dark:!text-blue-300 [&[data-active]]:!bg-blue-50 dark:[&[data-active]]:!bg-blue-900/20"
                                                        >
                                                            Modifier
                                                        </flux:menu.item>
                                                    @endif

                                                    @if ($member->pivot->status === \App\Models\Workspace::MEMBER_STATUS_ACTIVE)
                                                        <flux:menu.item
                                                            as="button"
                                                            type="button"
                                                            wire:click="suspendMember({{ $member->id }})"
                                                            class="!text-amber-700 dark:!text-amber-300 [&[data-active]]:!bg-amber-50 dark:[&[data-active]]:!bg-amber-900/20"
                                                        >
                                                            Suspendre
                                                        </flux:menu.item>
                                                    @else
                                                        <flux:menu.item
                                                            as="button"
                                                            type="button"
                                                            wire:click="activateMember({{ $member->id }})"
                                                            class="!text-emerald-700 dark:!text-emerald-300 [&[data-active]]:!bg-emerald-50 dark:[&[data-active]]:!bg-emerald-900/20"
                                                        >
                                                            Reactiver
                                                        </flux:menu.item>
                                                    @endif

                                                    <flux:menu.item
                                                        as="button"
                                                        type="button"
                                                        wire:click="transferOwnership({{ $member->id }})"
                                                        wire:confirm="Transferer ownership a ce membre ?"
                                                        class="!text-sky-700 dark:!text-sky-300 [&[data-active]]:!bg-sky-50 dark:[&[data-active]]:!bg-sky-900/20"
                                                    >
                                                        Transferer ownership
                                                    </flux:menu.item>

                                                    <flux:menu.separator />

                                                    <flux:menu.item
                                                        as="button"
                                                        type="button"
                                                        variant="danger"
                                                        wire:click="removeMember({{ $member->id }})"
                                                        wire:confirm="Retirer ce membre du workspace ?"
                                                        class="!text-red-700 dark:!text-red-300 [&[data-active]]:!bg-red-50 dark:[&[data-active]]:!bg-red-900/20"
                                                    >
                                                        Retirer
                                                    </flux:menu.item>
                                                </flux:menu>
                                            </flux:dropdown>
                                        </div>
                                    @else
                                        <div class="text-right text-xs text-zinc-500 dark:text-zinc-400">-</div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-10 text-center text-sm text-zinc-600 dark:text-zinc-400">
                                    Aucun membre selon les filtres.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    </table>
                </div>
            </div>
        @else
            <div class="mt-4 rounded-lg border border-blue-200 bg-blue-50 px-3 py-2 text-sm text-blue-700 dark:border-blue-900/60 dark:bg-blue-900/20 dark:text-blue-300">
                Filtre "Invite" actif. La liste des invitations en attente est affichee ci-dessous.
            </div>
        @endif
    </div>

    @if ($canManageMembers)
        <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <h2 class="text-sm font-semibold uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Invitations en attente</h2>
                <span class="text-xs text-zinc-500 dark:text-zinc-400">{{ $invitations->count() }} invitation(s)</span>
            </div>
            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                Un membre n'est ajoute qu'apres acceptation de son invitation.
            </p>

            <form wire:submit="createInvitation" class="mt-3 grid gap-3 md:grid-cols-[1fr_180px_1fr_150px_auto] md:items-end">
                <flux:input
                    wire:model="inviteEmail"
                    label="Email invite"
                    type="email"
                    required
                    placeholder="invite@exemple.com"
                />

                <div>
                    <label for="inviteRole" class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Role</label>
                    <select
                        id="inviteRole"
                        wire:model="inviteRole"
                        class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100"
                    >
                        <option value="{{ \App\Models\Workspace::ROLE_MEMBER }}">Membre</option>
                        <option value="{{ \App\Models\Workspace::ROLE_VIEWER }}">Lecteur</option>
                    </select>
                </div>

                <flux:input
                    wire:model="inviteJobTitle"
                    label="Titre (optionnel)"
                    type="text"
                    placeholder="Ex: QA"
                />

                <flux:input
                    wire:model="inviteExpiresInDays"
                    label="Expire dans (jours)"
                    type="number"
                    min="1"
                    max="30"
                />

                <button
                    type="submit"
                    class="inline-flex items-center justify-center rounded-lg border border-black bg-black px-4 py-2.5 text-sm font-medium text-white transition hover:bg-zinc-800 dark:border-zinc-100 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200"
                >
                    Inviter
                </button>
            </form>

            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full border-collapse">
                    <thead>
                        <tr class="text-left text-xs uppercase tracking-wide text-zinc-500 dark:text-zinc-400">
                            <th class="px-3 py-2">Email</th>
                            <th class="px-3 py-2">Role</th>
                            <th class="px-3 py-2">Titre</th>
                            <th class="px-3 py-2">Expiration</th>
                            <th class="px-3 py-2">Statut</th>
                            <th class="px-3 py-2 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($invitations as $invitation)
                            @php
                                $isExpired = $invitation->isExpired();
                            @endphp
                            <tr class="border-t border-zinc-200 text-sm dark:border-zinc-800">
                                <td class="px-3 py-2 text-zinc-700 dark:text-zinc-300">{{ $invitation->email }}</td>
                                <td class="px-3 py-2 text-zinc-700 dark:text-zinc-300">{{ $roleLabels[$invitation->role] ?? $invitation->role }}</td>
                                <td class="px-3 py-2 text-zinc-700 dark:text-zinc-300">{{ $invitation->job_title ?: 'Sans titre' }}</td>
                                <td class="px-3 py-2 text-zinc-700 dark:text-zinc-300">{{ $invitation->expires_at->format('d/m/Y H:i') }}</td>
                                <td class="px-3 py-2">
                                    @if ($isExpired)
                                        <span class="inline-flex rounded-full border border-red-300 bg-red-50 px-2.5 py-1 text-xs font-medium text-red-700 dark:border-red-900/70 dark:bg-red-900/20 dark:text-red-300">
                                            Expiree
                                        </span>
                                    @else
                                        <span class="inline-flex rounded-full border border-blue-300 bg-blue-50 px-2.5 py-1 text-xs font-medium text-blue-700 dark:border-blue-900/70 dark:bg-blue-900/20 dark:text-blue-300">
                                            En attente
                                        </span>
                                    @endif
                                </td>
                                <td class="px-3 py-2 text-right">
                                    <div class="flex justify-end gap-2">
                                        <button
                                            type="button"
                                            wire:click="resendInvitation({{ $invitation->id }})"
                                            class="rounded-lg border border-zinc-300 bg-white px-2.5 py-1.5 text-xs font-medium text-zinc-700 transition hover:bg-zinc-100 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-200 dark:hover:bg-zinc-800"
                                        >
                                            Relancer
                                        </button>
                                        <button
                                            type="button"
                                            wire:click="cancelInvitation({{ $invitation->id }})"
                                            class="rounded-lg border border-red-300 bg-red-50 px-2.5 py-1.5 text-xs font-medium text-red-700 transition hover:bg-red-100 dark:border-red-900/60 dark:bg-red-900/20 dark:text-red-300"
                                        >
                                            Annuler
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-3 py-8 text-center text-sm text-zinc-600 dark:text-zinc-400">
                                    Aucune invitation en attente pour ces filtres.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
