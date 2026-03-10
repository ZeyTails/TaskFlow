<x-layouts::auth :title="__('Email verification')">
    <div class="rounded-2xl border border-zinc-200 bg-white p-7 shadow-[0_1px_0_0_rgba(0,0,0,0.02),0_24px_50px_-30px_rgba(0,0,0,0.45)] dark:border-zinc-800 dark:bg-zinc-900 sm:p-8">
        <div class="flex flex-col gap-6">
            <x-auth-header :title="__('Verify your email')" :description="__('Check your inbox and confirm your address to continue')" />

            <flux:text class="text-center text-zinc-700 dark:text-zinc-300">
                {{ __('Please verify your email address by clicking on the link we just emailed to you.') }}
            </flux:text>

            @if (session('status') == 'verification-link-sent')
                <flux:text class="rounded-lg border border-green-200 bg-green-50 px-3 py-2 text-center text-sm font-medium text-green-700 dark:border-green-900/60 dark:bg-green-900/20 dark:text-green-300">
                    {{ __('A new verification link has been sent to the email address you provided during registration.') }}
                </flux:text>
            @endif

            <div class="flex flex-col items-center justify-between space-y-3">
                <form method="POST" action="{{ route('verification.send') }}" class="w-full">
                    @csrf
                    <button
                        type="submit"
                        class="inline-flex w-full items-center justify-center rounded-lg border border-black bg-black px-4 py-2.5 text-sm font-medium text-white transition hover:bg-zinc-800 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-black focus-visible:ring-offset-2 dark:border-zinc-100 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200 dark:focus-visible:ring-zinc-100 dark:focus-visible:ring-offset-zinc-900"
                    >
                        {{ __('Resend verification email') }}
                    </button>
                </form>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <flux:button variant="ghost" type="submit" class="cursor-pointer text-sm text-zinc-600 hover:text-zinc-900 dark:text-zinc-300 dark:hover:text-zinc-100" data-test="logout-button">
                        {{ __('Log out') }}
                    </flux:button>
                </form>
            </div>
        </div>
    </div>
</x-layouts::auth>
