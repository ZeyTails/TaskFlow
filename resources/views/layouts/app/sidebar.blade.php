<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-zinc-100 text-zinc-900 dark:bg-zinc-950 dark:text-zinc-100">
        @php
            $notificationsCount = 0;

            if (\Illuminate\Support\Facades\Schema::hasTable('workspace_invitations')) {
                $notificationsCount = \App\Models\WorkspaceInvitation::query()
                    ->pending()
                    ->where('email', auth()->user()->email)
                    ->count();
            }
        @endphp

        <flux:sidebar sticky collapsible="mobile" class="border-e border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
            <flux:sidebar.header>
                <x-app-logo :sidebar="true" href="{{ route('dashboard') }}" wire:navigate />
                <flux:sidebar.collapse class="lg:hidden" />
            </flux:sidebar.header>

            <flux:sidebar.nav>
                <flux:sidebar.group heading="Navigation" class="grid">
                    <flux:sidebar.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>
                        Tableau de bord
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="check-circle" :href="route('my-tasks')" :current="request()->routeIs('my-tasks')" wire:navigate>
                        Mes taches
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="briefcase" :href="route('workspaces.index')" :current="request()->routeIs('workspaces.*')" wire:navigate>
                        Espaces et projets
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="calendar" :href="route('calendar')" :current="request()->routeIs('calendar')" wire:navigate>
                        Calendrier
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="bell" :href="route('notifications')" :current="request()->routeIs('notifications')" wire:navigate>
                        Notifications
                        @if ($notificationsCount > 0)
                            <span class="ml-2 inline-flex rounded-full border border-blue-200 bg-blue-50 px-2 py-0.5 text-[10px] font-semibold leading-none text-blue-700 dark:border-blue-900/70 dark:bg-blue-900/20 dark:text-blue-300">
                                {{ $notificationsCount }}
                            </span>
                        @endif
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="cog" :href="route('profile.edit')" :current="request()->routeIs('profile.edit', 'user-password.edit', 'appearance.edit')" wire:navigate>
                        Parametres
                    </flux:sidebar.item>
                </flux:sidebar.group>
            </flux:sidebar.nav>

            <flux:spacer />

            <x-desktop-user-menu class="hidden lg:block" :name="auth()->user()->name" />
        </flux:sidebar>

        <flux:header class="border-b border-zinc-200 bg-white/90 backdrop-blur lg:px-6 dark:border-zinc-800 dark:bg-zinc-900/90">
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

            <div class="hidden w-full max-w-xl md:block">
                <label for="search-global" class="sr-only">Recherche</label>
                <input
                    id="search-global"
                    type="search"
                    placeholder="Rechercher (MVP)"
                    class="w-full rounded-lg border border-zinc-300 bg-zinc-50 px-3 py-2 text-sm text-zinc-900 placeholder:text-zinc-500 focus:border-zinc-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100 dark:placeholder:text-zinc-400"
                />
            </div>

            <flux:spacer />

            <a
                href="{{ route('workspaces.index') }}"
                wire:navigate
                class="inline-flex items-center gap-2 rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm font-medium text-zinc-800 transition hover:bg-zinc-100 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100 dark:hover:bg-zinc-800"
            >
                <span class="text-base leading-none">+</span>
                <span>Nouveau</span>
            </a>

            <flux:dropdown position="top" align="end" class="lg:hidden">
                <flux:profile :initials="auth()->user()->initials()" icon-trailing="chevron-down" />

                <flux:menu>
                    <flux:menu.radio.group>
                        <div class="p-0 text-sm font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                <flux:avatar :name="auth()->user()->name" :initials="auth()->user()->initials()" />

                                <div class="grid flex-1 text-start text-sm leading-tight">
                                    <flux:heading class="truncate">{{ auth()->user()->name }}</flux:heading>
                                    <flux:text class="truncate">{{ auth()->user()->email }}</flux:text>
                                </div>
                            </div>
                        </div>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <flux:menu.radio.group>
                        <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>
                            Parametres
                        </flux:menu.item>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item
                            as="button"
                            type="submit"
                            icon="arrow-right-start-on-rectangle"
                            class="w-full cursor-pointer"
                            data-test="logout-button"
                        >
                            Deconnexion
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:header>

        {{ $slot }}

        @fluxScripts
    </body>
</html>
