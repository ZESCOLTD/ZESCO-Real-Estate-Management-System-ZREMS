<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceRequest extends Model
{
    public const CONTEXT_ACQUISITION_REQUEST = 'acquisition_request';
    public const CONTEXT_MANAGEMENT_REQUEST = 'management_request';
    public const CONTEXT_DUE_DILIGENCE = 'acquisition_due_diligence';

    protected $fillable = [
        'requested_by',
        'context_type',
        'context_id',
        'notes',
        'status',
    ];

    public function scopeForContext($query, string $contextType, $contextId = null)
    {
        $query->where('context_type', $contextType);

        if ($contextId !== null) {
            $query->where('context_id', $contextId);
        }

        return $query;
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function departments()
    {
        return $this->hasMany(ServiceRequestDepartment::class, 'service_request_id');
    }

    public function services()
    {
        return $this->hasManyThrough(
            ServiceRequestService::class,
            ServiceRequestDepartment::class,
            'service_request_id',
            'service_request_department_id',
            'id',
            'id'
        );
    }
}
