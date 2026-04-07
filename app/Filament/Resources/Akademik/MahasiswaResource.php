<?php

namespace App\Filament\Resources\Akademik;

use App\Filament\Resources\Akademik\MahasiswaResource\Pages\CreateMahasiswa;
use App\Filament\Resources\Akademik\MahasiswaResource\Pages\EditMahasiswa;
use App\Filament\Resources\Akademik\MahasiswaResource\Pages\ListMahasiswas;
use App\Helpers\Auth\UserHelper;
use App\Models\Akademik\Mahasiswa;
use App\Models\Akademik\PesertaDidik;
use App\Models\Ref\Unit;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class MahasiswaResource extends Resource
{
    protected static ?string $model = Mahasiswa::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function getNavigationLabel(): string
    {
        return __('mahasiswa.list'); // Label for navigation
    }

    public static function getModelLabel(): string
    {
        return __('mahasiswa'); // Label tunggal
    }

    public static function getPluralModelLabel(): string
    {
        return __('mahasiswa.list'); // Label jamak
    }

    public static function shouldRegisterNavigation(): bool
    {
        return UserHelper::userHasRole(['ADMIN', 'PIC', 'KAPRODI', 'KOORDINATOR']);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nrp')
                    ->label(__('nrp'))
                    ->required()
                    ->maxLength(20),
                TextInput::make('email')
                    ->label(__('Email'))
                    ->email()
                    ->maxLength(255),
                Select::make('peserta_didik_id')
                    ->label(__('mahasiswa.name'))
                    ->getSearchResultsUsing(fn (string $search): array => PesertaDidik::where('nama', 'ilike', "%{$search}%")->limit(50)->pluck('nama', 'id')->toArray())
                    ->getOptionLabelUsing(fn ($value): ?string => PesertaDidik::find($value)?->nama)
                    ->searchable(),
                FileUpload::make('attachment')
                    ->label(__('Picture Profile'))
                    ->disk('s3')
                    ->directory('mahasiswa_attachments')
                    ->storeFileNamesIn('attachment_file_names')
                    ->maxSize(10240) // Maksimum 10 MB
                    ->visibility('public')
                    ->nullable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('attachment')
                    ->label(__('Picture'))
                    ->disk('s3')
                    // ->directory('mahasiswa_attachments')
                    ->rounded()
                    ->size(50)
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('no')->state(
                    static function (HasTable $livewire, stdClass $rowLoop): string {
                        return (string) (
                            $rowLoop->iteration +
                            ($livewire->getTableRecordsPerPage() * (
                                $livewire->getTablePage() - 1
                            ))
                        );
                    }
                )
                    ->label(__('no'))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('nrp')
                    ->label(__('Identification Number'))
                    ->description(
                        fn (Mahasiswa $record) => $record->pesertaDidik ? $record->pesertaDidik->nama : 'N/A'
                    )
                    ->searchable()
                    ->sortable(),
                TextColumn::make('unit.nama')
                    ->label(__('prodi'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->label(__('Email'))
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('unit')
                    ->query(fn (Builder $query, array $data) => $query->whereHas(
                        'unit',
                        function (Builder $query) use ($data) {
                            $query = $query->unitUtama('UA');
                            if ($data['value'] !== null) {
                                $info = Unit::find($data['value']);

                                return $query->where('info_left', '>=', $info->info_left)
                                    ->where('info_right', '<=', $info->info_right);

                            }

                            return $query;
                        }
                    ))
                    ->options(
                        fn (): array => Unit::unitUtama('UA')
                            ->nama()
                            ->userUnit()
                            ->pluck('nama', 'id')
                            ->toArray()
                    )
                    ->label(__('prodi'))
                    ->selectablePlaceholder(false)
                    ->default(session('current_unit', null)),
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

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMahasiswas::route('/'),
            'create' => CreateMahasiswa::route('/create'),
            'edit' => EditMahasiswa::route('/{record}/edit'),
        ];
    }
}
