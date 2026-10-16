<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceFormValue extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_form_submission_id',
        'service_form_field_id',
        'value',
        'comment',
        'file_path',
        'file_original_name',
        'file_mime_type',
        'file_size',
    ];

    public function submission(): BelongsTo
    {
        return $this->belongsTo(ServiceFormSubmission::class);
    }

    public function field(): BelongsTo
    {
        return $this->belongsTo(ServiceFormField::class, 'service_form_field_id');
    }

    public function hasFile(): bool
    {
        return !is_null($this->file_path);
    }

    public function getFileUrlAttribute(): ?string
    {
        return $this->file_path
            ? route('service_forms.files.view', ['value' => $this->id], false)
            : null;
    }
}
