@php
    $roleLabels = [
        \App\Models\Workspace::ROLE_OWNER => 'Proprietaire',
        \App\Models\Workspace::ROLE_MEMBER => 'Membre',
        \App\Models\Workspace::ROLE_VIEWER => 'Lecteur',
    ];
@endphp

<x-layouts::app title="Notifications">
    <div class="mx-auto w-full max-w-5xl space-y-6 p-4 sm:p-6 lg:p-8">
        <section class="rounded-2xl border border-zinc-200 bg-white p-6 dark:border-zinc-800 dark:bg-zinc-900">
            <h1 class="text-2xl font-semibold text-zinc-900 dark:text-zinc-100">Notifications</h1>
            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                Invitations d espace et alertes sur les taches.
            </p>

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
        </section>

        <section class="rounded-2xl border border-zinc-200 bg-white p-6 dark:border-zinc-800 dark:bg-zinc-900">
            <div class="mb-4 flex items-center justify-between gap-3">
                <div>
                    <h2 class="text-base font-semibold text-zinc-900 dark:text-zinc-100">Alertes taches</h2>
                    <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">Attributions et modifications recentes.</p>
                </div>

                @if ($taskNotifications->whereNull('read_at')->isNotEmpty())
                    <form method="POST" action="{{ route('notifications.tasks.read-all') }}">
                        @csrf
                        <button
                            type="submit"
                            class="rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm font-medium text-zinc-700 transition hover:bg-zinc-100 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-200 dark:hover:bg-zinc-800"
                        >
                            Tout marquer comme lu
                        </button>
                    </form>
                @endif
            </div>

            <div class="space-y-3">
                @forelse ($taskNotifications as $notification)
                    <article @class([
                        'rounded-xl border p-4 transition',
                        'border-blue-200 bg-blue-50/60 dark:border-blue-900/60 dark:bg-blue-950/20' => $notification->read_at === null,
                        'border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900' => $notification->read_at !== null,
                    ])>
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div class="space-y-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <p class="font-medium text-zinc-900 dark:text-zinc-100">{{ $notification->title }}</p>
                                    @if ($notification->read_at === null)
                                        <span class="inline-flex rounded-full border border-blue-300 bg-blue-50 px-2 py-0.5 text-[11px] font-medium text-blue-700 dark:border-blue-900/70 dark:bg-blue-900/20 dark:text-blue-300">
                                            Nouveau
                                        </span>
                                    @endif
                                </div>
                                <p class="text-sm text-zinc-600 dark:text-zinc-400">{{ $notification->body }}</p>
                                <div class="flex flex-wrap items-center gap-2 text-xs text-zinc-500 dark:text-zinc-400">
                                    @if ($notification->task)
                                        <a href="{{ route('tasks.show', $notification->task) }}" wire:navigate class="font-medium hover:text-zinc-900 dark:hover:text-zinc-100">
                                            {{ $notification->task->title }}
                                        </a>
                                    @endif
                                    @if ($notification->task?->project)
                                        <span>• {{ $notification->task->project->workspace->name }} / {{ $notification->task->project->name }}</span>
                                    @endif
                                    <span>• {{ $notification->created_at->format('d/m/Y H:i') }}</span>
                                </div>
                            </div>

                            @if ($notification->read_at === null)
                                <form method="POST" action="{{ route('notifications.tasks.read', $notification) }}">
                                    @csrf
                                    <button
                                        type="submit"
                                        class="rounded-lg border border-zinc-300 bg-white px-3 py-2 text-xs font-medium text-zinc-700 transition hover:bg-zinc-100 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-200 dark:hover:bg-zinc-800"
                                    >
                                        Marquer comme lu
                                    </button>
                                </form>
                            @endif
                        </div>
                    </article>
                @empty
                    <div class="rounded-xl border border-dashed border-zinc-300 px-4 py-8 text-center text-sm text-zinc-600 dark:border-zinc-700 dark:text-zinc-400">
                        Aucune alerte de tache pour le moment.
                    </div>
                @endforelse
            </div>
        </section>

        <section class="rounded-2xl border border-zinc-200 bg-white p-3 dark:border-zinc-800 dark:bg-zinc-900">
            <div class="px-3 pb-3 pt-2">
                <h2 class="text-base font-semibold text-zinc-900 dark:text-zinc-100">Invitations en attente</h2>
                <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">Demandes d acces a vos espaces.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full border-collapse">
                    <thead>
                        <tr class="text-left text-xs uppercase tracking-wide text-zinc-500 dark:text-zinc-400">
                            <th class="px-3 py-2">Espace</th>
                            <th class="px-3 py-2">Role</th>
                            <th class="px-3 py-2">Titre</th>
                            <th class="px-3 py-2">Invite par</th>
                            <th class="px-3 py-2">Expiration</th>
                            <th class="px-3 py-2">Statut</th>
                            <th class="px-3 py-2 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($invitations as $invitation)
                            @php
                                $expired = $invitation->isExpired();
                            @endphp
                            <tr class="border-t border-zinc-200 text-sm dark:border-zinc-800">
                                <td class="px-3 py-2 text-zinc-800 dark:text-zinc-200">
                                    <div class="font-medium">{{ $invitation->workspace->name }}</div>
                                    <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ $invitation->email }}</div>
                                </td>
                                <td class="px-3 py-2 text-zinc-700 dark:text-zinc-300">{{ $roleLabels[$invitation->role] ?? $invitation->role }}</td>
                                <td class="px-3 py-2 text-zinc-700 dark:text-zinc-300">{{ $invitation->job_title ?: 'Sans titre' }}</td>
                                <td class="px-3 py-2 text-zinc-700 dark:text-zinc-300">{{ $invitation->inviter?->name ?: 'Systeme' }}</td>
                                <td class="px-3 py-2 text-zinc-700 dark:text-zinc-300">{{ $invitation->expires_at->format('d/m/Y H:i') }}</td>
                                <td class="px-3 py-2">
                                    @if ($expired)
                                        <span class="inline-flex rounded-full border border-red-300 bg-red-50 px-2 py-1 text-xs font-medium text-red-700 dark:border-red-900/70 dark:bg-red-900/20 dark:text-red-300">
                                            Expiree
                                        </span>
                                    @else
                                        <span class="inline-flex rounded-full border border-blue-300 bg-blue-50 px-2 py-1 text-xs font-medium text-blue-700 dark:border-blue-900/70 dark:bg-blue-900/20 dark:text-blue-300">
                                            En attente
                                        </span>
                                    @endif
                                </td>
                                <td class="px-3 py-2">
                                    <div class="flex justify-end gap-2">
                                        <form method="POST" action="{{ route('notifications.invitations.accept', $invitation) }}">
                                            @csrf
                                            <button
                                                type="submit"
                                                @if ($expired) disabled @endif
                                                class="rounded-lg border border-green-300 bg-green-50 px-3 py-1.5 text-xs font-medium text-green-700 transition hover:bg-green-100 disabled:cursor-not-allowed disabled:opacity-50 dark:border-green-900/60 dark:bg-green-900/20 dark:text-green-300"
                                            >
                                                Accepter
                                            </button>
                                        </form>

                                        <form method="POST" action="{{ route('notifications.invitations.decline', $invitation) }}">
                                            @csrf
                                            <button
                                                type="submit"
                                                class="rounded-lg border border-red-300 bg-red-50 px-3 py-1.5 text-xs font-medium text-red-700 transition hover:bg-red-100 dark:border-red-900/60 dark:bg-red-900/20 dark:text-red-300"
                                            >
                                                Refuser
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-3 py-10 text-center text-sm text-zinc-600 dark:text-zinc-400">
                                    Aucune invitation pour le moment.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</x-layouts::app>
