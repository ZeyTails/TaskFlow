<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white font-sans text-zinc-900 antialiased dark:bg-zinc-950 dark:text-zinc-100">
        @php
            $isAuthPrimary = request()->routeIs('login', 'login.*', 'register', 'register.*');
        @endphp

        <div class="relative flex min-h-svh items-center justify-center overflow-hidden bg-white p-6 md:p-10 dark:bg-zinc-950">
            <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_top,_#fafafa_0%,_#ffffff_55%)] dark:bg-[radial-gradient(circle_at_top,_#171717_0%,_#09090b_55%)]"></div>
            <div class="pointer-events-none absolute inset-0 [background-image:linear-gradient(to_right,#f5f5f5_1px,transparent_1px),linear-gradient(to_bottom,#f5f5f5_1px,transparent_1px)] [background-size:36px_36px] dark:[background-image:linear-gradient(to_right,#27272a_1px,transparent_1px),linear-gradient(to_bottom,#27272a_1px,transparent_1px)]"></div>

            <div class="absolute right-6 top-6 z-10" x-data>
                <button
                    type="button"
                    class="inline-flex items-center gap-2 rounded-lg border border-zinc-200 bg-white px-3 py-1.5 text-xs font-medium text-zinc-700 transition hover:bg-zinc-100 dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-200 dark:hover:bg-zinc-800"
                    @click="
                        const order = ['light', 'dark', 'system'];
                        const current = $flux.appearance || 'system';
                        const next = order[(order.indexOf(current) + 1) % order.length];
                        $flux.appearance = next;
                    "
                >
                    <span x-show="($flux.appearance || 'system') === 'dark' || (($flux.appearance || 'system') === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches)">
                        @svg('icon-moon', 'size-3.5 opacity-80')
                    </span>
                    <span x-show="($flux.appearance || 'system') === 'light' || (($flux.appearance || 'system') === 'system' && !window.matchMedia('(prefers-color-scheme: dark)').matches)">
                        @svg('icon-sun', 'size-3.5 opacity-80')
                    </span>
                    <span>Theme:</span>
                    <span class="capitalize" x-text="$flux.appearance || 'system'"></span>
                </button>
            </div>

            <div @class([
                'relative flex w-full flex-col gap-5',
                'max-w-md' => $isAuthPrimary,
                'max-w-sm' => ! $isAuthPrimary,
            ])>
                <a href="{{ route('home') }}" class="mx-auto text-zinc-900 dark:text-zinc-100" wire:navigate>
                    <x-taskflow-logo class="h-auto w-[200px]" />
                </a>

                <div class="flex flex-col gap-6">
                    {{ $slot }}
                </div>
            </div>
        </div>
        @fluxScripts
    </body>
</html>
