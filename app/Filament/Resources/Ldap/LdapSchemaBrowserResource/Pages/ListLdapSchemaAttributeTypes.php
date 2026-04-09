<?php

namespace App\Filament\Resources\Ldap\LdapSchemaBrowserResource\Pages;

use App\Filament\Resources\Ldap\LdapSchemaBrowserResource;
use App\Filament\Resources\Ldap\LdapSchemaBrowserResource\Pages\Concerns\HandlesSchemaTypeTable;
use App\Models\LdapEditableAttributeType;
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

class ListLdapSchemaAttributeTypes extends ListRecords
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
        return 'attributeType';
    }

    public function getTitle(): string
    {
        return 'Attribute Types';
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

                        $this->redirect(LdapSchemaBrowserResource::getUrl('attribute-types'));
                    } catch (\Throwable $e) {
                        Notification::make()
                            ->title('Gagal sync LDAP schema')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

            Action::make('createAttributeType')
                ->label('Create Attribute Type')
                ->icon('heroicon-o-plus')
                ->color('primary')
                ->modalHeading('Create Attribute Type')
                ->modalSubmitActionLabel('Save')
                ->form($this->getAttributeTypeFormSchema())
                ->action(function (array $data): void {
                    try {
                        $editor = app(LdapSchemaEditorService::class);
                        $browser = app(LdapSchemaBrowserService::class);

                        $editor->addAttributeType($data);
                        $editor->syncSnapshots();
                        $browser->clearCache();

                        Notification::make()
                            ->title('Attribute type berhasil ditambahkan')
                            ->success()
                            ->send();

                        $this->redirect(LdapSchemaBrowserResource::getUrl('attribute-types'));
                    } catch (\Throwable $e) {
                        Notification::make()
                            ->title('Gagal menambahkan attribute type')
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

                Tables\Columns\TextColumn::make('usage')
                    ->label('Usage')
                    ->state(fn (LdapSchemaEntry $record): ?string => $this->extractUsage((string) $record->raw))
                    ->placeholder('-')
                    ->toggleable(),

                Tables\Columns\IconColumn::make('single_value_flag')
                    ->label('Single Value')
                    ->boolean()
                    ->state(fn (LdapSchemaEntry $record): bool => stripos((string) $record->raw, 'SINGLE-VALUE') !== false),

                Tables\Columns\IconColumn::make('no_user_modification_flag')
                    ->label('Read Only')
                    ->boolean()
                    ->state(fn (LdapSchemaEntry $record): bool => stripos((string) $record->raw, 'NO-USER-MODIFICATION') !== false),

                Tables\Columns\IconColumn::make('obsolete_flag')
                    ->label('Obsolete')
                    ->boolean()
                    ->state(fn (LdapSchemaEntry $record): bool => stripos((string) $record->raw, 'OBSOLETE') !== false),
            ])
            ->recordUrl(fn (LdapSchemaEntry $record): string => LdapSchemaBrowserResource::getUrl('detail', [
                'recordKey' => $record->id,
            ]))
            ->actions([
                Action::make('editAttributeType')
                    ->label('Edit')
                    ->icon('heroicon-o-pencil-square')
                    ->color('warning')
                    ->form($this->getAttributeTypeFormSchema())
                    ->fillForm(fn (LdapSchemaEntry $record): array => $this->mapRecordToAttributeTypeForm($record))
                    ->action(function (LdapSchemaEntry $record, array $data): void {
                        try {
                            $editor = app(LdapSchemaEditorService::class);
                            $browser = app(LdapSchemaBrowserService::class);

                            $editable = LdapEditableAttributeType::query()
                                ->where('oid', (string) $record->oid)
                                ->first();

                            if (! $editable) {
                                throw new \RuntimeException('Attribute type editable tidak ditemukan berdasarkan OID.');
                            }

                            $editor->updateAttributeType($editable, $data);
                            $editor->syncSnapshots();
                            $browser->clearCache();

                            Notification::make()
                                ->title('Attribute type berhasil diupdate')
                                ->success()
                                ->send();

                            $this->redirect(LdapSchemaBrowserResource::getUrl('attribute-types'));
                        } catch (\Throwable $e) {
                            Notification::make()
                                ->title('Gagal update attribute type')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),

                Action::make('deleteAttributeType')
                    ->label('Delete')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Delete Attribute Type')
                    ->modalDescription('Attribute type ini akan dihapus dari LDAP schema.')
                    ->action(function (LdapSchemaEntry $record): void {
                        try {
                            $editor = app(LdapSchemaEditorService::class);
                            $browser = app(LdapSchemaBrowserService::class);

                            $editable = LdapEditableAttributeType::query()
                                ->where('oid', (string) $record->oid)
                                ->first();

                            if (! $editable) {
                                throw new \RuntimeException('Attribute type editable tidak ditemukan berdasarkan OID.');
                            }

                            $editor->deleteAttributeType($editable);
                            $editor->syncSnapshots();
                            $browser->clearCache();

                            Notification::make()
                                ->title('Attribute type berhasil dihapus')
                                ->success()
                                ->send();

                            $this->redirect(LdapSchemaBrowserResource::getUrl('attribute-types'));
                        } catch (\Throwable $e) {
                            Notification::make()
                                ->title('Gagal menghapus attribute type')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
            ])
            ->bulkActions([])
            ->defaultSort('name');
    }

    protected function getAttributeTypeFormSchema(): array
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
                ->helperText('Pisahkan dengan koma. Contoh: petraBuildingName, petraCode'),

            Forms\Components\Textarea::make('description')
                ->label('Description')
                ->rows(2),

            Forms\Components\TextInput::make('sup')
                ->label('SUP')
                ->maxLength(255)
                ->placeholder('name'),

            Forms\Components\TextInput::make('equality')
                ->label('Equality')
                ->maxLength(255)
                ->placeholder('caseIgnoreMatch'),

            Forms\Components\TextInput::make('ordering')
                ->label('Ordering')
                ->maxLength(255)
                ->placeholder('caseIgnoreOrderingMatch'),

            Forms\Components\TextInput::make('substr')
                ->label('Substr')
                ->maxLength(255)
                ->placeholder('caseIgnoreSubstringsMatch'),

            Forms\Components\TextInput::make('syntax')
                ->label('Syntax')
                ->maxLength(255)
                ->placeholder('1.3.6.1.4.1.1466.115.121.1.15'),

            Forms\Components\Select::make('usage')
                ->label('Usage')
                ->options([
                    '' => '-',
                    'userApplications' => 'userApplications',
                    'directoryOperation' => 'directoryOperation',
                    'distributedOperation' => 'distributedOperation',
                    'dSAOperation' => 'dSAOperation',
                ])
                ->default('userApplications'),

            Forms\Components\Toggle::make('single_value')
                ->label('Single Value')
                ->default(false),

            Forms\Components\Toggle::make('no_user_modification')
                ->label('Read Only / No User Modification')
                ->default(false),

            Forms\Components\Toggle::make('obsolete')
                ->label('Obsolete')
                ->default(false),
        ];
    }

    protected function mapRecordToAttributeTypeForm(LdapSchemaEntry $record): array
    {
        $raw = (string) $record->raw;

        return [
            'oid' => (string) ($record->oid ?? ''),
            'aliases_text' => implode(', ', $this->extractNames($raw)),
            'description' => (string) ($record->description ?? ''),
            'sup' => (string) ($this->match('/SUP\s+([a-zA-Z0-9._-]+)/i', $raw) ?? ''),
            'equality' => (string) ($this->match('/EQUALITY\s+([a-zA-Z0-9._-]+)/i', $raw) ?? ''),
            'ordering' => (string) ($this->match('/ORDERING\s+([a-zA-Z0-9._-]+)/i', $raw) ?? ''),
            'substr' => (string) ($this->match('/SUBSTR\s+([a-zA-Z0-9._-]+)/i', $raw) ?? ''),
            'syntax' => (string) ($this->match('/SYNTAX\s+([a-zA-Z0-9.{}-]+)/i', $raw) ?? ''),
            'usage' => (string) ($this->extractUsage($raw) ?? ''),
            'single_value' => stripos($raw, 'SINGLE-VALUE') !== false,
            'no_user_modification' => stripos($raw, 'NO-USER-MODIFICATION') !== false,
            'obsolete' => stripos($raw, 'OBSOLETE') !== false,
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

    protected function extractUsage(string $raw): ?string
    {
        return $this->match('/USAGE\s+([a-zA-Z0-9_-]+)/i', $raw);
    }

    protected function match(string $pattern, string $raw): ?string
    {
        return preg_match($pattern, $raw, $matches) === 1
            ? trim($matches[1])
            : null;
    }
}
