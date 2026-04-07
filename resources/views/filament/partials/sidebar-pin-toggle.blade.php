@php
    $inSidebar = (bool) ($inSidebar ?? false);
@endphp

<div x-data="{
    pinned: false,
    init() {
        const current = document.documentElement.getAttribute('data-sidebar-pinned');
        this.pinned = current === '1';
    },
    togglePin() {
        try {
            const key = 'spms:sidebar:pinned';
            const next = this.pinned ? '0' : '1';

            this.pinned = next === '1';
            document.documentElement.setAttribute('data-sidebar-pinned', next);
            localStorage.setItem(key, next);
        } catch (e) {
            // no-op
        }
    },
}" class="spms-sidebar-pin-toggle {{ $inSidebar ? 'spms-sidebar-pin-toggle--sidebar' : '' }}">
    <x-filament::icon-button color="gray" :icon="\Filament\Support\Icons\Heroicon::OutlinedBars3" icon-size="md" :label="__('filament-panels::layout.actions.sidebar.expand.label')" x-cloak x-show="! pinned"
        x-on:click="togglePin" x-bind:aria-pressed="pinned ? 'true' : 'false'" class="spms-sidebar-pin-toggle__btn" />

    <x-filament::icon-button color="gray" :icon="\Filament\Support\Icons\Heroicon::OutlinedXMark" icon-size="md" :label="__('filament-panels::layout.actions.sidebar.collapse.label')" x-cloak x-show="pinned"
        x-on:click="togglePin" x-bind:aria-pressed="pinned ? 'true' : 'false'" class="spms-sidebar-pin-toggle__btn" />
</div>
