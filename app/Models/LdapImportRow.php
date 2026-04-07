<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LdapImportRow extends Model
{
    protected $fillable = [
        'ldap_import_id',
        'row_number',
        'uid',
        'dn',
        'status',
        'message',
        'payload_json',
    ];

    public function ldapImport(): BelongsTo
    {
        return $this->belongsTo(LdapImport::class);
    }
}
