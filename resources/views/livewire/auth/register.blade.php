<x-layouts::auth :title="__('Register')">
    <div class="rounded-2xl border border-zinc-200 bg-white p-7 shadow-[0_1px_0_0_rgba(0,0,0,0.02),0_24px_50px_-30px_rgba(0,0,0,0.45)] dark:border-zinc-800 dark:bg-zinc-900 sm:p-8">
        <div class="flex flex-col gap-6">
            <x-auth-header :title="__('Create an account')" :description="__('Start organizing your team work on TaskFlow')" />

            <x-auth-session-status class="rounded-lg border border-zinc-200 bg-zinc-50 px-3 py-2 text-center text-sm text-zinc-700 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-200" :status="session('status')" />

            <form method="POST" action="{{ route('register.store') }}" class="flex flex-col gap-5">
                @csrf
                <div class="grid gap-4 sm:grid-cols-2">
                    <flux:input
                        name="first_name"
                        :label="__('First name')"
                        :value="old('first_name')"
                        type="text"
                        required
                        autofocus
                        autocomplete="given-name"
                        :placeholder="__('Leila')"
                    />

                    <flux:input
                        name="last_name"
                        :label="__('Last name')"
                        :value="old('last_name')"
                        type="text"
                        required
                        autocomplete="family-name"
                        :placeholder="__('Konan')"
                    />
                </div>

                <flux:input
                    name="email"
                    :label="__('Email address')"
                    :value="old('email')"
                    type="email"
                    required
                    autocomplete="email"
                    placeholder="email@loko.com"
                />

                <flux:input
                    name="password"
                    :label="__('Password')"
                    type="password"
                    required
                    autocomplete="new-password"
                    :placeholder="__('Password')"
                    viewable
                />

                <flux:input
                    name="password_confirmation"
                    :label="__('Confirm password')"
                    type="password"
                    required
                    autocomplete="new-password"
                    :placeholder="__('Confirm password')"
                    viewable
                />

                <div class="flex items-center justify-end">
                    <button
                        type="submit"
                        class="inline-flex w-full items-center justify-center rounded-lg border border-black bg-black px-4 py-2.5 text-sm font-medium text-white transition hover:bg-zinc-800 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-black focus-visible:ring-offset-2 dark:border-zinc-100 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200 dark:focus-visible:ring-zinc-100 dark:focus-visible:ring-offset-zinc-900"
                        data-test="register-user-button"
                    >
                        {{ __('Create account') }}
                    </button>
                </div>
            </form>

            <div class="space-x-1 rtl:space-x-reverse text-center text-sm text-zinc-600 dark:text-zinc-400">
                <span>{{ __('Already have an account?') }}</span>
                <flux:link class="text-zinc-900 dark:text-zinc-100" :href="route('login')" wire:navigate>{{ __('Log in') }}</flux:link>
            </div>
        </div>
    </div>
</x-layouts::auth>
