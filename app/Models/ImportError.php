<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ImportError extends Model
{
    protected $fillable = [
        'import_job_id',
        'row_number',
        'error_message',
        'row_data'
    ];

    protected $casts = [
        'row_data' => 'array'
    ];
}
