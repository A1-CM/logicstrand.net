@props(['sidebar' => false])
@if($sidebar)
    <flux:sidebar.brand :name="config('app.name', 'LogicStrand')" {{ $attributes }}>
        <x-slot name="logo" class="flex aspect-square size-9 items-center justify-center rounded-lg bg-[#3157d8] text-white">
            <x-app-logo-icon class="size-6 text-white" />
        </x-slot>
    </flux:sidebar.brand>
@else
    <flux:brand :name="config('app.name', 'LogicStrand')" {{ $attributes }}>
        <x-slot name="logo" class="flex aspect-square size-9 items-center justify-center rounded-lg bg-[#3157d8] text-white">
            <x-app-logo-icon class="size-6 text-white" />
        </x-slot>
    </flux:brand>
@endif
