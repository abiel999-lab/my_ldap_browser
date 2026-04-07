<?php

namespace App\Models\Auth;

class App
{
    public $id;

    public $app_kategori_id;

    public $nama;

    public $kode;

    public $deskripsi;

    public $deskripsi_en;

    public $icon;

    public $url_api;

    public $is_hidden;

    public $url;

    public function __construct($attributes = [])
    {
        foreach ($attributes as $key => $value) {
            if (! in_array($key, ['created_at', 'updated_at', 'secret'])) {
                $this->$key = $value;
            }
        }
    }
}
