<?php

namespace App\Filament\Resources\Ldap\LdapAppViewResource\Pages;

use App\Filament\Resources\Ldap\LdapAppViewResource;
use App\Models\LdapAppRoleView;
use App\Services\Ldap\LdapAppRoleService;
use App\Services\Ldap\LdapAppSyncService;
use Filament\Actions;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ListLdapAppViews extends ListRecords
{
    protected static string $resource = LdapAppViewResource::class;

    public ?string $app = null;

    public function mount(): void
    {
        $this->app = request()->query('app');
    }

    public function getTitle(): string
    {
        return $this->app
            ? 'App Roles - ' . $this->app
            : 'App Roles';
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('backToApps')
                ->label('Back')
                ->icon('heroicon-o-arrow-left')
                ->color('gray')
                ->visible(fn () => filled($this->app))
                ->url(static::getResource()::getUrl('index')),

            Actions\Action::make('createApp')
                ->label('Create App')
                ->icon('heroicon-o-plus')
                ->color('primary')
                ->visible(fn () => blank($this->app))
                ->form([
                    TextInput::make('cn')
                        ->label('App CN')
                        ->required()
                        ->placeholder('app-web-baru'),
                    Textarea::make('description')
                        ->label('Description')
                        ->rows(3),
                ])
                ->action(function (array $data) {
                    try {
                        app(LdapAppRoleService::class)->createApp(
                            (string) $data['cn'],
                            $data['description'] ?? null
                        );

                        Notification::make()
                            ->title('App created successfully')
                            ->success()
                            ->send();

                        $this->redirect(static::getResource()::getUrl('index'));
                    } catch (\Throwable $e) {
                        Notification::make()
                            ->title('Failed to create app')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

            Actions\Action::make('createRoleInApp')
                ->label('Create Role in App')
                ->icon('heroicon-o-plus-circle')
                ->color('gray')
                ->form([
                    TextInput::make('app_cn')
                        ->label('App CN')
                        ->required()
                        ->default(fn () => $this->app),
                    TextInput::make('role_cn')
                        ->label('Role CN')
                        ->required()
                        ->placeholder('alumni-role-web'),
                ])
                ->action(function (array $data) {
                    try {
                        app(LdapAppRoleService::class)->createRole(
                            (string) $data['app_cn'],
                            (string) $data['role_cn']
                        );

                        Notification::make()
                            ->title('Role created in app successfully')
                            ->success()
                            ->send();

                        $this->redirect(static::getResource()::getUrl('index', [
                            'app' => $data['app_cn'],
                        ]));
                    } catch (\Throwable $e) {
                        Notification::make()
                            ->title('Failed to create role in app')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

            Actions\Action::make('syncApps')
                ->label('Sync Apps')
                ->icon('heroicon-o-arrow-path')
                ->color('gray')
                ->action(function () {
                    app(LdapAppSyncService::class)->sync();

                    Notification::make()
                        ->title('Apps synced successfully')
                        ->success()
                        ->send();

                    $this->redirect(request()->fullUrl());
                }),
        ];
    }

    public function table(Table $table): Table
    {
        if (blank($this->app)) {
            return parent::table($table);
        }

        return $table
            ->query(
                LdapAppRoleView::query()->where('app_cn', $this->app)
            )
            ->striped()
            ->columns([
                TextColumn::make('role_cn')
                    ->label('Role')
                    ->searchable()
                    ->sortable()
                    ->weight('600'),

                TextColumn::make('member_count')
                    ->label('Members')
                    ->sortable(),

                TextColumn::make('synced_at')
                    ->label('Synced')
                    ->dateTime('Y-m-d H:i:s')
                    ->sortable(),
            ])
            ->defaultSort('role_cn')
            ->recordUrl(fn ($record) => \App\Filament\Resources\Ldap\LdapAppRoleMemberViewResource::getUrl('index', [
                'app' => $record->app_cn,
                'role' => $record->role_cn,
            ]));
    }

    protected function getTableQuery(): Builder
    {
        if (blank($this->app)) {
            return parent::getTableQuery();
        }

        return LdapAppRoleView::query()->where('app_cn', $this->app);
    }
}
