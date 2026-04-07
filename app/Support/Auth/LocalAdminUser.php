<?php

declare(strict_types=1);

namespace App\Support\Auth;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Foundation\Auth\User as Authenticatable;

class LocalAdminUser extends Authenticatable implements FilamentUser
{
    protected $table = null;

    protected $fillable = [
        'id',
        'name',
        'email',
    ];

    protected $guarded = [];

    public $timestamps = false;

    public $incrementing = false;

    protected $keyType = 'string';

    public function canAccessPanel(Panel $panel): bool
    {
        return true;
    }

    public function getFilamentName(): string
    {
        return (string) ($this->attributes['name'] ?? 'Local Admin');
    }

    public function getFilamentAvatarUrl(): ?string
    {
        return null;
    }
}
