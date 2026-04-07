<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LdapImport extends Model
{
    protected $fillable = [
        'title',
        'file_path',
        'original_name',
        'mode',
        'status',
        'total_rows',
        'success_rows',
        'failed_rows',
        'notes',
        'error_message',
        'requested_by',
    ];

    public function rows(): HasMany
    {
        return $this->hasMany(LdapImportRow::class);
    }
}
