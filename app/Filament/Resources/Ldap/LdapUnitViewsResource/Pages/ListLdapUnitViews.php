<?php

namespace App\Filament\Resources\Ldap\LdapUnitViewResource\Pages;

use App\Filament\Resources\Ldap\LdapUnitViewResource;
use App\Models\LdapUnitView;
use App\Services\Ldap\LdapUnitService;
use App\Services\Ldap\LdapUnitSyncService;
use Filament\Actions;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListLdapUnitViews extends ListRecords
{
    protected static string $resource = LdapUnitViewResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('createUnit')
                ->label('Create Unit')
                ->icon('heroicon-o-plus')
                ->color('primary')
                ->form([
                    TextInput::make('cn')
                        ->label('Unit CN')
                        ->required()
                        ->placeholder('ppsi'),
                    Textarea::make('description')
                        ->label('Description')
                        ->rows(3),
                ])
                ->action(function (array $data) {
                    try {
                        app(LdapUnitService::class)->create(
                            (string) $data['cn'],
                            $data['description'] ?? null
                        );

                        Notification::make()
                            ->title('Unit created successfully')
                            ->success()
                            ->send();

                        $this->redirect(static::getResource()::getUrl('index'));
                    } catch (\Throwable $e) {
                        Notification::make()
                            ->title('Failed to create unit')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

            Actions\Action::make('editUnit')
                ->label('Edit Unit')
                ->icon('heroicon-o-pencil-square')
                ->color('gray')
                ->form([
                    Select::make('dn')
                        ->label('Select Unit')
                        ->options(fn () => LdapUnitView::query()->orderBy('cn')->pluck('cn', 'dn')->toArray())
                        ->searchable()
                        ->required(),
                    TextInput::make('cn')
                        ->label('New Unit CN')
                        ->required(),
                    Textarea::make('description')
                        ->label('Description')
                        ->rows(3),
                ])
                ->action(function (array $data) {
                    try {
                        app(LdapUnitService::class)->update(
                            (string) $data['dn'],
                            (string) $data['cn'],
                            $data['description'] ?? null
                        );

                        Notification::make()
                            ->title('Unit updated successfully')
                            ->success()
                            ->send();

                        $this->redirect(static::getResource()::getUrl('index'));
                    } catch (\Throwable $e) {
                        Notification::make()
                            ->title('Failed to update unit')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

            Actions\Action::make('deleteUnit')
                ->label('Delete Unit')
                ->icon('heroicon-o-trash')
                ->color('danger')
                ->requiresConfirmation()
                ->form([
                    Select::make('dn')
                        ->label('Select Unit')
                        ->options(fn () => LdapUnitView::query()->orderBy('cn')->pluck('cn', 'dn')->toArray())
                        ->searchable()
                        ->required(),
                ])
                ->action(function (array $data) {
                    try {
                        app(LdapUnitService::class)->delete((string) $data['dn']);

                        Notification::make()
                            ->title('Unit deleted successfully')
                            ->success()
                            ->send();

                        $this->redirect(static::getResource()::getUrl('index'));
                    } catch (\Throwable $e) {
                        Notification::make()
                            ->title('Failed to delete unit')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

            Actions\Action::make('syncUnits')
                ->label('Sync Units')
                ->icon('heroicon-o-arrow-path')
                ->color('gray')
                ->action(function () {
                    app(LdapUnitSyncService::class)->sync();

                    Notification::make()
                        ->title('Units synced successfully')
                        ->success()
                        ->send();

                    $this->redirect(static::getResource()::getUrl('index'));
                }),
        ];
    }
}
