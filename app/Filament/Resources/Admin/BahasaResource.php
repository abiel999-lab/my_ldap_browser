<?php

namespace App\Filament\Resources\Admin;

use App\Filament\Clusters\Admin;
use App\Filament\Resources\Admin\BahasaResource\Pages\ManageBahasas;
use App\Helpers\Auth\UserHelper;
use App\Models\Ref\Bahasa;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class BahasaResource extends Resource
{
    protected static ?string $model = Bahasa::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-language';

    protected static ?string $cluster = Admin::class;

    public static function getPluralLabel(): ?string
    {
        return __('Language References');
    }

    public static function getModelLabel(): string
    {
        return __('Language Reference');
    }

    public static function shouldRegisterNavigation(): bool
    {
        return UserHelper::userHasRole(['ADMIN', 'PIC']);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('key')
                    ->label(__('Key'))
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
                TextInput::make('value_en')
                    ->label(__('Value (English)'))
                    ->required()
                    ->maxLength(255),
                TextInput::make('value_id')
                    ->label(__('Value (Indonesian)'))
                    ->required()
                    ->maxLength(255),
                TextInput::make('value_zh')
                    ->label(__('Value (Chinese)'))
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Split::make([
                    TextColumn::make('key')
                        ->label(__('Key'))
                        ->searchable()
                        ->sortable(),
                    Stack::make([
                        TextColumn::make('value_en')
                            ->label(__('Value (English)'))
                            ->prefix('EN: ')
                            ->searchable()
                            ->sortable(),
                        TextColumn::make('value_id')
                            ->label(__('Value (Indonesian)'))
                            ->prefix('ID: ')
                            ->searchable()
                            ->sortable(),
                        TextColumn::make('value_zh')
                            ->label(__('Value (Chinese)'))
                            ->prefix('ZH: ')
                            ->searchable()
                            ->sortable(),
                    ]),
                ]),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageBahasas::route('/'),
        ];
    }
}
