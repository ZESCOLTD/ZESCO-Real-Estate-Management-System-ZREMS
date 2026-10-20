<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaseAgreement extends Model
{
    protected $fillable = [
        'contract_description',
        'landlord_name',
        'landlord_contact_phone',
        'town',
        'province_id',
        'lease_duration',
        'lease_tenure_years',
        'monthly_payment',
        'annual_payment',
        'start_date',
        'end_date',
        'lease_status_id',
        'comment',
        'responsible_officer_id',
    ];

    protected $casts = [
        'lease_tenure_years' => 'decimal:2',
        'monthly_payment' => 'decimal:2',
        'annual_payment' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

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
}