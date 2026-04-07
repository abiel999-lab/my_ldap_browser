<?php

declare(strict_types=1);

namespace App\Filament\Clusters;

use BackedEnum;
use Filament\Clusters\Cluster;

class AdminManagement extends Cluster
{
    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-shield-check';

    protected static ?int $navigationSort = 20;

    protected static ?string $slug = 'admin-management';

    public static function getNavigationLabel(): string
    {
        return 'Admin Management';
    }

    public static function getClusterBreadcrumb(): ?string
    {
        return 'Admin Management';
    }
}
