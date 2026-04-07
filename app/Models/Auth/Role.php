<?php

namespace App\Models\Auth;

class Role
{
    public $id;

    public $nama;

    public $kode;

    public function __construct($attributes = [])
    {
        foreach ($attributes as $key => $value) {
            if (! in_array($key, ['created_at', 'updated_at'])) {
                $this->$key = $value;
            }
        }
    }
}
