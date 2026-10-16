<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Connection;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;

#[Table('lams_documen_audit_trail', incrementing: false, timestamps: false, )]
#[Connection('oracle')]
class SpmsDocumentAuditTrialView extends Model
{
    protected $primaryKey = null;
}
