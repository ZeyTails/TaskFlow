<section class="w-full">
    @include('partials.settings-heading')

    <flux:heading class="sr-only">Parametres du mot de passe</flux:heading>

    <x-settings.layout heading="Mot de passe" subheading="Utilisez un mot de passe long et difficile a deviner pour securiser votre compte.">
        <form method="POST" wire:submit="updatePassword" class="mt-6 space-y-6">
            <flux:input
                wire:model="current_password"
                label="Mot de passe actuel"
                type="password"
                required
                autocomplete="current-password"
            />
            <flux:input
                wire:model="password"
                label="Nouveau mot de passe"
                type="password"
                required
                autocomplete="new-password"
            />
            <flux:input
                wire:model="password_confirmation"
                label="Confirmer le mot de passe"
                type="password"
                required
                autocomplete="new-password"
            />

            <div class="flex items-center gap-4">
                <div class="flex items-center justify-end">
                    <flux:button variant="primary" type="submit" class="w-full">Enregistrer</flux:button>
                </div>

                <x-action-message class="me-3" on="password-updated">
                    Enregistre.
                </x-action-message>
            </div>
        </form>
    </x-settings.layout>
</section>
