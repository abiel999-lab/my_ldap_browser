<?php

namespace App\Filament\Pages\Ldap;

use App\Models\LdapScriptRun;
use App\Models\LdapUploadedScript;
use App\Services\Ldap\LdapUploadedScriptRunnerService;
use Filament\Actions;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class LdapScriptRunner extends Page
{
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-command-line';
    protected static string | \UnitEnum | null $navigationGroup = 'LDAP Management';
    protected static ?string $navigationLabel = 'Run LDAP Script';
    protected static ?string $title = 'Run LDAP Script';
    protected static ?int $navigationSort = 80;

    protected string $view = 'filament.pages.ldap-script-runner';

    public ?LdapScriptRun $lastRun = null;
    public ?LdapUploadedScript $selectedScript = null;

    public function mount(): void
    {
        $this->lastRun = LdapScriptRun::query()->latest()->first();
        $this->selectedScript = LdapUploadedScript::query()->latest()->first();
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('uploadScript')
                ->label('Upload Script')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('gray')
                ->form([
                    TextInput::make('name')
                        ->label('Script Name')
                        ->required(),

                    FileUpload::make('script_file')
                        ->label('Script File')
                        ->required()
                        ->disk('local')
                        ->directory('ldap-scripts')
                        ->preserveFilenames(),
                ])
                ->action(function (array $data) {
                    try {
                        $storedPath = (string) ($data['script_file'] ?? '');

                        if ($storedPath === '') {
                            throw new \RuntimeException('File script wajib diupload.');
                        }

                        $absolutePath = storage_path('app/' . $storedPath);

                        if (! file_exists($absolutePath)) {
                            throw new \RuntimeException('File upload tidak ditemukan di storage.');
                        }

                        $originalFilename = basename($storedPath);
                        $extension = strtolower(pathinfo($originalFilename, PATHINFO_EXTENSION));

                        if (! in_array($extension, ['sh', 'bat', 'cmd', 'ps1'], true)) {
                            throw new \RuntimeException('Extension file tidak didukung. Gunakan .sh, .bat, .cmd, atau .ps1');
                        }

                        $content = file_get_contents($absolutePath);

                        $user = Auth::user();

                        $script = LdapUploadedScript::query()->create([
                            'name' => (string) $data['name'],
                            'original_filename' => $originalFilename,
                            'stored_path' => $storedPath,
                            'extension' => $extension,
                            'script_content' => $content,
                            'is_active' => true,
                            'uploaded_by_name' => $user->name ?? null,
                            'uploaded_by_email' => $user->email ?? null,
                        ]);

                        $this->selectedScript = $script;

                        Notification::make()
                            ->title('Script uploaded successfully')
                            ->success()
                            ->send();
                    } catch (\Throwable $e) {
                        Notification::make()
                            ->title('Upload script failed')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

            Actions\Action::make('selectScript')
                ->label('Select Script')
                ->icon('heroicon-o-document-text')
                ->color('gray')
                ->form([
                    Select::make('script_id')
                        ->label('Script')
                        ->options(fn () => LdapUploadedScript::query()
                            ->orderByDesc('id')
                            ->get()
                            ->mapWithKeys(fn ($script) => [
                                $script->id => "{$script->name} ({$script->original_filename})"
                            ])
                            ->toArray()
                        )
                        ->searchable()
                        ->required(),
                ])
                ->action(function (array $data) {
                    $this->selectedScript = LdapUploadedScript::query()->find((int) $data['script_id']);

                    Notification::make()
                        ->title('Script selected')
                        ->success()
                        ->send();
                }),

            Actions\Action::make('runScript')
                ->label('Run Script')
                ->icon('heroicon-o-play')
                ->color('primary')
                ->requiresConfirmation()
                ->action(function () {
                    try {
                        if (! $this->selectedScript) {
                            throw new \RuntimeException('Pilih script terlebih dahulu.');
                        }

                        $run = app(LdapUploadedScriptRunnerService::class)->run($this->selectedScript->id);
                        $this->lastRun = $run;

                        Notification::make()
                            ->title('Script executed successfully')
                            ->success()
                            ->send();
                    } catch (\Throwable $e) {
                        Notification::make()
                            ->title('Script execution failed')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

            Actions\Action::make('refresh')
                ->label('Refresh')
                ->icon('heroicon-o-arrow-path')
                ->color('gray')
                ->action(function () {
                    $this->lastRun = LdapScriptRun::query()->latest()->first();

                    if ($this->selectedScript) {
                        $this->selectedScript = LdapUploadedScript::query()->find($this->selectedScript->id);
                    }

                    Notification::make()
                        ->title('Refreshed')
                        ->success()
                        ->send();
                }),
        ];
    }
}
