<?php

namespace App\Filament\Resources\Admin\UserResource\Pages;

use App\Filament\Resources\Admin\UserResource;
use App\Helpers\Auth\UserHelper;
use App\Models\Admin\UserUnit;
use App\Models\Gate\Role;
use App\Models\Gate\User;
use App\Models\Ref\Unit;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ListUserUnits extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected array $userIds = [];

    protected $userUnits = [];

    public function table(Table $table): Table
    {
        $this->loadCache();

        // Ambil semua unit sekaligus (untuk lookup)
        $units = Unit::get()->keyBy('id');

        // Ambil Units Utama untuk Options Select
        $unitUtamas = Unit::unitUtama()->nama()->pluck('nama', 'id')->toArray();

        return $table
            ->records(fn () => User::query()
                    ->whereIn('id', $this->userIds)
                    ->orderBy('nama')
                    ->get()
            )
            ->columns([
                // -----------kolom yang ditampilkan di table
                TextColumn::make('nama')
                    ->label(__('Name'))
                    ->searchable()
                    ->sortable(),
                // -----------kolom unit melakukan subquery untuk mengambil nama unit dari relasi user_unit → unit
                TextColumn::make('unit_nama')
                    ->label(__('Unit'))
                    ->getStateUsing(function ($record) use ($units) {
                        // Ambil nama unit lewat relasi user_unit → unit
                        $unitIds = optional($this->userUnits[$record->id])->pluck('unit_id') ?? collect();

                        return $unitIds
                            ->map(fn ($id) => $units[$id]->nama ?? null)
                            ->filter()
                            ->implode(', ');
                    })
                    ->wrap(),
            ])
            ->recordActions([
                // -----------tombol aksi yang ditampilkan di table
                // -----------tombol edit untuk mengedit user unit
                EditAction::make()
                    ->schema(fn (User $record) => [
                        Select::make('user_id')
                            ->label(__('User'))
                            ->placeholder(__('Select User'))
                            ->options(User::pegawai()->aktif()->pluck('nama', 'id'))
                            ->afterStateHydrated(function ($component, $state) use ($record) {
                                $component->state($record->id);
                            })
                            ->disabled(),

                        Select::make('unit_id')
                            ->label(__('Unit'))
                            ->placeholder(__('Select Units'))
                            ->multiple()
                            ->required()
                            ->searchable()
                            ->options($unitUtamas)
                            ->afterStateHydrated(function ($component, $state) use ($record) {
                                $unitIds = optional($this->userUnits[$record->id])->pluck('unit_id')->toArray();
                                $component->state($unitIds); // set state field dengan array unit_ids
                            }),
                    ])
                    ->action(function (User $record, array $data) {
                        $this->actionUpdate($record, $data);
                    }),

                DeleteAction::make()
                    ->action(function (User $record) {
                        $this->actionDelete($record);
                    }),
            ])
            ->headerActions([
                CreateAction::make()
                    ->schema([
                        Select::make('user_id')
                            ->label(__('User'))
                            ->placeholder(__('Select User'))
                            ->options(User::pegawai()->aktif()->pluck('nama', 'id'))
                            ->searchable()
                            ->required(),

                        Select::make('unit_id')
                            ->label(__('Unit'))
                            ->placeholder(__('Select Units'))
                            ->multiple()
                            ->required()
                            ->options($unitUtamas)
                            ->searchable(),
                    ])
                    ->action(function (array $data) {
                        $this->actionCreate($data);
                    })
                    ->label(__('Add User Unit')),
            ]);
    }

    private function actionUpdate(User $record, array $data): void
    {
        // -------cari id role koordinator
        $koordinatorRole = Role::where('kode', 'KOORDINATOR')->first();
        if (! $koordinatorRole) {
            Notification::make()
                ->title('Error')
                ->body(__('message.user_unit.error.role_not_found'))
                ->danger()
                ->send();

            return;
        }
        $listUnit = [];
        foreach ($data['unit_id'] as $unitId) {
            // -----------cek apakah user sudah ada di unit ini
            if (! UserUnit::where('user_id', $record->id)->where('unit_id', $unitId)->exists()) {
                // -----------tambah user unit
                $save = UserUnit::create([
                    'user_id' => $record->id,
                    'unit_id' => $unitId,
                    'role_id' => $koordinatorRole->id,
                ]);
                if (! $save) {
                    Notification::make()
                        ->title('Error')
                        ->body(__('message.global.error.created'))
                        ->danger()
                        ->send();

                    return;
                }
            }
            $listUnit[] = $unitId;
        }

        // -----------hapus user unit yang tidak ada di list unit
        UserUnit::where('user_id', $record->id)
            ->whereNotIn('unit_id', $listUnit)
            ->delete();

        Notification::make()
            ->title(__('message.global.success.updated'))
            ->success()
            ->send();
        $this->refresh();
    }

    private function actionDelete(User $record): void
    {
        // -------cari id role koordinator
        $koordinatorRole = Role::where('kode', 'KOORDINATOR')->first();
        if (! $koordinatorRole) {
            Notification::make()
                ->title('Error')
                ->body(__('message.user_unit.error.role_not_found'))
                ->danger()
                ->send();

            return;
        }
        // -----------hapus user unit
        $delete = UserUnit::where('user_id', $record->id)->delete();
        if (! $delete) {
            Notification::make()
                ->title('Error')
                ->body(__('message.global.error.deleted'))
                ->danger()
                ->send();
        }

        if ($delete > 0) {
            // -----------hapus role dari user
            $deleteRole = UserHelper::deleteRole($record->id, $koordinatorRole->id);
            if ($deleteRole['status'] == '0') {
                Notification::make()
                    ->title('Error')
                    ->body(__('message.global.error.deleted'))
                    ->danger()
                    ->send();
            }
        }

        $this->refresh();
    }

    private function actionCreate(array $data): void
    {
        // -------cari id role koordinator
        $koordinatorRole = Role::where('kode', 'KOORDINATOR')->first();
        if (! $koordinatorRole) {
            Notification::make()
                ->title('Error')
                ->body(__('message.user_unit.error.role_not_found'))
                ->danger()
                ->send();

            return;
        }

        foreach ($data['unit_id'] as $unitId) {
            // -----------cek apakah user sudah ada di unit ini
            if (UserUnit::where('user_id', $data['user_id'])->where('unit_id', $unitId)->exists()) {
                Notification::make()
                    ->title(__('message.user_unit.error.user_exists'))
                    ->danger()
                    ->send();

                return;
            }

            // -----------tambah user unit
            UserUnit::create([
                'user_id' => $data['user_id'],
                'unit_id' => $unitId,
                'role_id' => $koordinatorRole->id,
            ]);
        }

        // -----------tambah role ke user
        $addRole = UserHelper::addRole($data['user_id'], $koordinatorRole->id);
        if ($addRole['status'] == '0') {
            Notification::make()
                ->title('Error')
                ->body(__('message.global.error.created'))
                ->danger()
                ->send();

            return;
        }
        Notification::make()
            ->title(__('message.global.success.created'))
            ->success()
            ->send();
        $this->refresh();
    }

    public function refresh(): void
    {
        $this->loadCache();
        $this->dispatch('refreshTable');
    }

    public static function getNavigationLabel(): string
    {
        return __('User Units'); // Label for navigation
    }

    protected function loadCache(): void
    {
        // Ambil semua user yang memiliki user_unit
        $this->userIds = UserUnit::whereNotNull('unit_id')->pluck('user_id')->toArray();

        // Ambil user_units sekaligus untuk mapping
        $this->userUnits = UserUnit::whereIn('user_id', $this->userIds)->get()->groupBy('user_id'); // grouped by user_id
    }
}
