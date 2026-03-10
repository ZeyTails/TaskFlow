<x-layouts::auth :title="__('Reset password')">
    <div class="rounded-2xl border border-zinc-200 bg-white p-7 shadow-[0_1px_0_0_rgba(0,0,0,0.02),0_24px_50px_-30px_rgba(0,0,0,0.45)] dark:border-zinc-800 dark:bg-zinc-900 sm:p-8">
        <div class="flex flex-col gap-6">
            <x-auth-header :title="__('Reset password')" :description="__('Choose a new secure password')" />

            <x-auth-session-status class="rounded-lg border border-zinc-200 bg-zinc-50 px-3 py-2 text-center text-sm text-zinc-700 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-200" :status="session('status')" />

            <form method="POST" action="{{ route('password.update') }}" class="flex flex-col gap-5">
                @csrf
                <input type="hidden" name="token" value="{{ request()->route('token') }}">

                <flux:input
                    name="email"
                    value="{{ request('email') }}"
                    :label="__('Email')"
                    type="email"
                    required
                    autocomplete="email"
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
                        data-test="reset-password-button"
                    >
                        {{ __('Reset password') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-layouts::auth>
