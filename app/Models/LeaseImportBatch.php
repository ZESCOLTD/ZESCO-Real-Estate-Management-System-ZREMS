<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaseImportBatch extends Model
{
    protected $fillable = [
        'original_filename',
        'file_path',
        'uploaded_by',
        'status',
        'row_count',
        'rows',
        'choices',
        'errors',
        'results',
        'applied_at',
        'date_order',
    ];

    protected $casts = [
        'rows' => 'array',
        'choices' => 'array',
        'errors' => 'array',
        'results' => 'array',
        'applied_at' => 'datetime',
    ];

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}