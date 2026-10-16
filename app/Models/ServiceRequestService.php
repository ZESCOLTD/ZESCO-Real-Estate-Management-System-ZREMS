<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceRequestService extends Model
{
    protected $fillable = [
        'service_request_department_id',
        'service_child_id',
        'service_name',
        'assigned_to_user_id',
        'assigned_by_user_id',
        'assigned_at',
        'status',
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
    ];

    public function department()
    {
        return $this->belongsTo(ServiceRequestDepartment::class, 'service_request_department_id');
    }

    public function child()
    {
        return $this->belongsTo(ServiceChild::class, 'service_child_id');
    }

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to_user_id');
    }

    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by_user_id');
    }

    public function getRequestAttribute()
    {
        return $this->department?->request;
    }
}
