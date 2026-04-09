<?php

namespace App\Filament\Resources\Ldap\LdapSchemaBrowserResource\Pages;

use App\Filament\Resources\Ldap\LdapSchemaBrowserResource;
use App\Filament\Resources\Ldap\LdapSchemaBrowserResource\Pages\Concerns\HandlesSchemaTypeTable;
use App\Models\LdapEditableObjectClass;
use App\Models\LdapSchemaEntry;
use App\Services\Ldap\LdapSchemaBrowserService;
use App\Services\Ldap\LdapSchemaEditorService;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class ListLdapSchemaObjectClasses extends ListRecords
{
    use HandlesSchemaTypeTable;

    protected static string $resource = LdapSchemaBrowserResource::class;

    public function mount(): void
    {
        parent::mount();

        try {
            app(LdapSchemaEditorService::class)->syncSnapshots();
            app(LdapSchemaBrowserService::class)->clearCache();
        } catch (\Throwable $e) {
            Notification::make()
                ->title('Gagal sync LDAP schema')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    protected function getSchemaType(): string
    {
        return 'objectClass';
    }

    public function getTitle(): string
    {
        return 'Object Classes';
    }





    protected function resolveTableRecord(?string $key): ?Model
    {
        if (blank($key)) {
            return null;
        }

        return app(LdapSchemaBrowserService::class)->findById((string) $key);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('back')
                ->label('Back')
                ->icon('heroicon-o-arrow-left')
                ->url(LdapSchemaBrowserResource::getUrl('index')),

            Action::make('syncSchema')
                ->label('Sync LDAP Schema')
                ->icon('heroicon-o-arrow-path')
                ->color('gray')
                ->action(function (): void {
                    try {
                        $editor = app(LdapSchemaEditorService::class);
                        $browser = app(LdapSchemaBrowserService::class);

                        $editor->syncSnapshots();
                        $browser->clearCache();

                        Notification::make()
                            ->title('LDAP schema berhasil disinkronkan')
                            ->success()
                            ->send();

                        $this->redirect(LdapSchemaBrowserResource::getUrl('object-classes'));
                    } catch (\Throwable $e) {
                        Notification::make()
                            ->title('Gagal sync LDAP schema')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

            Action::make('createObjectClass')
                ->label('Create Object Class')
                ->icon('heroicon-o-plus')
                ->color('primary')
                ->modalHeading('Create Object Class')
                ->modalSubmitActionLabel('Save')
                ->form($this->getObjectClassFormSchema())
                ->action(function (array $data): void {
                    try {
                        $editor = app(LdapSchemaEditorService::class);
                        $browser = app(LdapSchemaBrowserService::class);

                        $editor->addObjectClass($data);
                        $editor->syncSnapshots();
                        $browser->clearCache();

                        Notification::make()
                            ->title('Object class berhasil ditambahkan')
                            ->success()
                            ->send();

                        $this->redirect(LdapSchemaBrowserResource::getUrl('object-classes'));
                    } catch (\Throwable $e) {
                        Notification::make()
                            ->title('Gagal menambahkan object class')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Name')
                    ->sortable()
                    ->searchable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('oid')
                    ->label('OID')
                    ->sortable()
                    ->searchable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('description')
                    ->label('Description')
                    ->wrap()
                    ->limit(80)
                    ->searchable(),

                Tables\Columns\TextColumn::make('sup')
                    ->label('SUP')
                    ->placeholder('-')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->formatStateUsing(fn ($state) => strtoupper((string) $state))
                    ->toggleable(),

                Tables\Columns\IconColumn::make('obsolete_flag')
                    ->label('Obsolete')
                    ->boolean()
                    ->state(fn (LdapSchemaEntry $record): bool => stripos((string) $record->raw, 'OBSOLETE') !== false),
            ])
            ->recordUrl(fn (LdapSchemaEntry $record): string => LdapSchemaBrowserResource::getUrl('detail', [
                'recordKey' => $record->id,
            ]))
            ->actions([
                Action::make('editObjectClass')
                    ->label('Edit')
                    ->icon('heroicon-o-pencil-square')
                    ->color('warning')
                    ->form($this->getObjectClassFormSchema())
                    ->fillForm(fn (LdapSchemaEntry $record): array => $this->mapRecordToObjectClassForm($record))
                    ->action(function (LdapSchemaEntry $record, array $data): void {
                        try {
                            $editor = app(LdapSchemaEditorService::class);
                            $browser = app(LdapSchemaBrowserService::class);

                            $editable = new LdapEditableObjectClass();
                            $editable->raw_definition = (string) $record->raw;

                            $editor->updateObjectClass($editable, $data);
                            $editor->syncSnapshots();
                            $browser->clearCache();

                            Notification::make()
                                ->title('Object class berhasil diupdate')
                                ->success()
                                ->send();

                            $this->redirect(LdapSchemaBrowserResource::getUrl('object-classes'));
                        } catch (\Throwable $e) {
                            Notification::make()
                                ->title('Gagal update object class')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),

                Action::make('deleteObjectClass')
                    ->label('Delete')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Delete Object Class')
                    ->modalDescription('Object class ini akan dihapus dari LDAP schema.')
                    ->action(function (LdapSchemaEntry $record): void {
                        try {
                            $editor = app(LdapSchemaEditorService::class);
                            $browser = app(LdapSchemaBrowserService::class);

                            $editable = new LdapEditableObjectClass();
                            $editable->raw_definition = (string) $record->raw;

                            $editor->deleteObjectClass($editable);
                            $editor->syncSnapshots();
                            $browser->clearCache();

                            Notification::make()
                                ->title('Object class berhasil dihapus')
                                ->success()
                                ->send();

                            $this->redirect(LdapSchemaBrowserResource::getUrl('object-classes'));
                        } catch (\Throwable $e) {
                            Notification::make()
                                ->title('Gagal menghapus object class')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
            ])
            ->bulkActions([])
            ->defaultSort('name');
    }

    protected function getObjectClassFormSchema(): array
    {
        return [
            Forms\Components\TextInput::make('oid')
                ->label('OID')
                ->required()
                ->maxLength(255)
                ->placeholder('1.3.6.1.4.1.x.x'),

            Forms\Components\Textarea::make('aliases_text')
                ->label('NAME / Aliases')
                ->required()
                ->rows(2)
                ->helperText('Pisahkan dengan koma. Contoh: petraPerson, petraUser'),

            Forms\Components\Textarea::make('description')
                ->label('Description')
                ->rows(2),

            Forms\Components\Textarea::make('sup_text')
                ->label('SUP')
                ->rows(2)
                ->helperText('Pisahkan dengan koma bila lebih dari satu. Contoh: top, person'),

            Forms\Components\Select::make('class_type')
                ->label('Class Type')
                ->required()
                ->options([
                    'STRUCTURAL' => 'STRUCTURAL',
                    'AUXILIARY' => 'AUXILIARY',
                    'ABSTRACT' => 'ABSTRACT',
                ])
                ->default('STRUCTURAL'),

            Forms\Components\Toggle::make('obsolete')
                ->label('Obsolete')
                ->default(false),

            Forms\Components\Textarea::make('must_text')
                ->label('MUST Attributes')
                ->rows(3)
                ->helperText('Pisahkan dengan koma. Contoh: cn, sn, uid'),

            Forms\Components\Textarea::make('may_text')
                ->label('MAY Attributes')
                ->rows(3)
                ->helperText('Pisahkan dengan koma. Contoh: mail, description, telephoneNumber'),
        ];
    }

    protected function mapRecordToObjectClassForm(LdapSchemaEntry $record): array
    {
        $raw = (string) $record->raw;

        return [
            'oid' => (string) ($record->oid ?? ''),
            'aliases_text' => implode(', ', $this->extractNames($raw)),
            'description' => (string) ($record->description ?? ''),
            'sup_text' => implode(', ', $this->extractListAfterKeyword($raw, 'SUP')),
            'class_type' => $this->extractObjectClassType($raw),
            'obsolete' => stripos($raw, 'OBSOLETE') !== false,
            'must_text' => implode(', ', $this->extractAttributeList($raw, 'MUST')),
            'may_text' => implode(', ', $this->extractAttributeList($raw, 'MAY')),
        ];
    }

    protected function extractNames(string $raw): array
    {
        if (preg_match("/NAME\s+'([^']+)'/i", $raw, $matches) === 1) {
            return [$matches[1]];
        }

        if (preg_match("/NAME\s+\(\s*([^)]+)\)/i", $raw, $matches) === 1) {
            preg_match_all("/'([^']+)'/", $matches[1], $nameMatches);

            return $nameMatches[1] ?? [];
        }

        return [];
    }

    protected function extractObjectClassType(string $raw): string
    {
        if (preg_match('/\bABSTRACT\b/i', $raw)) {
            return 'ABSTRACT';
        }

        if (preg_match('/\bAUXILIARY\b/i', $raw)) {
            return 'AUXILIARY';
        }

        return 'STRUCTURAL';
    }

    protected function extractAttributeList(string $raw, string $keyword): array
    {
        if (preg_match('/' . preg_quote($keyword, '/') . '\s+\(\s*([^)]+)\)/i', $raw, $matches) === 1) {
            return $this->normalizeDollarList($matches[1]);
        }

        if (preg_match('/' . preg_quote($keyword, '/') . '\s+([a-zA-Z0-9._-]+)/i', $raw, $matches) === 1) {
            return [$matches[1]];
        }

        return [];
    }

    protected function extractListAfterKeyword(string $raw, string $keyword): array
    {
        if (preg_match('/' . preg_quote($keyword, '/') . '\s+\(\s*([^)]+)\)/i', $raw, $matches) === 1) {
            return $this->normalizeDollarList($matches[1]);
        }

        if (preg_match('/' . preg_quote($keyword, '/') . '\s+([a-zA-Z0-9._-]+)/i', $raw, $matches) === 1) {
            return [$matches[1]];
        }

        return [];
    }

    protected function normalizeDollarList(string $value): array
    {
        return collect(explode('$', $value))
            ->map(fn ($item) => trim(str_replace(["'", '"'], '', $item)))
            ->filter()
            ->values()
            ->all();
    }
}
