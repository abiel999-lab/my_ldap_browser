<x-filament-widgets::widget class="fi-filament-info-widget">
    <x-filament::section>
        <div class="flex w-full items-center justify-between">
            <div>
                <a href="#" rel="noopener noreferrer" target="_blank">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                        {{ config('app.name', 'Filament') }}
                    </h2>
                </a>

                <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                    {{ config('app.version', '1.0.0') }}
                </p>
            </div>

            <div class="flex flex-col items-end gap-y-1">
                <x-filament::link color="gray" href="{{ \App\Filament\Resources\Ldap\LdapUserManualResource::getUrl('index') }}" icon="heroicon-m-book-open"
                    icon-alias="panels::widgets.filament-info.open-documentation-button" rel="noopener noreferrer">
                    {{ __('User Manual') }}
                </x-filament::link>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
