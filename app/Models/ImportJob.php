<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ImportJob extends Model
{
    protected $fillable = [
        'file_path',
        'status',
        'total_rows',
        'processed_rows',
        'failed_rows'
    ];
}
