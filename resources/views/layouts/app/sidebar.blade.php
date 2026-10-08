<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('partials.head')
</head>
<body class="logic-app-body">
    <flux:sidebar sticky collapsible="mobile" class="logic-sidebar border-e border-zinc-200">
        <flux:sidebar.header>
            <x-app-logo :sidebar="true" href="{{ route('dashboard') }}" wire:navigate />
            <flux:sidebar.collapse class="lg:hidden" />
        </flux:sidebar.header>
        <flux:sidebar.nav>
            <flux:sidebar.group :heading="__('WORKSPACE')" class="grid">
                <flux:sidebar.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>Overview</flux:sidebar.item>
                <flux:sidebar.item icon="document-text" :href="route('documents.index')" :current="request()->routeIs('documents.*')" wire:navigate>Documents</flux:sidebar.item>
                <flux:sidebar.item icon="sparkles" :href="route('answers.create')" :current="request()->routeIs('answers.create')" wire:navigate>Ask a question</flux:sidebar.item>
                <flux:sidebar.item icon="clock" :href="route('answers.index')" :current="request()->routeIs('answers.index', 'answers.show')" wire:navigate>Answer history</flux:sidebar.item>
            </flux:sidebar.group>
            @if(auth()->user()->planAccess?->plan === 'sandbox' || ! auth()->user()->planAccess)
                <flux:sidebar.group :heading="__('GET STARTED')" class="grid">
                    <flux:sidebar.item icon="squares-2x2" :href="route('sandbox.index')" :current="request()->routeIs('sandbox.*')" wire:navigate>Sandbox guide</flux:sidebar.item>
                </flux:sidebar.group>
            @endif
        </flux:sidebar.nav>
        <flux:spacer />
        <div class="workspace-sidebar-foot">
            <span class="workspace-sidebar-foot-icon" aria-hidden="true"><i class="fa-solid fa-link"></i></span>
            <div><strong>Follow the evidence.</strong><small>Every answer starts with your sources.</small></div>
        </div>
        <x-desktop-user-menu class="hidden lg:block" :name="auth()->user()->name" />
    </flux:sidebar>
    <flux:header class="lg:hidden">
        <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />
        <span class="workspace-mobile-title">LogicStrand <span>/ Workspace</span></span>
        <flux:spacer />
        <flux:dropdown position="top" align="end">
            <flux:profile :initials="auth()->user()->initials()" icon-trailing="chevron-down" />
            <flux:menu>
                <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>Settings</flux:menu.item>
                <form method="POST" action="{{ route('logout') }}" class="w-full">@csrf<flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle">Log out</flux:menu.item></form>
            </flux:menu>
        </flux:dropdown>
    </flux:header>
    {{ $slot }}
    <x-toast-stack />
    @fluxScripts
    @include('partials.cookie-consent')
</body>
</html>
