<x-layouts::auth :title="__('Email verification')">
    <div class="flex flex-col gap-6">
        <x-auth-header :title="__('Check your inbox.')" :description="__('We sent a verification link to your email address. Open it to activate your workspace.')" />

        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <flux:button type="submit" variant="primary" class="w-full auth-submit">
                {{ __('Resend verification email') }}
            </flux:button>
        </form>

        <form method="POST" action="{{ route('logout') }}" class="text-center">
            @csrf
            <flux:button variant="ghost" type="submit" class="text-sm cursor-pointer" data-test="logout-button">
                {{ __('Log out') }}
            </flux:button>
        </form>
    </div>
</x-layouts::auth>
