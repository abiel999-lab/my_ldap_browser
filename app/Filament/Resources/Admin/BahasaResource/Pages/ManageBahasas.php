<?php

namespace App\Filament\Resources\Admin\BahasaResource\Pages;

use App\Filament\Resources\Admin\BahasaResource;
use Exception;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification; // Untuk notifikasi sukses
use Filament\Resources\Pages\ManageRecords; // Untuk panggil command
use Illuminate\Support\Facades\Artisan; // Class Action custom

class ManageBahasas extends ManageRecords
{
    protected static string $resource = BahasaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
            // 2. Tombol Custom "Generate Files"
            Action::make('generate_files')
                ->label('Generate JSON') // Label tombol
                ->icon('heroicon-o-arrow-path') // Icon refresh/sync
                ->color('success') // Warna hijau
                ->requiresConfirmation() // Munculkan pop-up konfirmasi (Safety)
                ->modalHeading('Generate Language Files?')
                ->modalDescription('Tindakan ini akan menimpa file JSON bahasa yang ada dengan data dari database.')
                ->modalSubmitActionLabel('Ya, Generate')
                ->action(function () {
                    // Logika Utama: Panggil Artisan Command
                    try {
                        Artisan::call('lang:generate');

                        // Kirim Notifikasi Sukses
                        Notification::make()
                            ->title('File Bahasa Berhasil Digenerate')
                            ->success()
                            ->send();
                    } catch (Exception $e) {
                        // Kirim Notifikasi Error (jika gagal)
                        Notification::make()
                            ->title('Gagal Mengenerate')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
        ];
    }
}
