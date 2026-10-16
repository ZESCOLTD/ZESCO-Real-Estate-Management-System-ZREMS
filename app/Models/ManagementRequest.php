<?php

namespace App\Models;

use App\Enums\Acquisition\AcquisitionType;
use App\Enums\Acquisition\ApprovalStatus;
use App\Enums\Land\OwnershipStatus;
use App\Enums\Land\Unit;
use App\Enums\Shared\YesNo;
use Illuminate\Database\Eloquent\Attributes\Unguarded;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Unguarded]
class ManagementRequest extends Model
{
    use SoftDeletes;

    protected $casts = [
        'request_date'         => 'date:Y-m-d',
        'expected_start_date'  => 'date:Y-m-d',
        'expected_end_date'    => 'date:Y-m-d',
        'estimated_land_cost'  => 'decimal:2',
        'extent_required_ha'   => 'decimal:4',
        'cet_approval'         => 'boolean',
        'board_approval'       => 'boolean',
        'location'             => 'string',
        'is_budget_approved'   => YesNo::class,
        'status'               => ApprovalStatus::class,
        'approved_at'          => 'datetime',
        'acquisition_type' => AcquisitionType::class,
        'ownership_status' => OwnershipStatus::class,
        'extent_unit' => Unit::class,
    ];

    public function acquisitionDueDiligence(): HasOne
    {
        return $this->hasOne(AcquisitionDueDiligence::class);
    }

    public function documents(): MorphMany
    {
        return $this->morphMany(ParcelDocument::class, 'documentable');
    }

    public function payments(): MorphMany
    {
        return $this->morphMany(ParcelPayment::class, 'payable');
    }

    public function getAttachmentsAttribute($value): array
    {
        return $value ? json_decode($value, true) : [];
    }

    public function setAttachmentsAttribute($value): void
    {
        $this->attributes['attachments'] = $value ? json_encode($value) : null;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function attachments()
    {
        return $this->hasMany(AcquisitionRequestAttachment::class, 'acquisition_request_id');
    }
    public function attachmentsShow()
    {
        return $this->hasMany(AcquisitionRequestAttachment::class, 'acquisition_request_id');
    }

    public function workflowInstance()
    {
        return $this->morphOne(WorkflowInstance::class, 'workflowable');
    }

//    public function workflowInstance()
//    {
//        return $this->hasOne(WorkflowInstance::class, 'acquisition_request_id');
//    }

    public function workflowActions()
    {
        return $this->morphMany(WorkflowAction::class, 'workflowable')
            ->orderBy('step_order');
    }




}
