<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantOnboardingDocument extends Model
{
    protected $fillable = [
        'tenant_onboarding_application_id', 'document_type', 'original_name', 'storage_path',
        'mime_type', 'file_size', 'uploaded_by', 'uploaded_at',
    ];

    protected $casts = [
        'file_size' => 'integer',
        'uploaded_at' => 'datetime',
    ];

    public function application(): BelongsTo
    {
        return $this->belongsTo(TenantOnboardingApplication::class, 'tenant_onboarding_application_id');
    }
}