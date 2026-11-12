<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LeaseAgreement extends Model
{
    protected $fillable = [
        'contract_description',
        'landlord_name',
        'landlord_contact_phone',
        'tenant_name',
        'tenant_contact_name',
        'tenant_contact_phone',
        'tenant_contact_email',
        'property_reference',
        'town',
        'province_id',
        'lease_duration',
        'lease_tenure_years',
        'monthly_payment',
        'annual_payment',
        'payment_period',
        'payment_amount',
        'start_date',
        'end_date',
        'lease_status_id',
        'comment',
        'responsible_officer_id',
        'zesco_role',
    ];

    protected $casts = [
        'lease_tenure_years' => 'decimal:2',
        'monthly_payment' => 'decimal:2',
        'annual_payment' => 'decimal:2',
        'payment_amount' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    protected $attributes = [
    'zesco_role' => 'tenant',
];

    protected static function booted(): void
    {
        static::created(function (self $lease): void {
            if ($lease->lease_number !== null) {
                return;
            }

            $prefix = match ($lease->zesco_role) {
                'tenant' => 'ZT',
                'landlord' => 'ZL',
            };

            $lease->forceFill([
                'lease_number' => sprintf('%s-%06d', $prefix, $lease->id),
            ])->saveQuietly();
        });
    }

    public function province(): BelongsTo
    {
        return $this->belongsTo(Province::class);
    }

    public function leaseStatus(): BelongsTo
    {
        return $this->belongsTo(LeaseStatus::class);
    }

    public function responsibleOfficer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsible_officer_id');
    }

    public function tenantWorkflows(): HasMany
    {
        return $this->hasMany(LeaseTenantWorkflow::class)->latest();
    }

    public function lifecycleCases(): HasMany
    {
        return $this->hasMany(LeaseLifecycleCase::class)->latest();
    }

    public function landlordApplication(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(LeaseLandlordApplication::class);
    }
}