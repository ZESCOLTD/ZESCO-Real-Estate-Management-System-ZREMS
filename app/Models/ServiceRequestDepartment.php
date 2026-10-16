<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceRequestDepartment extends Model
{
    protected $fillable = [
        'service_request_id',
        'department_code',
        'hod_user_id',
        'assigned_to_user_id',
        'assigned_by_user_id',
        'assigned_at',
        'status',
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
    ];

    public function request()
    {
        return $this->belongsTo(ServiceRequest::class, 'service_request_id');
    }

    public function serviceRequest()
    {
        return $this->belongsTo(ServiceRequest::class, 'service_request_id');
    }

    public function hod()
    {
        return $this->belongsTo(User::class, 'hod_user_id');
    }

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to_user_id');
    }

    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by_user_id');
    }

    public function services()
    {
        return $this->hasMany(ServiceRequestService::class, 'service_request_department_id');
    }
}
