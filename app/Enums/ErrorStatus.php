<?php

namespace App\Enums;

enum ErrorStatus: string
{
    case Emergency = 'EMERGENCY';

    case Alert = 'ALERT';

    case Critical = 'CRITICAL';

    case Error = 'ERROR';

    case Warning = 'WARNING';

    case Notice = 'NOTICE';

    case Info = 'INFO';

    public function getLabel(): string
    {
        return match ($this) {
            self::Emergency => 'Emergency',
            self::Alert => 'Alert',
            self::Critical => 'Critical',
            self::Error => 'Error',
            self::Warning => 'Warning',
            self::Notice => 'Notice',
            self::Info => 'Info',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Emergency => 'danger',
            self::Alert => 'warning',
            self::Critical, self::Error => 'danger',
            self::Warning => 'warning',
            self::Notice => 'info',
            self::Info => 'info',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::Emergency => 'heroicon-m-triangle-exclamation',
            self::Alert => 'heroicon-m-triangle-exclamation',
            self::Critical => 'heroicon-m-triangle-exclamation',
            self::Error => 'heroicon-m-triangle-exclamation',
            self::Warning => 'heroicon-m-triangle-exclamation',
            self::Notice => 'heroicon-m-information-circle',
            self::Info => 'heroicon-m-information-circle',
        };
    }
}
