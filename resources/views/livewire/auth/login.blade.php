<x-layouts::auth :title="__('Log in')">
    <div class="rounded-2xl border border-zinc-200 bg-white p-7 shadow-[0_1px_0_0_rgba(0,0,0,0.02),0_24px_50px_-30px_rgba(0,0,0,0.45)] dark:border-zinc-800 dark:bg-zinc-900 sm:p-8">
        <div class="flex flex-col gap-6">
            <x-auth-header :title="__('Welcome back')" :description="__('Use your account to continue on TaskFlow')" />

            <x-auth-session-status class="rounded-lg border border-zinc-200 bg-zinc-50 px-3 py-2 text-center text-sm text-zinc-700 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-200" :status="session('status')" />

            <form method="POST" action="{{ route('login.store') }}" class="flex flex-col gap-5">
                @csrf

                <flux:input
                    name="email"
                    :label="__('Email address')"
                    :value="old('email')"
                    type="email"
                    required
                    autofocus
                    autocomplete="email"
                    placeholder="email@loko.com"
                />

                <div class="relative">
                    <flux:input
                        name="password"
                        :label="__('Password')"
                        type="password"
                        required
                        autocomplete="current-password"
                        :placeholder="__('Password')"
                        viewable
                    />

                    @if (Route::has('password.request'))
                        <flux:link class="absolute end-0 top-0 text-sm text-zinc-500 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-100" :href="route('password.request')" wire:navigate>
                            {{ __('Forgot your password?') }}
                        </flux:link>
                    @endif
                </div>

                <flux:checkbox name="remember" :label="__('Remember me')" :checked="old('remember')" />

                <div class="flex items-center justify-end">
                    <button
                        type="submit"
                        class="inline-flex w-full items-center justify-center rounded-lg border border-black bg-black px-4 py-2.5 text-sm font-medium text-white transition hover:bg-zinc-800 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-black focus-visible:ring-offset-2 dark:border-zinc-100 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200 dark:focus-visible:ring-zinc-100 dark:focus-visible:ring-offset-zinc-900"
                        data-test="login-button"
                    >
                        {{ __('Log in') }}
                    </button>
                </div>
            </form>

            @if (Route::has('register'))
                <div class="space-x-1 text-center text-sm text-zinc-600 rtl:space-x-reverse dark:text-zinc-400">
                    <span>{{ __('Don\'t have an account?') }}</span>
                    <flux:link class="text-zinc-900 dark:text-zinc-100" :href="route('register')" wire:navigate>{{ __('Sign up') }}</flux:link>
                </div>
            @endif
        </div>
    </div>
</x-layouts::auth>
