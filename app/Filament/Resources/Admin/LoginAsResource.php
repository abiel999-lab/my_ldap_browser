<?php

namespace App\Filament\Resources\Admin;

use App\Filament\Clusters\Admin;
use App\Filament\Resources\Admin\LoginAsResource\Pages\ManageLoginAs;
use App\Helpers\Auth\UserHelper;
use App\Models\Gate\User;
use Filament\Actions\Action;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Crypt;

class LoginAsResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-user';

    public static function getNavigationLabel(): string
    {
        return __('Login As User');
    }

    protected static ?string $cluster = Admin::class;

    public static function getModelLabel(): string
    {
        return __('Login As User Management');
    }

    public static function shouldRegisterNavigation(): bool
    {
        return UserHelper::userHasRole(['ADMIN', 'PIC']);
    }

    protected static bool $shouldRegisterNavigation = false;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(
                // di query untuk mendapatkan daftar user yang bisa login sebagai
                // hanya user yang memiliki role di aplikasi ini
                User::query()
                    ->with(['user_kode', 'tipe'])
                    ->select(['id', 'nama', 'email', 'tipe_user_id'])
                    ->whereHas('user_role', function (Builder $query) {
                        $query->where('app_id', config('app.id')); // Sesuaikan dengan role_id yang diizinkan
                    })
            )
            ->columns([
                TextColumn::make('user_kode.kode')
                    ->label(__('User Code'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('nama')
                    ->label(__('User Name'))
                    ->description(
                        fn (User $record) => $record->email ? $record->email : 'N/A'
                    )
                    ->searchable()
                    ->sortable(),
                TextColumn::make('tipe.nama')
                    ->label(__('User Type'))
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([
                Filter::make('is_pegawai')
                    ->label(__('Is Employee'))
                    ->query(fn (Builder $query) => $query->where('tipe_user_id', 1))
                    ->default(),
                Filter::make('is_mahasiswa')
                    ->label(__('Is Student'))
                    ->query(fn (Builder $query) => $query->where('tipe_user_id', 2)),
                Filter::make('is_external')
                    ->label(__('Is External'))
                    ->query(fn (Builder $query) => $query->where('tipe_user_id', 3)),
            ], layout: FiltersLayout::AboveContent)
            ->recordActions([
                Action::make('loginAs')
                    ->label(__('Login As'))
                    ->action(function (User $record) {
                        return redirect()->route('loginas.set', Crypt::encryptString($record->id));
                    })
                    ->icon('heroicon-o-arrow-right')
                    ->requiresConfirmation()
                    ->modalDescription(__('Are you sure you want to login as this user?'))
                    ->modalSubmitActionLabel(__('Yes, Login'))
                    ->color('primary'),
            ])
            ->toolbarActions([
                // Define any bulk actions if needed
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageLoginAs::route('/'),
        ];
    }
}
