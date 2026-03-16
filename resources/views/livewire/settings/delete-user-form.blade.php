<section class="mt-10 space-y-6">
    <div class="relative mb-5">
        <flux:heading>Supprimer le compte</flux:heading>
        <flux:subheading>Supprimez votre compte et toutes les donnees associees.</flux:subheading>
    </div>

    <flux:modal.trigger name="confirm-user-deletion">
        <flux:button variant="danger" x-data="" x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')">
            Supprimer le compte
        </flux:button>
    </flux:modal.trigger>

    <flux:modal name="confirm-user-deletion" :show="$errors->isNotEmpty()" focusable class="max-w-lg">
        <form method="POST" wire:submit="deleteUser" class="space-y-6">
            <div>
                <flux:heading size="lg">Voulez-vous vraiment supprimer votre compte ?</flux:heading>

                <flux:subheading>
                    Une fois votre compte supprime, toutes ses ressources et ses donnees seront effacees definitivement. Saisissez votre mot de passe pour confirmer cette suppression.
                </flux:subheading>
            </div>

            <flux:input wire:model="password" label="Mot de passe" type="password" />

            <div class="flex justify-end space-x-2 rtl:space-x-reverse">
                <flux:modal.close>
                    <flux:button variant="filled">Annuler</flux:button>
                </flux:modal.close>

                <flux:button variant="danger" type="submit">Supprimer le compte</flux:button>
            </div>
        </form>
    </flux:modal>
</section>
