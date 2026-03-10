<x-layouts::app title="Notifications">
    <div class="mx-auto w-full max-w-5xl space-y-6 p-4 sm:p-6 lg:p-8">
        <section class="rounded-2xl border border-zinc-200 bg-white p-6 dark:border-zinc-800 dark:bg-zinc-900">
            <h1 class="text-2xl font-semibold text-zinc-900 dark:text-zinc-100">Notifications</h1>
            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                Invitations de workspace en attente pour votre compte.
            </p>
        </section>

        <section class="rounded-2xl border border-zinc-200 bg-white p-3 dark:border-zinc-800 dark:bg-zinc-900">
            <div class="overflow-x-auto">
                <table class="min-w-full border-collapse">
                    <thead>
                        <tr class="text-left text-xs uppercase tracking-wide text-zinc-500 dark:text-zinc-400">
                            <th class="px-3 py-2">Workspace</th>
                            <th class="px-3 py-2">Role</th>
                            <th class="px-3 py-2">Titre</th>
                            <th class="px-3 py-2">Invite par</th>
                            <th class="px-3 py-2">Expiration</th>
                            <th class="px-3 py-2">Statut</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($invitations as $invitation)
                            @php
                                $expired = $invitation->isExpired();
                            @endphp
                            <tr class="border-t border-zinc-200 text-sm dark:border-zinc-800">
                                <td class="px-3 py-2 text-zinc-800 dark:text-zinc-200">{{ $invitation->workspace->name }}</td>
                                <td class="px-3 py-2 text-zinc-700 dark:text-zinc-300">{{ $invitation->role }}</td>
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
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-3 py-10 text-center text-sm text-zinc-600 dark:text-zinc-400">
                                    Aucune notification pour le moment.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</x-layouts::app>
