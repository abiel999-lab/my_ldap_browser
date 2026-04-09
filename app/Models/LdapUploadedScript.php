<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LdapUploadedScript extends Model
{
    protected $fillable = [
        'name',
        'original_filename',
        'stored_path',
        'extension',
        'script_content',
        'is_active',
        'uploaded_by_name',
        'uploaded_by_email',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
