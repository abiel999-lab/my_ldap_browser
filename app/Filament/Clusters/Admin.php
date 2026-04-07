<?php

namespace App\Filament\Clusters;

use Filament\Clusters\Cluster;

class Admin extends Cluster
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-squares-2x2';

    protected static ?string $clusterBreadcrumb = 'Admin Management';

    public static function getNavigationLabel(): string
    {
        return __('Admin Management');
    }
}
