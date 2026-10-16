<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Connection;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;

#[Table('lams_document_tracker', incrementing: false, timestamps: false, )]
#[Connection('oracle')]
class SpmsDocumentTrackerView extends Model
{
    protected $primaryKey = null;
}
