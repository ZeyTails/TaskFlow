<section class="w-full">
    @include('partials.settings-heading')

    <flux:heading class="sr-only">Parametres du profil</flux:heading>

    <x-settings.layout heading="Profil" subheading="Modifiez votre prenom, votre nom et votre adresse email.">
        <form wire:submit="updateProfileInformation" class="my-6 w-full space-y-6">
            <div class="grid gap-4 sm:grid-cols-2">
                <flux:input wire:model="first_name" label="Prenom" type="text" required autofocus autocomplete="given-name" />
                <flux:input wire:model="last_name" label="Nom" type="text" required autocomplete="family-name" />
            </div>

            <div>
                <flux:input wire:model="email" label="Email" type="email" required autocomplete="email" />

                @if ($this->hasUnverifiedEmail)
                    <div>
                        <flux:text class="mt-4">
                            Votre adresse email n est pas verifiee.

                            <flux:link class="text-sm cursor-pointer" wire:click.prevent="resendVerificationNotification">
                                Cliquez ici pour renvoyer l email de verification.
                            </flux:link>
                        </flux:text>

                        @if (session('status') === 'verification-link-sent')
                            <flux:text class="mt-2 font-medium !dark:text-green-400 !text-green-600">
                                Un nouveau lien de verification a ete envoye a votre adresse email.
                            </flux:text>
                        @endif
                    </div>
                @endif
            </div>

            <div class="flex items-center gap-4">
                <div class="flex items-center justify-end">
                    <flux:button variant="primary" type="submit" class="w-full">Enregistrer</flux:button>
                </div>

                <x-action-message class="me-3" on="profile-updated">
                    Enregistre.
                </x-action-message>
            </div>
        </form>

        @if ($this->showDeleteUser)
            <livewire:settings.delete-user-form />
        @endif
    </x-settings.layout>
</section>
