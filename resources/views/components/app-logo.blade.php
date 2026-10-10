@props(['sidebar' => false])
@if ($sidebar)
    <flux:sidebar.brand name="" {{ $attributes }}>
        <x-slot name="logo" class="app-brand-slot">
            <img class="app-brand-lockup in-data-flux-sidebar-collapsed-desktop:hidden" src="{{ asset('images/logicstrand/logo.webp') }}" alt="strand" width="3168" height="899">
            <img class="app-brand-mark hidden in-data-flux-sidebar-collapsed-desktop:block" src="{{ asset('images/logicstrand/logo-mark.webp') }}" alt="" width="1680" height="899">
        </x-slot>
    </flux:sidebar.brand>
@else
    <flux:brand name="" {{ $attributes }}>
        <x-slot name="logo" class="app-brand-slot">
            <img class="app-brand-lockup" src="{{ asset('images/logicstrand/logo.webp') }}" alt="strand" width="3168" height="899">
        </x-slot>
    </flux:brand>
@endif
