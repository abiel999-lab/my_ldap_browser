<?php

namespace App\Models\Auth;

use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Filament\Models\Contracts\HasName;
use Filament\Panel;
use Illuminate\Auth\GenericUser;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;

class User extends GenericUser implements Arrayable, Authenticatable, FilamentUser, HasAvatar, HasName, Jsonable
{
    public $id;

    public $tipe_user_id;

    public $nama;

    public $email;

    public $kode;

    public function __construct($attributes = [])
    {
        foreach ($attributes as $key => $value) {
            if (! in_array($key, ['user_role', 'user_kode', 'created_at', 'updated_at'])) {
                $this->$key = $value;
            }
        }
        if (isset($attributes['user_role']) && is_array($attributes['user_role'])) {
            $user_role = [];
            foreach ($attributes['user_role'] as $userRoleData) {
                if ($userRoleData['app_id'] == config('app.id')) {
                    $user_role[] = new UserRole($userRoleData);
                }
            }
            $this->user_role = $user_role;
        }
    }

    public function getNameAttribute()
    {
        return $this->nama;
    }

    /**
     * Mengubah objek user menjadi array.
     * Ini membuka akses ke properti protected $attributes.
     */
    public function toArray()
    {
        return $this->attributes;
    }

    /**
     * Mengubah objek user menjadi JSON string.
     */
    public function toJson($options = 0)
    {
        return json_encode($this->toArray(), $options);
    }

    public function user_role()
    {
        return collect($this->user_role);
    }

    public function hasRole(...$roleKode)
    {
        foreach ($this->user_role() as $userRole) {
            if (in_array($userRole->role->kode, $roleKode)) {
                return true;
            }
        }

        return false;
    }

    // --- 1. SOLUSI ERROR: Tambahkan Method ini ---
    public function getAttributeValue($key)
    {
        return $this->$key ?? null;
    }

    // Tambahkan juga ini untuk jaga-jaga (sering dipanggil Eloquent)
    public function getAttribute($key)
    {
        return $this->getAttributeValue($key);
    }

    // Agar Filament bisa mengenali Primary Key (selain getAuthIdentifier)
    public function getKey()
    {
        return $this->attributes['id'] ?? null;
    }

    // --- 2. Magic Method (Agar bisa akses $user->email) ---
    public function __get($key)
    {
        return $this->getAttributeValue($key);
    }

    public function __set($key, $value)
    {
        $this->$key = $value;
    }

    public function __isset($key)
    {
        return isset($this->$key);
    }

    // --- 3. Authenticatable Interface (Wajib) ---
    public function getAuthIdentifierName()
    {
        return 'id';
    }

    public function getAuthIdentifier()
    {
        return $this->id ?? null;
    }

    public function getAuthPassword()
    {
        return '';
    }

    public function getRememberToken()
    {
        return '';
    }

    public function setRememberToken($value) {}

    public function getRememberTokenName()
    {
        return 'remember_token';
    }

    // --- 4. Filament Interface ---
    public function canAccessPanel(Panel $panel): bool
    {
        return true;
    }

    public function getFilamentName(): string
    {
        return $this->nama ?? 'Guest';
    }

    public function getFilamentAvatarUrl(): ?string
    {
        return 'https://ui-avatars.com/api/?name=' . urlencode($this->getFilamentName());
    }
}
