<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServiceFormSubmission extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'service_request_service_id',
        'service_form_template_id',
        'submitted_by_id',
        'status',
        'submitted_at',
        'locked_at',
        'rejection_reason',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'locked_at' => 'datetime',
    ];

    public function serviceRequestService(): BelongsTo
    {
        return $this->belongsTo(ServiceRequestService::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(ServiceFormTemplate::class, 'service_form_template_id');
    }

    public function submittedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by_id');
    }

    public function values(): HasMany
    {
        return $this->hasMany(ServiceFormValue::class);
    }

    public function getValueForField(int $fieldId): ?ServiceFormValue
    {
        return $this->values()->where('service_form_field_id', $fieldId)->first();
    }

    public function getValuesForField(int $fieldId)
    {
        return $this->values()->where('service_form_field_id', $fieldId)->get();
    }

    public function submit(int $userId): bool
    {
        $this->update([
            'status' => 'submitted',
            'submitted_by_id' => $userId,
            'submitted_at' => now(),
        ]);
        return true;
    }

    public function lock(): bool
    {
        $this->update([
            'status' => 'locked',
            'locked_at' => now(),
        ]);
        return true;
    }

    public function reject(string $reason): bool
    {
        $this->update([
            'status' => 'rejected',
            'rejection_reason' => $reason,
        ]);
        return true;
    }

    public function approve(): bool
    {
        $this->update(['status' => 'approved']);
        return true;
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isSubmitted(): bool
    {
        return $this->status === 'submitted';
    }

    public function isLocked(): bool
    {
        return $this->status === 'locked';
    }

    public function isEditable(): bool
    {
        return $this->status === 'draft';
    }
}
