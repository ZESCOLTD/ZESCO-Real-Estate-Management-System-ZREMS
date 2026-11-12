<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class TenantDocument extends Model
{
    protected $fillable = [
        'documentable_type', 'documentable_id', 'document_type', 'original_name', 'storage_path',
        'mime_type', 'file_size', 'review_status', 'expires_at', 'review_comment', 'uploaded_by',
        'reviewed_by', 'uploaded_at', 'reviewed_at',
    ];

    protected $casts = [
        'file_size' => 'integer',
        'expires_at' => 'date',
        'uploaded_at' => 'datetime',
        'reviewed_at' => 'datetime',
    ];

    public function documentable(): MorphTo
    {
        return $this->morphTo();
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}