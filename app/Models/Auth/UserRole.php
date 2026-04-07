<?php

namespace App\Models\Auth;

class UserRole
{
    public $id;

    public $user_id;

    public $role_id;

    public $app_id;

    public function __construct($attributes = [])
    {
        foreach ($attributes as $key => $value) {
            if (! in_array($key, ['created_at', 'updated_at'])) {
                $this->$key = $value;
            }
        }

        if (isset($attributes['role'])) {
            $this->role = new Role($attributes['role']);
        }
        if (isset($attributes['app'])) {
            $this->app = new App($attributes['app']);
        }
    }

    public function role()
    {
        return $this->role;
    }

    public function app()
    {
        return $this->app;
    }
}
