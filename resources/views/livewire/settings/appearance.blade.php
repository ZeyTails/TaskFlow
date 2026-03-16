<section class="w-full">
    @include('partials.settings-heading')

    <flux:heading class="sr-only">Parametres d apparence</flux:heading>

    <x-settings.layout heading="Apparence" subheading="Choisissez l apparence utilisee pour votre compte.">
        <flux:radio.group x-data variant="segmented" x-model="$flux.appearance">
            <flux:radio value="light" icon="sun">Clair</flux:radio>
            <flux:radio value="dark" icon="moon">Sombre</flux:radio>
            <flux:radio value="system" icon="computer-desktop">Systeme</flux:radio>
        </flux:radio.group>
    </x-settings.layout>
</section>
