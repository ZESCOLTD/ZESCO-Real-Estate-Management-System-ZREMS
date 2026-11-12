<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class TenantOnboardingApplication extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'reference_no', 'tenant_type', 'applicant_name', 'identity_reference', 'man_no', 'employer',
        'organization_registration_no', 'contact_person', 'phone', 'email', 'physical_address',
        'postal_address', 'station_location', 'category', 'location_code',
        'requested_unit_reference', 'occupants_count', 'requested_move_in_date',
        'declaration_confirmed', 'status', 'tenant_register_id', 'created_by', 'submitted_at',
    ];

    protected $casts = [
        'occupants_count' => 'integer',
        'requested_move_in_date' => 'date',
        'declaration_confirmed' => 'boolean',
        'submitted_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::created(function (self $application): void {
            if (blank($application->reference_no)) {
                $application->forceFill([
                    'reference_no' => sprintf('TEN-%s-%05d', $application->created_at->format('Y'), $application->id),
                ])->saveQuietly();
            }
        });
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function tenantRegisterRecord(): BelongsTo
    {
        return $this->belongsTo(TenantRegister::class, 'tenant_register_id');
    }

    public function documents(): MorphMany
    {
        return $this->morphMany(TenantDocument::class, 'documentable');
    }

    public function workflowInstances(): MorphMany
    {
        return $this->morphMany(WorkflowInstance::class, 'workflowable')->latest();
    }

    public function historyEvents(): MorphMany
    {
        return $this->morphMany(TenantHistoryEvent::class, 'tenantable')->latest('occurred_at');
    }
}