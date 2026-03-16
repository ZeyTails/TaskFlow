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

            if (\Illuminate\Support\Facades\Schema::hasTable('user_notifications')) {
                $notificationsCount += \App\Models\UserNotification::query()
                    ->where('user_id', auth()->id())
                    ->whereNull('read_at')
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
                    <flux:sidebar.item icon="book-open-text" :href="route('reports')" :current="request()->routeIs('reports*')" wire:navigate>
                        Rapports
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

            <div class="hidden lg:flex items-center">
                <img src="{{ asset('storage/Logo UTT.png') }}" alt="Logo UTT" class="h-8 w-auto" />
            </div>

            <flux:spacer />

            <div class="flex items-center gap-2">
                <a
                    href="{{ route('notifications') }}"
                    wire:navigate
                    @class([
                        'relative inline-flex h-10 w-10 items-center justify-center rounded-lg border text-sm font-medium transition',
                        'border-zinc-900 bg-zinc-900 text-white hover:bg-zinc-800 dark:border-zinc-100 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200' => request()->routeIs('notifications'),
                        'border-zinc-300 bg-white text-zinc-800 hover:bg-zinc-100 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100 dark:hover:bg-zinc-800' => ! request()->routeIs('notifications'),
                    ])"
                    aria-label="Notifications"
                >
                    <flux:icon.bell class="size-4" />
                    @if ($notificationsCount > 0)
                        <span class="absolute -right-1 -top-1 inline-flex min-w-5 items-center justify-center rounded-full border border-blue-200 bg-blue-50 px-1.5 py-0.5 text-[10px] font-semibold leading-none text-blue-700 dark:border-blue-900/70 dark:bg-blue-900/20 dark:text-blue-300">
                            {{ $notificationsCount }}
                        </span>
                    @endif
                </a>

                <livewire:workspaces.quick-actions />
            </div>

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
